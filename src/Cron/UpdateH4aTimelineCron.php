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
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Crawler\H4aReportNoCrawler;
use Psr\Log\LoggerInterface;

class UpdateH4aTimelineCron
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private readonly LoggerInterface $contaoCronLogger,
        private readonly LoggerInterface $contaoErrorLogger,
        private H4aReportNoCrawler $h4aReportNoCrawler,
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
            if (isset($objEvent->sGID) && '' === $objEvent->sGID) {
                $this->h4aReportNoCrawler->setProvider($objEvent->provider);
                $this->h4aReportNoCrawler->setClassID($objEvent->gClassID);
                $this->h4aReportNoCrawler->setClassShortName($objEvent->gClassName);
                $this->h4aReportNoCrawler->setgGameID($objEvent->gGameID);
                $this->h4aReportNoCrawler->setVerbandName($objEvent->verband);
                $this->h4aReportNoCrawler->crawlReportNo();

                $sGID = $this->h4aReportNoCrawler->getSGid();

                if (null !== $sGID && '' !== $sGID) {
                    $objEvent->sGID = $sGID;
                    $objEvent->save();
                } else {
                    continue;
                }
            }

            $objTimeline = H4aTimelineModel::findBy('pid', $objEvent->id);

            if (null !== $objTimeline) {
                continue;
            }

            $h4areportparser = new H4aReportParser($objEvent->sGID);

            try {
                $h4areportparser->parseReport();
            } catch (\Exception $e) {
                $this->contaoErrorLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objEvent->gGameNo.' ['.$objEvent->title.']: '.$e->getMessage());

                continue;
            }

            // Timeline des Spiels speichern
            H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objEvent->id);

            $this->contaoCronLogger->info('Timeline aus Bericht Nr. '.$objEvent->sGID
                    .' für Spiel '.$objEvent->gGameID.' '.$h4areportparser->heim_name.' - '.$h4areportparser->gast_name
                    .' über Handball4all gespeichert');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }
    }
}
