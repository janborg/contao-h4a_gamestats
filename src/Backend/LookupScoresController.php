<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Backend;

use Contao\Backend;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Input;
use Contao\System;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aTabellen\Helper\Helper;
use Psr\Log\LogLevel;

class LookupScoresController extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function lookupScores(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        if (isset($objCalendarEvent->sGID) && '' === $objCalendarEvent->sGID) {
            $objCalendarEvent->sGID = Helper::getReportNo($objCalendarEvent->gClassID, $objCalendarEvent->gGameNo);
            $objCalendarEvent->save();
        }

        //check if sGID is set and not empty
        if (isset($objCalendarEvent->sGID) && '' !== $objCalendarEvent->sGID) {
            $sGID = $objCalendarEvent->sGID;
        } else {
            $this->redirect($this->getReferer());

            return;
        }

        $h4areportparser = new H4aReportParser($sGID);

        $h4areportparser->parseReport();

        //Spieler der Heimmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objCalendarEvent->id, $h4areportparser->heim_name, $home_guest = 1);

        //Spieler der Gastmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objCalendarEvent->id, $h4areportparser->gast_name, $home_guest = 2);

        System::getContainer()
            ->get('monolog.logger.contao')
            ->log(LogLevel::INFO, 'Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.', ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)])
            ;

        $this->redirect($this->getReferer());
    }
}
