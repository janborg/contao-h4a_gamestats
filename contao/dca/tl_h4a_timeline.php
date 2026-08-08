<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

use Contao\Backend;
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\DC_Table;

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_h4a_timeline'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_calendar_events',
        'doNotCopyRecords' => true,
        'doNotDeleteRecords' => false,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'flag' => DataContainer::SORT_INITIAL_LETTERS_ASC,
            'headerFields' => ['title', 'startDate', 'startTime', 'homeGoals', 'awayGoals'],
            'fields' => ['matchtime'],
            'panelLayout' => 'sort;filter',
            'child_record_callback' => ['tl_h4a_timeline', 'listTimelineActions',
            ],
        ],

        'global_operations' => [
            'all',
            'lookup_timeline' => [
                'href' => 'key=lookup_timeline',
                'class' => 'header_lookup_timeline',
                'icon' => 'bundles/janborgh4agamestats/icon/data-update.svg',
                'primary' => true
            ],
        ],

        'operations' => [
            'edit',
            'delete',
            'show',
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{timeline_legend},matchtime,currentscore,action_player_number,action_player,action_team,action_type',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'matchtime' => [
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(32) unsigned NOT NULL default ''",
        ],
        'currentscore' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 7, 'tl_class' => 'w50'],
            'sql' => "varchar(7) unsigned NOT NULL default ''",
        ],
        'action_team' => [
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 48, 'tl_class' => 'w50'],
            'sql' => "varchar(48) NOT NULL default ''",
        ],
        'action_player' => [
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 48, 'tl_class' => 'w50'],
            'sql' => "varchar(48) NOT NULL default ''",
        ],
        'action_player_number' => [
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'action_type' => [
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 32, 'tl_class' => 'w50'],
            'sql' => "varchar(32) NOT NULL default ''",
        ],
    ],
];

class tl_h4a_timeline extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function listTimelineActions($arrRow)
    {
        return '<div class="tl_content_left">'.$arrRow['matchtime'].' - '.$arrRow['action_type'].' - '.$arrRow['action_team'].' <span style="color:#999;padding-left:3px"> (Spieler: '.$arrRow['action_player'].' ('.$arrRow['action_player_number'].') | Spielstand:'.$arrRow['currentscore'].')</span>'."</div>\n";
    }
}
