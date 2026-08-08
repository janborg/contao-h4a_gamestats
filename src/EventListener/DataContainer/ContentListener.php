<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;
use Janborg\H4aTabellen\Model\HandballnetSeasonsModel;
use Janborg\H4aTabellen\Model\HandballnetTeamsModel;

/**
 * @property ContaoFramework $contaoFramework
 * @property Connection      $connection
 */
class ContentListener
{
    public function __construct(
        public ContaoFramework $contaoFramework,
        public Connection $connection,
    ) {}

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.handballnet_game_season.options')]
    public function HandballnetGameSeasonOptionsCallback(DataContainer $dc): array
    {
        $stmt = $this->connection->executeQuery(
            'SELECT DISTINCT
                `handballnet_season`
            FROM
                `tl_calendar_events`
            WHERE
                `pid` = ?
            ORDER BY `handballnet_season` DESC',
            [$dc->activeRecord->team_calendar],
        );

        $options = [];

        while ($row = $stmt->fetchAssociative()) {
            $season = HandballnetSeasonsModel::findById($row['handballnet_season']);
            $options[$row['handballnet_season']] = \sprintf('%s - %s', $season->season_name, $season->club_name);
        }

        return $options;
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.handballnet_game_tournament.options')]
    public function HandballnetGameTournamentOptionsCallback(DataContainer $dc): array
    {
        $stmt = $this->connection->executeQuery(
            'SELECT DISTINCT
                `handballnet_tournament_id`, `handballnet_tournament_name`
            FROM
                `tl_calendar_events`
            WHERE
                `pid` = ? AND
                `handballnet_season` = ?
            ORDER BY `handballnet_tournament_id`',
            [$dc->activeRecord->team_calendar, $dc->activeRecord->handballnet_game_season],
        );

        $options = [];

        while ($row = $stmt->fetchAssociative()) {
            $options[$row['handballnet_tournament_id']] = $row['handballnet_tournament_name'];
        }

        return $options;
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.my_team_id.options')]
    public function HandballnetMyTeamIdOptionsCallback(DataContainer $dc): array
    {
        $options = [];

        $myTeam = HandballnetTeamsModel::findOneByHandballnet_tournament_id($dc->activeRecord->handballnet_game_tournament);

        if (isset($myTeam)) {
            $options[] = $myTeam->handballnet_team_id;
        }

        return $options;
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.my_team_name.options')]
    public function HandballnetMyTeamNmeOptionsCallback(DataContainer $dc): array
    {
        $options = [];

        $myTeam = HandballnetTeamsModel::findOneByHandballnet_tournament_id($dc->activeRecord->handballnet_game_tournament);

        if (isset($myTeam)) {
            $options[] = $myTeam->my_team_name;
        }

        return $options;
    }

    /**
     * @return array<mixed>
     */
    #[AsCallback(table: 'tl_content', target: 'fields.handballnet_game_id.options')]
    public function HandballnetGameIdOptionsCallback(DataContainer $dc): array
    {
        $stmt = $this->connection->executeQuery(
            'SELECT
                `id`, `title`, `startDate`
            FROM
                `tl_calendar_events`
            WHERE
                `pid` = ? AND
                `handballnet_tournament_id` = ?
            ORDER BY `startDate`',
            [$dc->activeRecord->team_calendar, $dc->activeRecord->handballnet_game_tournament],
        );

        $options = [];

        while ($row = $stmt->fetchAssociative()) {
            $options[$row['id']] = date('d.m.Y', (int) $row['startDate']) . ' / ' . $row['title'];
        }

        return $options;
    }
}
