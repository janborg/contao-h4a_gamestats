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
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Psr\Log\LoggerInterface;

class UpdateH4aTimelineCron
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private readonly LoggerInterface $contaoCronLogger,
        private GameStatsCrawler $gameStatsCrawler,
    ) {
        $this->framework->initialize();
    }

    public function updateTimeline(): void
    {
        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true],
        );

        if (null === $objEvents) {
            return;
        }

        foreach ($objEvents as $objEvent) {
            $objTimeline = H4aTimelineModel::findBy('pid', $objEvent->id);

            if (null !== $objTimeline) {
                continue;
            }

            $this->gameStatsCrawler->setgGameID($objEvent->gGameID);
            $this->gameStatsCrawler->setProvider($objEvent->provider);
            $this->gameStatsCrawler->setVerbandName($objEvent->verband);
            $this->gameStatsCrawler->setClassShortName($objEvent->gClassName);
            $this->gameStatsCrawler->setClassID($objEvent->gClassID);

            $this->gameStatsCrawler->crawlAllGameStats();

            $timeline = $this->gameStatsCrawler->getTimeline();

            if (empty($timeline)) {
                $this->contaoCronLogger->info('Timeline für Spiel '.$objEvent->gGameID.' / '.$objEvent->gClassName.' / '.$this->gameStatsCrawler->getHomeTeam().' - '.$this->gameStatsCrawler->getGuestTeam()
                .' konnte nicht gefunden werden.');
                continue;
            }

            H4aTimelineModel::saveTimeline($timeline, $objEvent->id);

            $this->contaoCronLogger->info('Timeline für Spiel '.$objEvent->gGameID.' '.$this->gameStatsCrawler->getHomeTeam().' - '.$this->gameStatsCrawler->getGuestTeam()
                    .' über Handball.net gespeichert');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }
    }
}
