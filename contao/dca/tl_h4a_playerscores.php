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

$GLOBALS['TL_DCA']['tl_h4a_playerscores'] = [
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
            'flag' => DataContainer::SORT_ASC,
            'headerFields' => ['title', 'startDate', 'starttime', 'homeGoals', 'awayGoals'],
            'fields' => ['is_home_or_guest', 'goals'],
            'panelLayout' => 'sort;filter',
            'child_record_callback' => ['tl_h4a_playerscores', 'listPlayerScores',
            ],
        ],

        'global_operations' => [
            'all',
            'lookup_scores' => [
                'href' => 'key=lookup_scores',
                'class' => 'header_lookup_scores',
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
        'default' => '{team_legend},team_name,team_id,is_home_or_guest;{player_legend},name,player_id,number;{score_legend},goals,penalty_goals,penalty_tries,yellow_card,suspensions,red_card,blue_card',
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
        'number' => [
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'player_id' => [
            'sorting' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'readonly' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'team_name' => [
            'sorting' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'team_id' => [
            'sorting' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'readonly' => true, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'name' => [
            'sorting' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'goals' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'penalty_goals' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'penalty_tries' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'yellow_card' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'suspensions' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'red_card' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'blue_card' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'is_home_or_guest' => [
            'default' => 1,
            'search' => true,
            'inputType' => 'radio',
            'options' => [1 => 'Heim', 2 => 'Gast'],
            'eval' => ['tl_class' => 'w50 cbx'],
            'sql' => "char(200) NOT NULL default ''",
        ],
    ],
];

class tl_h4a_playerscores extends Backend
{
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function listPlayerScores($arrRow)
    {
        return '<div class="tl_content_left">'.$arrRow['number'].' - '.$arrRow['name'].' <span style="color:#999;padding-left:3px"> (Tore: '.$arrRow['goals'].' | 7m:'.$arrRow['penalty_goals'].'/'.$arrRow['penalty_tries'].' | G:'.$arrRow['yellow_card'].' | 2m:'.$arrRow['suspensions'].' | R:'.$arrRow['red_card'].')</span>'."</div>\n";
    }
}
