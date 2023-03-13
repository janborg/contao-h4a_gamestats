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
use Contao\System;

/**
 * add properties for IDE support.
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
            [$pid]
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
            [$classId, $team_name]
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
            [$season, $team_name]
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
            [$pid, $home_guest]
        );

        return $stmt->fetchAll();
    }
}
