<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats\EventListener\DataContainer;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Contao\System;
use Doctrine\DBAL\Connection;

class ContentListener
{
    public function __construct(ContaoFramework $contaoFramework, Connection $connection)
    {
        $this->contaoFramework = $contaoFramework;
        $this->connection = $connection;
    }


    /**
     * @Callback(table="tl_content", target="fields.gGameNo.options")
     *
     * @param $dc
     */
    public function gGameNoOptionsCallback(DataContainer $dc): array
    {
 
        $stmt = $this->connection->executeQuery('SELECT `title`, `gGameNo`, `startDate` FROM `tl_calendar_events` WHERE `pid` = ? ORDER BY `startDate`', [$dc->activeRecord->team_calendar]);

        $result = $stmt->fetchAll();

        $options = [];

        foreach ($result as $row) {
            $options[$row['gGameNo']] = date('d.m.Y', (int) ($row['startDate'])).' / '.$row['title'];
        }

        return $options;
    }
    
    /**
     * @Callback(table="tl_content", target="fields.h4a_season.options")
     *
     * @param $dc
     */
    public function h4aSeasonOptionsCallback(DataContainer $dc): array
    {
 
        $stmt = $this->connection->executeQuery('SELECT `id`, `season` FROM `tl_h4a_seasons` ORDER BY `season` DESC', [$dc->activeRecord->team_calendar]);

        $result = $stmt->fetchAll();

        $options = [];

        foreach ($result as $row) {
            $options[$row['id']] = $row['season'];
        }

        return $options;
    }

}
