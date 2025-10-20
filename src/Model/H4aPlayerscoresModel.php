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
 * @property string $number
 * @property string $team_name
 * @property string $name
 * @property int    $goals
 * @property int    $penalty_goals
 * @property int    $penalty_tries
 * @property int    $yellow_card
 * @property int    $suspensions
 * @property int    $red_card
 * @property int    $blue_card
 * @property string $is_home_or_guest
 *
 * @method static H4aPlayerscoresModel|null                                  findById($id, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findByPk($id, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneBy($col, $val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByPid($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByTstamp($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByNumber($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByTeam_name($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByName($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByGoals($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByPenalty_goals($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByPenalty_tries($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByYellow_card($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneBySuspensions($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByRed_card($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByBlue_card($val, array $opt=[])
 * @method static H4aPlayerscoresModel|null                                  findOneByIs_home_or_guest($val, array $opt=[])
 * @method static Collection<H4aPlayerscoresModel>|H4aPlayerscoresModel|null findByPid($val, array $opt=[])
 * @method static Collection<H4aPlayerscoresModel>|H4aPlayerscoresModel|null findByTeamName($val, array $opt=[])
 * @method static Collection<H4aPlayerscoresModel>|H4aPlayerscoresModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection<H4aPlayerscoresModel>|H4aPlayerscoresModel|null findBy($col, $val, array $opt=array())
 * @method static Collection<H4aPlayerscoresModel>|H4aPlayerscoresModel|null findAll(array $opt=array())
 * @method static integer                                                    countById($id, array $opt=array())
 * @method static integer                                                    countByPid($val, array $opt=array())
 * @method static integer                                                    countByTstamp($val, array $opt=array())
 * @method static integer                                                    countByPidNumber($val, array $opt=array())
 * @method static integer                                                    countByTeam_name($val, array $opt=array())
 * @method static integer                                                    countByName($val, array $opt=array())
 * @method static integer                                                    countByGoals($val, array $opt=array())
 * @method static integer                                                    countByPenalty_goals($val, array $opt=array())
 * @method static integer                                                    countByPenalty_tries($val, array $opt=array())
 * @method static integer                                                    countByYellow_card($val, array $opt=array())
 * @method static integer                                                    countBySuspensions($val, array $opt=array())
 * @method static integer                                                    countByRed_card($val, array $opt=array())
 * @method static integer                                                    countByBlue_card($val, array $opt=array())
 * @method static integer                                                    countByIs_home_or_guest($val, array $opt=array())
 */
class H4aPlayerscoresModel extends Model
{
    protected static $strTable = 'tl_h4a_playerscores';

    /**
     * @param array<mixed> $players
     * @param int          $pid
     * @param string       $teamname
     * @param int          $home_guest
     */
    public static function savePlayerscores($players, $pid, $teamname, $home_guest): void
    {
        foreach ($players as $player) {
            if (!empty($player['3rd_suspension'])) {
                $suspensions = 3;
            } elseif (!empty($player['2nd_suspension'])) {
                $suspensions = 2;
            } elseif (!empty($player['1st_suspension'])) {
                $suspensions = 1;
            } else {
                $suspensions = 0;
            }

            $objPlayerscore = self::findBy(
                ['pid = ?', 'name = ?'],
                [$pid, $player['name']],
            );

            if (null === $objPlayerscore) {
                $objPlayerscore = new self();
            }

            $objPlayerscore->pid = $pid;
            $objPlayerscore->tstamp = time();
            $objPlayerscore->team_name = $teamname;
            $objPlayerscore->is_home_or_guest = $home_guest;
            $objPlayerscore->number = $player['number'];
            $objPlayerscore->name = $player['name'];
            $objPlayerscore->goals = $player['goals'];
            $objPlayerscore->penalty_goals = $player['penalty_goals'];
            $objPlayerscore->penalty_tries = $player['penalty_tries'];
            $objPlayerscore->yellow_card = $player['yellow_card'];
            $objPlayerscore->suspensions = $suspensions;
            $objPlayerscore->red_card = $player['red_card'];
            $objPlayerscore->blue_card = $player['blue_card'];
            $objPlayerscore->save();
        }
    }

    /**
     * @param string $pid
     *
     * @return array<mixed>
     */
    public static function findScoresByCalendarEvent($pid)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                `is_home_or_guest`
                ,`team_name`
                ,`number`
                ,`name`
                , SUM(`goals`) AS `goals`
                , SUM(`penalty_goals`) AS `penalty_goals`
                , SUM(`penalty_tries`) AS `penalty_tries`
                , SUM(`yellow_card`) AS `yellow_cards`
                , SUM(`suspensions`) AS `suspensions`
                , SUM(`red_card`) AS `red_cards`
                , SUM(`blue_card`) AS `blue_cards`
            FROM
                `tl_h4a_playerscores`
            WHERE
                `pid` = ?
            GROUP BY
                `is_home_or_guest`
                ,`team_name`
                ,`name`
                ,`number`
            ORDER BY
                `is_home_or_guest`,`team_name`,`name`',
            [$pid],
        );

        return $stmt->fetchAll();
    }

    /**
     * @param string $classId
     * @param string $team_name
     *
     * @return array<mixed>
     */
    public static function findScoresByClassIdAndTeamName($classId, $team_name)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                ps.`name`
                , COUNT(ce.`gGameID`) AS `games`
                , SUM(ps.`goals`) AS `goals`
                , SUM(ps.`penalty_goals`) AS `penalty_goals`
                , SUM(ps.`penalty_tries`) AS `penalty_tries`
                , SUM(ps.`yellow_card`) AS `yellow_cards`
                , SUM(ps.`suspensions`) AS `suspensions`
                , SUM(ps.`red_card`) AS `red_cards`
                , SUM(ps.`blue_card`) AS `blue_cards`
            FROM
                `tl_h4a_playerscores` ps
            JOIN
                `tl_calendar_events` ce
            ON
                ps.`pid` = ce.`id`
            WHERE
                ce.`gClassID` = ? AND
                ps.`team_name`= ? AND
                ps.`number` NOT IN ("A", "B", "C", "D")
            GROUP BY
                ps.`name`
            ORDER BY
                ps.`name` ASC',
            [$classId, $team_name],
        );

        return $stmt->fetchAll();
    }

    /**
     * @param string $season
     * @param string $team_name
     *
     * @return array<mixed>
     */
    public static function findScoresBySeasonAndTeamName($season, $team_name)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                ps.`name`
                , COUNT(ce.`gGameID`) AS `games`
                , SUM(ps.`goals`) AS `goals`
                , SUM(ps.`penalty_goals`) AS `penalty_goals`
                , SUM(ps.`penalty_tries`) AS `penalty_tries`
                , SUM(ps.`yellow_card`) AS `yellow_cards`
                , SUM(ps.`suspensions`) AS `suspensions`
                , SUM(ps.`red_card`) AS `red_cards`
                , SUM(ps.`blue_card`) AS `blue_cards`
            FROM
                `tl_h4a_playerscores` ps
            JOIN
                `tl_calendar_events` ce
            ON
                ps.`pid` = ce.`id`
            WHERE
                ce.`h4a_season` = ? AND
                ps.`team_name`= ?
            GROUP BY
                ps.`name`
            ORDER BY
                ps.`name` ASC',
            [$season, $team_name],
        );

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param string $season
     * @param string $classShortname
     * @param string $team_name
     *
     * @return array<mixed>
     */
    public static function findScoresBySeasonAndClassNameAndTeamName($season, $className, $team_name)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                ps.`name`
                , COUNT(ce.`gGameID`) AS `games`
                , SUM(ps.`goals`) AS `goals`
                , SUM(ps.`penalty_goals`) AS `penalty_goals`
                , SUM(ps.`penalty_tries`) AS `penalty_tries`
                , SUM(ps.`yellow_card`) AS `yellow_cards`
                , SUM(ps.`suspensions`) AS `suspensions`
                , SUM(ps.`red_card`) AS `red_cards`
                , SUM(ps.`blue_card`) AS `blue_cards`
            FROM
                `tl_h4a_playerscores` ps
            JOIN
                `tl_calendar_events` ce
            ON
                ps.`pid` = ce.`id`
            WHERE
                ce.`h4a_season` = ? AND
                ce.`gClassName` = ? AND
                ps.`team_name`= ?
            GROUP BY
                ps.`name`
            ORDER BY
                ps.`name` ASC',
            [$season, $className, $team_name],
        );

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param int    $pid
     * @param string $home_guest (1 = home, 2 = guest)
     *
     * @return array<mixed>
     */
    public static function findTeamScoresByCalendarEvent($pid, $home_guest)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT
                `team_name`
                ,`number`
                ,`name`
                , SUM(`goals`) AS `goals`
                , SUM(`penalty_goals`) AS `penalty_goals`
                , SUM(`penalty_tries`) AS `penalty_tries`
                , SUM(`yellow_card`) AS `yellow_cards`
                , SUM(`suspensions`) AS `suspensions`
                , SUM(`red_card`) AS `red_cards`
                , SUM(`blue_card`) AS `blue_cards`
            FROM
                `tl_h4a_playerscores`
            WHERE
                `pid` = ? AND
                `is_home_or_guest` = ?
            GROUP BY
                `name`
                ,`number`
            ORDER BY
                `number`,`name`',
            [$pid, $home_guest],
        );

        return $stmt->fetchAll();
    }
}
