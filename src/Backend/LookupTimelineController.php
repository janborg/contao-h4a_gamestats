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
use Contao\BackendUser;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Psr\Log\LoggerInterface;
use Contao\Input;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Helper\H4aApiHelper;

class LookupTimelineController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private H4aApiHelper $h4aApiHelper,
        private readonly LoggerInterface $contaoGeneralLogger,
        private readonly LoggerInterface $contaoErrorLogger,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupTimeline(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        if (isset($objCalendarEvent->sGID) && '' === $objCalendarEvent->sGID) {
            $objCalendarEvent->sGID = $this->h4aApiHelper->getReportNo($objCalendarEvent->gClassID, $objCalendarEvent->gGameNo);
            $objCalendarEvent->save();
        }

        // check if sGID is set and not empty
        if (isset($objCalendarEvent->sGID) && '' !== $objCalendarEvent->sGID) {
            $sGID = $objCalendarEvent->sGID;
        } else {
            $this->redirect($this->getReferer());

            return; // @phpstan-ignore deadCode.unreachable
        }

        $h4areportparser = new H4aReportParser($sGID);

        try {
            $h4areportparser->parseReport();    
        } catch (\Exception $e) {
            $this->contaoErrorLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.']: '.$e->getMessage());
            
            $this->redirect($this->getReferer());
            
            return;
        }

        H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objCalendarEvent->id);

        $this->contaoGeneralLogger->info('Timeline für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
