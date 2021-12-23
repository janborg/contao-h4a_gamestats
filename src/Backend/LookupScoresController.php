<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats\Backend;

use Contao\Backend;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\Input;
use Contao\System;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
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

        //check if sGID is set
        if (isset($objCalendarEvent->sGID)) {
            $sGID = $objCalendarEvent->sGID;
        } else {
            return;
        }

        $h4areportparser = new H4aReportParser($sGID);

        $h4areportparser->parseReport();

        //Spieler der Heimmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->heim_players, $objCalendarEvent->id, $h4areportparser->heim_name);

        //Spieler der Gastmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->gast_players, $objCalendarEvent->id, $h4areportparser->gast_name);

        System::getContainer()
                ->get('monolog.logger.contao')
                ->log(LogLevel::INFO, 'Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.', ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)])
            ;
    

    $this->redirect($this->getReferer());
    }
}