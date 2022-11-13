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
            'mode' => Contao\DataContainer::MODE_PARENT,
            'flag' => Contao\DataContainer::SORT_ASC,
            'headerFields' => ['title', 'startDate', 'starttime', 'sGID', 'gHomeGoals', 'gGuestGoals'],
            'fields' => ['is_home_or_guest', 'goals'],
            'panelLayout' => 'sort;filter',
            'child_record_callback' => ['tl_h4a_playerscores', 'listPlayerScores',
            ],
        ],

        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
            'lookup_scores' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['lookup_scores'],
                'href' => 'key=lookup_scores',
                'class' => 'header_lookup_scores',
                'icon' => 'bundles/janborgh4agamestats/icon/data-update.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],

        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right:3px"',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{player_legend},team_name,name,is_home_or_guest,number;{score_legend},goals,penalty_goals,penalty_tries,yellow_card,suspensions,red_card,blue_card',
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
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['number'],
            'sorting' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'team_name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['team_name'],
            'exclude' => true,
            'sorting' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'name' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['name'],
            'exclude' => true,
            'sorting' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],

        'goals' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['goals'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'penalty_goals' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['penalty_goals'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'penalty_tries' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['penalty_tries'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(2) unsigned NOT NULL default '0'",
        ],

        'yellow_card' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['yellow_card'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'suspensions' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['suspensions'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'red_card' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['red_card'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],

        'blue_card' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['blue_card'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 1, 'rgxp' => 'natural', 'tl_class' => 'w50'],
            'sql' => "int(1) unsigned NOT NULL default '0'",
        ],

        'is_home_or_guest' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['home_or_guest'],
            'default' => 1,
            'exclude' => true,
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
