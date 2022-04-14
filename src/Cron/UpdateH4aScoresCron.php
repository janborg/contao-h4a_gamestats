<?php

declare(strict_types=1);


namespace Janborg\H4aGamestats\Cron;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Monolog\ContaoContext;
use Psr\log\LogLevel;
use Contao\System;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Contao\CalendarEventsModel;
use Janborg\H4aTabellen\Helper\Helper;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;


class UpdateH4aScoresCron
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

    public function updateScores(): void
    {
        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true]
        );
        
        if (null === $objEvents) {
          
            return;
        }

        foreach ($objEvents as $objEvent) {
            if (isset($objEvent->sGID) && $objEvent->sGID =="") {
                $sGID = Helper::getReportNo($objEvent->gClassID, $objEvent->gGameNo);
                if (null !== $sGID) {
                    $objEvent->sGID = $sGID;
                    $objEvent->save();
                }
                else {
                    continue;
                }
            }
        
            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                continue;
            }
 
            $h4areportparser = new H4aReportParser($objEvent->sGID);
            $h4areportparser->parseReport();

            //Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objEvent->id, $h4areportparser->heim_name, $home_guest = 1);

            //Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objEvent->id, $h4areportparser->gast_name, $home_guest = 2);

            System::getContainer()
                    ->get('monolog.logger.contao')
                    ->log(LogLevel::INFO, 'Gamescores aus Bericht Nr. '.$objEvent->sGID
                        .' für Spiel '.$objEvent->gGameNo.' '.$h4areportparser->heim_name.' - '.$h4areportparser->gast_name
                        .' über Handball4all gespeichert', 
                        ['contao' => new ContaoContext(__CLASS__.'::'.__FUNCTION__, TL_CRON)]);
        }
    }
}