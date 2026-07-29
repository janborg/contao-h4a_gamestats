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
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Psr\Log\LoggerInterface;

class UpdateH4aScoresCron
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private readonly LoggerInterface $contaoCronLogger,
        private readonly LoggerInterface $contaoErrorLogger,
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
            if (isset($objEvent->sGID) && '' === $objEvent->sGID) {
                continue;
            }

            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                continue;
            }

            $h4areportparser = new H4aReportParser($objEvent->sGID);

            try {
                $h4areportparser->parseReport();
            } catch (\Exception $e) {
                $this->contaoErrorLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objEvent->gGameNo.' ['.$objEvent->title.']: '.$e->getMessage());

                continue;
            }

            // Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objEvent->id, $h4areportparser->heim_name, $home_guest = 1);

            // Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objEvent->id, $h4areportparser->gast_name, $home_guest = 2);

            $this->contaoCronLogger->info('Gamescores aus Bericht Nr. '.$objEvent->sGID
                    .' für Spiel '.$objEvent->gGameID.' '.$h4areportparser->heim_name.' - '.$h4areportparser->gast_name
                    .' über Handball4all gespeichert');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }
    }
}
