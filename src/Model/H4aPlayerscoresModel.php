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
use Contao\CalendarModel;

/**
 * add properties for IDE support.
 */
class H4aPlayerscoresModel extends Model
{
    protected static $strTable = 'tl_h4a_playerscores';

    /**
     * @param array $players
     * @param string $pid
     * @param string $teamname
     */
    public static function savePlayerscores($players, $pid, $teamname): void
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
            $objPlayerscore->nummer = $player['nummer'];
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
     * @var string
     *
     * @return array
     */
    public static function findScoresByCalendarEvent($pid)
    {
        $db = System::getContainer()->get('database_connection');

        $stmt = $db->executeQuery(
            'SELECT 
                `team_name`
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
                `team_name`
                ,`name` 
            ORDER BY 
                `team_name`,`name`', 
            [$pid]); 

        return $stmt->fetchAll();
    }

    /**
     * @var string
     * @var string
     *
     * @return array
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
                ps.`team_name`= ? 
            GROUP BY 
                ps.`name` 
            ORDER BY 
                ps.`name` ASC'
            , [$classId, $team_name]);

        return $stmt->fetchAll();
    }
}
