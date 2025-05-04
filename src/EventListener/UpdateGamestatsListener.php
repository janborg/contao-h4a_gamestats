<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\EventListener;

use Contao\CoreBundle\Monolog\SystemLogger;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aTabellen\Event\H4aReportUpdatedEvent;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @property ReportUpdateEvent $event
 * @property CalendarEventsModelk $calendarEvent
 */

class UpdateGamestatsListener
{
    public function __construct(
        private readonly SystemLogger $systemLogger,
        private readonly EntityCacheTags $entityCacheTags
    ) {
    }
    /**
     * @var H4aReportUpdateEvent
     */
    #[AsEventListener(event: H4aReportUpdatedEvent::class)]
    public function onH4aReportUpdatedEvent(H4aReportUpdatedEvent $event): void
    {
        $this->event = $event;
        $this->calendarEvent = $event->getCalendarEvent();

        $h4areportparser = new H4aReportParser($this->calendarEvent->sGID);

        try {
            $h4areportparser->parseReport();
        } catch (\Exception $e) {
            $this->systemLogger->error(
                'Error while parsing report: ' . $e->getMessage()
            );

            return;
        }

        // Spieler der Heim Mannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $this->calendarEvent->id, $h4areportparser->heim_name, $home_guest = 1);

        // Spieler der Gast Mannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $this->calendarEvent->id, $h4areportparser->gast_name, $home_guest = 2);

        // Timeline des Spiels speichern
        H4aTimelineModel::saveTimeline($h4areportparser->timeline, $this->calendarEvent->id);

        $this->entityCacheTags->invalidateTagsFor($this->calendarEvent);
    }
}