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

use Contao\CalendarEventsModel;
use Contao\ContentModel;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Janborg\H4aGamestats\Controller\ContentElement\H4aGameScoreElement;
use Janborg\H4aGamestats\Controller\ContentElement\H4aTimelineElement;

class MigrateGameScoreAndTimelineElementsMigration extends AbstractMigration
{
    public function __construct(private Connection $connection)
    {
    }

    public function getName(): string
    {
        return 'Handballnet: Migriere h4a_gamescore/h4a_timeline Content Elements von h4a_event_id auf handballnet_game_id';
    }

    public function shouldRun(): bool
    {
        if (!$this->tableExists('tl_content')) {
            return false;
        }

        $columns = $this->connection->createSchemaManager()->listTableColumns('tl_content');

        foreach (['type', 'h4a_event_id', 'handballnet_game_id', 'handballnet_game_season', 'handballnet_game_tournament'] as $required) {
            if (!isset($columns[strtolower($required)])) {
                return false;
            }
        }

        $count = $this->connection->fetchOne(
            "SELECT COUNT(*) FROM tl_content
             WHERE type IN (?, ?)
               AND COALESCE(h4a_event_id, 0) > 0
               AND COALESCE(handballnet_game_id, '') = ''",
            [H4aGameScoreElement::TYPE, H4aTimelineElement::TYPE],
        );

        return (int) $count > 0;
    }

    public function run(): MigrationResult
    {
        $rows = $this->connection->fetchAllAssociative(
            "SELECT id, h4a_event_id FROM tl_content
             WHERE type IN (?, ?)
               AND COALESCE(h4a_event_id, 0) > 0
               AND COALESCE(handballnet_game_id, '') = ''",
            [H4aGameScoreElement::TYPE, H4aTimelineElement::TYPE],
        );

        $migrated = 0;
        $withoutEvent = 0;

        foreach ($rows as $row) {
            $contentElement = ContentModel::findById($row['id']);

            if (null === $contentElement) {
                continue;
            }

            $eventId = (int) $row['h4a_event_id'];

            // Die lokale tl_calendar_events.id bleibt beim Handballnet-Wechsel erhalten,
            // daher kann h4a_event_id direkt als handballnet_game_id übernommen werden.
            $contentElement->handballnet_game_id = (string) $eventId;

            $event = CalendarEventsModel::findById($eventId);

            if (null !== $event) {
                $contentElement->handballnet_game_season = (string) $event->handballnet_season;
                $contentElement->handballnet_game_tournament = (string) $event->handballnet_tournament_id;
            } else {
                ++$withoutEvent;
            }

            $contentElement->save();

            ++$migrated;
        }

        return new MigrationResult(
            true,
            \sprintf(
                '%d Spielbericht-/Timeline-Element(e) migriert (h4a_event_id → handballnet_game_id). '.
                '%d davon ohne zugehöriges Event – dort konnten Season und Tournament nicht gesetzt werden.',
                $migrated,
                $withoutEvent,
            ),
        );
    }

    private function tableExists(string $tableName): bool
    {
        return $this->connection->createSchemaManager()->tablesExist([$tableName]);
    }
}
