<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Migration;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;

class WronglyEncodedTimelinesMigration extends AbstractMigration
{
    public function __construct(
        private Connection $connection,
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
    ) {
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        // If the database table already itself exists we should do nothing
        if (!$schemaManager->tablesExist(['tl_h4a_timeline'])) {
            return false;
        }

        $stmt = $this->connection->executeQuery('
            SELECT id, action_player FROM tl_h4a_timeline
        ');

        $allPlayerNames = $stmt->fetchAllAssociative();

        $wrongEncodedPlayerNames = array_filter(
            $allPlayerNames,
            static fn ($player): bool => (bool) preg_match('/\?/', $player['action_player']),
        );

        return !empty($wrongEncodedPlayerNames);
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $stmt = $this->connection->executeQuery('
            SELECT id, pid, action_player FROM tl_h4a_timeline
        ');

        $allPlayerNames = $stmt->fetchAllAssociative();

        $wrongEncodedPlayerNames = array_filter(
            $allPlayerNames,
            static fn ($player): bool => (bool) preg_match('/\?/', $player['action_player']),
        );

        foreach ($wrongEncodedPlayerNames as $player) {
            $objCalendarEvent = CalendarEventsModel::findById($player['pid']);

            $h4areportparser = new H4aReportParser($objCalendarEvent->sGID);

            $h4areportparser->parseReport();

            // Timeline des Spiels abrufen
            H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objCalendarEvent->id);

            // Delete the old Timeline Item
            H4aTimelineModel::findById($player['id'])->delete();

            $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);
        }

        return $this->createResult(
            true,
            'Updated '.\count($wrongEncodedPlayerNames).' timeline item.',
        );
    }
}
