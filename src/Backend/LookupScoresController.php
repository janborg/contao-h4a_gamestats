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
use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;

class LookupScoresController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private GameStatsCrawler $gameStatsCrawler,
        private readonly SystemLogger $systemLogger,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupScores(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        $this->gameStatsCrawler->setgGameID($objCalendarEvent->gGameID);
        $this->gameStatsCrawler->setProvider($objCalendarEvent->provider);
        $this->gameStatsCrawler->setVerbandName($objCalendarEvent->verband);
        $this->gameStatsCrawler->setClassShortName($objCalendarEvent->gClassName);
        $this->gameStatsCrawler->setClassID($objCalendarEvent->gClassID);

        try {
            $this->gameStatsCrawler->crawlGameLineups();
        } catch (\Exception $e) {
            $this->systemLogger->error('Fehler beim Abrufen der Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            Message::addError('Fehler beim Abrufen der Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            $this->redirect($this->getReferer());
        }

        // Spieler der Heimmannschaft speichern
        H4aPlayerscoresModel::saveHandballnetPlayerscores(
            $this->gameStatsCrawler->getHomeLineup(),
            $objCalendarEvent->id,
            $this->gameStatsCrawler->getHomeTeam(),
            1);

        // Spieler der Gastmannschaft speichern
        H4aPlayerscoresModel::saveHandballnetPlayerscores(
            $this->gameStatsCrawler->getGuestLineup(),
            $objCalendarEvent->id,
            $this->gameStatsCrawler->getGuestTeam(),
            2);

        Message::addConfirmation('Playerscores für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
