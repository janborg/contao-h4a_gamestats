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
use Contao\CoreBundle\Monolog\SystemLogger;
use Contao\Input;
use Contao\Message;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Crawler\H4aReportNoCrawler;

class LookupTimelineController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private H4aReportNoCrawler $h4aReportNoCrawler,
        private readonly SystemLogger $systemLogger,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupTimeline(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        if (isset($objCalendarEvent->sGID) && '' === $objCalendarEvent->sGID) {
            $this->h4aReportNoCrawler->setProvider($objCalendarEvent->provider);
            $this->h4aReportNoCrawler->setClassID($objCalendarEvent->gClassID);
            $this->h4aReportNoCrawler->setClassShortName($objCalendarEvent->gClassName);
            $this->h4aReportNoCrawler->setgGameID($objCalendarEvent->gGameID);
            $this->h4aReportNoCrawler->setVerbandName($objCalendarEvent->verband);
            $this->h4aReportNoCrawler->crawlReportNo();

            $objCalendarEvent->sGID = $this->h4aReportNoCrawler->getSGid();
            $objCalendarEvent->save();
        }

        // check if sGID is set and not empty
        if (isset($objCalendarEvent->sGID) && '' !== $objCalendarEvent->sGID) {
            $sGID = $objCalendarEvent->sGID;
        } else {
            Message::addError('Spielberichtsnummer nicht gefunden.');

            $this->redirect($this->getReferer());
        }

        $h4areportparser = new H4aReportParser($sGID);

        try {
            $h4areportparser->parseReport();
        } catch (\Exception $e) {
            $this->systemLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            Message::addError('Fehler beim Abrufen des Spielberichts für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            $this->redirect($this->getReferer());
        }

        H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objCalendarEvent->id);

        Message::addConfirmation('Timeline für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
