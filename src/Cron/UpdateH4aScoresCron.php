<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Cron;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Psr\Log\LoggerInterface;

class UpdateH4aScoresCron
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private readonly LoggerInterface $contaoCronLogger,
        private GameStatsCrawler $gameStatsCrawler,
    ) {
        $this->framework->initialize();
    }

    public function updateScores(): void
    {
        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true],
        );

        if (null === $objEvents) {
            return;
        }

        foreach ($objEvents as $objEvent) {
            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                continue;
            }

            $this->gameStatsCrawler->setgGameID($objEvent->gGameID);
            $this->gameStatsCrawler->setProvider($objEvent->provider);
            $this->gameStatsCrawler->setVerbandName($objEvent->verband);
            $this->gameStatsCrawler->setClassShortName($objEvent->gClassName);
            $this->gameStatsCrawler->setClassID($objEvent->gClassID);

            try {
                $this->gameStatsCrawler->crawlGameLineups();
            } catch (\Exception $e) {
                $this->contaoCronLogger->error('Fehler beim Abrufen der Playerscores für Spiel '.$objEvent->gGameNo.' ['.$objEvent->title.']: '.$e->getMessage());

                continue;
            }

            // Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::saveHandballnetPlayerscores(
                $this->gameStatsCrawler->getHomeLineup(),
                $objEvent->id,
                $this->gameStatsCrawler->getHomeTeam(),
                1,
            );

            // Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::saveHandballnetPlayerscores(
                $this->gameStatsCrawler->getGuestLineup(),
                $objEvent->id,
                $this->gameStatsCrawler->getGuestTeam(),
                2,
            );

            $this->contaoCronLogger->info('Gamescores aus Bericht Nr. '.$objEvent->sGID
                .' für Spiel '.$objEvent->gGameID.' '.$this->gameStatsCrawler->getHomeTeam().' - '.$this->gameStatsCrawler->getGuestTeam()
                .' über Handball4all gespeichert');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }
    }
}
