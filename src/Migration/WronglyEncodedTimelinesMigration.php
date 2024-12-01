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

        $results = $this->connection->fetchAllAssociative(
            "SELECT id, action_player
               FROM tl_h4a_timeline
              WHERE tl_h4a_timeline.action_player LIKE '%\\?%'",
        );

        return !empty($results);
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $wronglyEncodedPlayerNames = $this->connection->fetchAllAssociative(
            "SELECT id, pid, action_player
               FROM tl_h4a_timeline
              WHERE tl_h4a_timeline.action_player LIKE '%\\?%'",
        );

        foreach ($wronglyEncodedPlayerNames as $player) {
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
            'Updated '.\count($wronglyEncodedPlayerNames).' timeline items.',
        );
    }
}
