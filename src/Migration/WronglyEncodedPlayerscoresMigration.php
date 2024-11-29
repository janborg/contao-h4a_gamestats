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
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;

class WronglyEncodedPlayerscoresMigration extends AbstractMigration
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
        if (!$schemaManager->tablesExist(['tl_h4a_playerscores'])) {
            return false;
        }

        $stmt = $this->connection->executeQuery('
            SELECT id, name FROM tl_h4a_playerscores
        ');

        $allPlayerNames = $stmt->fetchAllAssociative();

        $wrongEncodedPlayerNames = array_filter(
            $allPlayerNames,
            static fn ($player): bool => (bool) preg_match('/\?/', $player['name']),
        );

        return !empty($wrongEncodedPlayerNames);
    }

    public function run(): MigrationResult
    {
        $this->framework->initialize();

        $stmt = $this->connection->executeQuery('
            SELECT id, pid, name FROM tl_h4a_playerscores
        ');

        $allPlayerNames = $stmt->fetchAllAssociative();

        $wrongEncodedPlayerNames = array_filter(
            $allPlayerNames,
            static fn ($player): bool => (bool) preg_match('/\?/', $player['name']),
        );

        foreach ($wrongEncodedPlayerNames as $playerscore) {
            $objCalendarEvent = CalendarEventsModel::findById($playerscore['pid']);

            $h4areportparser = new H4aReportParser($objCalendarEvent->sGID);

            $h4areportparser->parseReport();

            // Spieler der Heimmannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->home_team, $objCalendarEvent->id, $h4areportparser->heim_name, $home_guest = 1);

            // Spieler der Gastmannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->guest_team, $objCalendarEvent->id, $h4areportparser->gast_name, $home_guest = 2);

            // Delete the old Playerscore
            H4aPlayerscoresModel::findById($playerscore['id'])->delete();

            $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);
        }

        return $this->createResult(
            true,
            'Updated '.\count($wrongEncodedPlayerNames).' playerscore.',
        );
    }
}
