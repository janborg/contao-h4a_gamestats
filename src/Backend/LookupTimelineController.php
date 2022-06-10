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
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Helper\Helper;
use Psr\Log\LogLevel;

class LookupTimelineController extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    public function lookupTimeline(): void
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

        H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objCalendarEvent->id);

        System::getContainer()
            ->get('monolog.logger.contao')
            ->log(LogLevel::INFO, 'Timeline für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.', ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_GENERAL)])
    ;

        $this->redirect($this->getReferer());
    }
}
