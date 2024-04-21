<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Model;

use Contao\Model;
use Contao\Model\Collection;
use Contao\System;

/**
 * @property int    $id
 * @property int    $pid
 * @property int    $tstamp
 * @property string $matchtime
 * @property string $currentscore
 * @property string $action_team
 * @property int    $action_player
 * @property int    $action_player_number
 * @property int    $action_type
 *
 * @method static H4aTimelineModel|null                                         findById($id, array $opt=[])
 * @method static H4aTimelineModel|null                                         findByPk($id, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneBy($col, $val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByPid($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByTstamp($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByMatchtime($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByCurrentscore($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByAction_team($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByAction_player($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByAction_player_number($val, array $opt=[])
 * @method static H4aTimelineModel|null                                         findOneByAction_type($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByPid($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByMatchtime($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByCurrentscore($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByAction_team($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByAction_player($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByAction_player_number($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByAction_type($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findByAction_team($val, array $opt=[])
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findMultipleByIds($val, array $opt=array())
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findBy($col, $val, array $opt=array())
 * @method static Collection<H4aTimelineModel>|H4aTimelineModel|null            findAll(array $opt=array())
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByMatchtime($val, array $opt=array())
 * @method static integer countByCurrentscore($val, array $opt=array())
 * @method static integer countByAction_team($val, array $opt=array())
 * @method static integer countByAction_player($val, array $opt=array())
 * @method static integer countByAction_player_number($val, array $opt=array())
 * @method static integer countByAction_type($val, array $opt=array())
 * @method static integer countByAction_team($val, array $opt=array())
 */
class H4aTimelineModel extends Model
{
    protected static $strTable = 'tl_h4a_timeline';

    /**
     * @param array<mixed> $timelineEvents
     */
    public static function saveTimeline($timelineEvents, int $pid): void
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

    /**
     * @param int $pid
     *
     * @return array<mixed>
     */
    public static function findAllGoalsByCalendarEvent($pid)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                `matchtime`
                ,`currentscore`
                ,`action_team`
                ,`action_player`
                ,`action_player_number`
                ,`action_type`
            FROM
                `tl_h4a_timeline`
            WHERE
                `pid` = ?  AND
                `action_type` IN ("Tor", "7m-Tor")
            ORDER BY
                `matchtime` ASC',
            [$pid]
        );

        return $stmt->fetchAll();
    }
}
