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
use Janborg\H4aGamestats\HandballNet\HandballnetGamestatsParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Psr\Log\LoggerInterface;

class UpdateH4aScoresCron
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private readonly LoggerInterface $contaoCronLogger,
        private readonly LoggerInterface $contaoErrorLogger,
        private readonly HandballnetGamestatsParser $gamestatsParser,
    ) {
        $this->framework->initialize();
    }

    public function updateScores(): void
    {
        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'hn_resultComplete = ?'],
            [date('Y-m-d'), true],
        );

        if (null === $objEvents) {
            return;
        }

        foreach ($objEvents as $objEvent) {
            if (!isset($objEvent->handballnet_game_id) || '' === $objEvent->handballnet_game_id) {
                continue;
            }

            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                continue;
            }

            try {
                $this->gamestatsParser->parseGame($objEvent->handballnet_game_id);
            } catch (\Exception $e) {
                $this->contaoErrorLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objEvent->handballnet_game_id.' ['.$objEvent->title.']: '.$e->getMessage());

                continue;
            }

            // Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->home_team, $objEvent->id, $this->gamestatsParser->heim_name, 1);

            // Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->guest_team, $objEvent->id, $this->gamestatsParser->gast_name, 2);

            $this->contaoCronLogger->info('Gamescores für Spiel '.$objEvent->handballnet_game_id
                    .' '.$this->gamestatsParser->heim_name.' - '.$this->gamestatsParser->gast_name
                    .' über handball.net gespeichert');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }
    }
}
