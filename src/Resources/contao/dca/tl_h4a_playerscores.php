<?php

declare(strict_types=1);

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
        'dataContainer' => 'Table',
        'ptable' => 'tl_calendar_events',
        'doNotCopyRecords' => true,
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
            'mode' => 4,
            'flag' => 12,
            'headerFields' => ['title', 'startDate', 'endDate'],
            'fields' => ['reportNo', 'gameNo', 'team_name'],
            'panelLayout' => 'sort,filter;search,limit',
        ],

        'label' => [
            'fields' => ['team_name', 'name', 'reportNo', 'gameNo'],
            'format' => '%s - %s (Report %s / Game %s)',
        ],

        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
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
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
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
        'default' => '{title_legend},gClassID,reportNo,gameNo,team_name;{player_legend},name,goals,penalty_goals,penalty_tries,yellow_card,suspensions,red_card,blue_card',
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

        'reportNo' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_playerscores']['reportNo'],
            'exclude' => true,
            'sorting' => true,
            'filter' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 255, 'tl_class' => 'w50'],
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
    ],
];
