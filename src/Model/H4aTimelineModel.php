<?php

declare(strict_types=1);

/*
 * This file is part of hsg-heilbronn website.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Model;

use Contao\Model;
use Contao\System;

/**
 * add properties for IDE support.
 */
class H4aTimelineModel extends Model
{
    protected static $strTable = 'tl_h4a_timeline';

    /**
     * @param array $timelineEvents
     * @param string $pid
     */
    public static function saveTimeline(array $timelineEvents, string $pid): void
    {
        foreach ($timelineEvents as $timelineEvent) {
            
            $objTimelineEvent = self::findBy(
                ['pid = ?', 'matchtime = ?'],
                [$pid, $timelineEvent['matchtime']],
            );

            if (null === $objTimelineEvent) {
                $objTimelineEvent = new self();
            }

            $objTimelineEvent->pid = $pid;
            $objTimelineEvent->tstamp = time();
            $objTimelineEvent->matchtime = $timelineEvent['matchtime'];
            $objTimelineEvent->currentscore = $timelineEvent['currentscore'];
            $objTimelineEvent->action_team = $timelineEvent['action_team'];
            $objTimelineEvent->action_player = $timelineEvent['action_player'];
            $objTimelineEvent->action_player_number = $timelineEvent['action_player_number'];
            $objTimelineEvent->action_type = $timelineEvent['action_type'];

            $objTimelineEvent->save();
        }
    }
}