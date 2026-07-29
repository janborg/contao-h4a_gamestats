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
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;

class LookupScoresController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private readonly SystemLogger $systemLogger,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupScores(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        // check if sGID is set and not empty
        if (isset($objCalendarEvent->sGID) && '' !== $objCalendarEvent->sGID) {
            $sGID = $objCalendarEvent->sGID;
        } else {
            Message::addError('Spielberichtsnummer für Spiel mit ID nicht vorhanden.');

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

        // Spieler der Heimmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objCalendarEvent->id, $h4areportparser->heim_name, $home_guest = 1);

        // Spieler der Gastmannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objCalendarEvent->id, $h4areportparser->gast_name, $home_guest = 2);

        Message::addConfirmation('Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
