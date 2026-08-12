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

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Monolog\SystemLogger;
use Janborg\H4aGamestats\HandballNet\HandballnetGamestatsParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Event\H4aReportUpdatedEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

/**
 * @property H4aReportUpdatedEvent $event
 * @property CalendarEventsModel   $calendarEvent
 */
class UpdateGamestatsListener
{
    public H4aReportUpdatedEvent $event;

    private CalendarEventsModel $calendarEvent;

    public function __construct(
        private readonly SystemLogger $systemLogger,
        private readonly EntityCacheTags $entityCacheTags,
        private readonly HandballnetGamestatsParser $gamestatsParser,
    ) {
    }

    #[AsEventListener(event: H4aReportUpdatedEvent::class)]
    public function onH4aReportUpdatedEvent(H4aReportUpdatedEvent $event): void
    {
        $this->event = $event;
        $this->calendarEvent = $event->getCalendarEvent();

        if (!isset($this->calendarEvent->handballnet_game_id) || '' === $this->calendarEvent->handballnet_game_id) {
            return;
        }

        try {
            $this->gamestatsParser->parseGame($this->calendarEvent->handballnet_game_id);
        } catch (\Exception $e) {
            $this->systemLogger->error(
                'Error while fetching game stats: '.$e->getMessage(),
            );

            return;
        }

        // Spieler der Heim Mannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->home_team, $this->calendarEvent->id, $this->gamestatsParser->heim_name, 1, $this->gamestatsParser->heim_id);

        // Spieler der Gast Mannschaft speichern
        H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->guest_team, $this->calendarEvent->id, $this->gamestatsParser->gast_name, 2, $this->gamestatsParser->gast_id);

        // Timeline des Spiels speichern
        H4aTimelineModel::saveTimeline($this->gamestatsParser->timeline, $this->calendarEvent->id);

        $this->entityCacheTags->invalidateTagsFor($this->calendarEvent);
    }
}
