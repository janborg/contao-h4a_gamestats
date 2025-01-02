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
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aTabellen\Helper\H4aApiHelper;

class LookupScoresController extends Backend
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

    public function lookupScores(): void
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

        $h4areportparser->parseReport();

        // Spieler der Heimmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objCalendarEvent->id, $h4areportparser->heim_name, $home_guest = 1);

        // Spieler der Gastmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objCalendarEvent->id, $h4areportparser->gast_name, $home_guest = 2);

        $this->contaoGeneralLogger->info('Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
