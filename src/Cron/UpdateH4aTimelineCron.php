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
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Contao\System;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Helper\Helper;
use Psr\Log\LogLevel;

class UpdateH4aTimelineCron
{
    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
        $this->framework->initialize();
    }

    public function updateTimeline(): void
    {
        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true]
        );

        if (null === $objEvents) {
            return;
        }

        foreach ($objEvents as $objEvent) {
            if (isset($objEvent->sGID) && '' === $objEvent->sGID) {
                $sGID = Helper::getReportNo($objEvent->gClassID, $objEvent->gGameNo);

                if (null !== $sGID) {
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
            $h4areportparser->parseReport();

            //Timeline des Spiels speichern
            H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objEvent->id);

            System::getContainer()
                ->get('monolog.logger.contao')
                ->log(
                    LogLevel::INFO,
                    'Timeline aus Bericht Nr. '.$objEvent->sGID
                        .' für Spiel '.$objEvent->gGameID.' '.$h4areportparser->heim_name.' - '.$h4areportparser->gast_name
                        .' über Handball4all gespeichert',
                    ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON)]
                )
            ;
        }
    }
}
