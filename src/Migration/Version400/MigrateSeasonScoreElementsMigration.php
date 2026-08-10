<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Migration\Version400;

use Contao\ContentModel;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Janborg\H4aGamestats\Controller\ContentElement\H4aSeasonScoreElement;
use Janborg\H4aTabellen\Model\HandballnetTeamsModel;

class MigrateSeasonScoreElementsMigration extends AbstractMigration
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Handballnet: Migriere h4a_seasonscore Content Elements über my_team_name auf Handballnet-Tournament/Team';
    }

    public function shouldRun(): bool
    {
        if (!$this->tableExists('tl_content') || !$this->tableExists('tl_hn_teams')) {
            return false;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns('tl_content');

        foreach (['type', 'my_team_name', 'my_team_id', 'handballnet_game_season', 'handballnet_game_tournament'] as $required) {
            if (!isset($columns[strtolower($required)])) {
                return false;
            }
        }

        $count = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM tl_content
             WHERE type = ?
               AND COALESCE(my_team_name, '') != ''
               AND COALESCE(handballnet_game_tournament, '') = ''",
            [H4aSeasonScoreElement::TYPE],
        );

        return (int) $count > 0;
    }

    public function run(): MigrationResult
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, my_team_name FROM tl_content
             WHERE type = ?
               AND COALESCE(my_team_name, '') != ''
               AND COALESCE(handballnet_game_tournament, '') = ''",
            [H4aSeasonScoreElement::TYPE],
        );

        $linked = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $contentElement = ContentModel::findById($row['id']);

            if (null === $contentElement) {
                continue;
            }

            // Es kann mehrere Teams mit gleichem my_team_name (über Seasons) geben. Aktives
            // und aktuellstes Team bevorzugen.
            $team = HandballnetTeamsModel::findOneBy(
                ['my_team_name = ?'],
                [$row['my_team_name']],
                ['order' => 'is_active DESC, saison DESC, id DESC'],
            );

            if (null === $team) {
                // Team noch nicht via tl_hn_teams synchronisiert. Da die Felder leer bleiben,
                // greift shouldRun() beim nächsten Lauf erneut.
                ++$skipped;
                continue;
            }

            $contentElement->handballnet_game_tournament = $team->handballnet_tournament_id;
            $contentElement->handballnet_game_season = (string) $team->pid;
            $contentElement->my_team_id = $team->handballnet_team_id;
            $contentElement->save();

            ++$linked;
        }

        return new MigrationResult(
            true,
            \sprintf(
                '%d Saison-Statistik-Element(e) verknüpft, %d übersprungen (Team über my_team_name noch nicht synchronisiert).',
                $linked,
                $skipped,
            ),
        );
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->createSchemaManager()->tablesExist([$tableName]);
    }
}
