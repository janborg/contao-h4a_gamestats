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
        'dataContainer' => 'Table',
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
            'mode' => 4,
            'flag' => 3,
            'headerFields' => ['title', 'startDate', 'starttime', 'sGID', 'gHomeGoals', 'gGuestGoals'],
            'fields' => ['matchtime'],
            'panelLayout' => 'sort;filter',
            'child_record_callback' => ['tl_h4a_timeline', 'listTimelineActions',
            ],
        ],

        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
            'lookup_timeline' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['lookup_timeline'],
                'href' => 'key=lookup_timeline',
                'class' => 'header_lookup_timeline',
                'icon' => 'bundles/janborgh4agamestats/icon/data-update.svg',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
            ],
        ],

        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\''.($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? null).'\'))return false;Backend.getScrollOffset()"',
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif',
                'attributes' => 'style="margin-right:3px"',
            ],
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
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['matchtime'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50'],
            'sql' => "varchar(32) unsigned NOT NULL default ''",
        ],
        'currentscore' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['currentscore'],
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 7, 'tl_class' => 'w50'],
            'sql' => "varchar(7) unsigned NOT NULL default ''",
        ],
        'action_team' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['action_team'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 48, 'tl_class' => 'w50'],
            'sql' => "varchar(48) NOT NULL default ''",
        ],
        'action_player' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['action_player'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 48, 'tl_class' => 'w50'],
            'sql' => "varchar(48) NOT NULL default ''",
        ],
        'action_player_number' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['action_player_number'],
            'exclude' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 2, 'tl_class' => 'w50'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'action_type' => [
            'label' => &$GLOBALS['TL_LANG']['tl_h4a_timeline']['action_type'],
            'exclude' => true,
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
        $this->import('BackendUser', 'User');
    }

    public function listTimelineActions($arrRow)
    {
        return '<div class="tl_content_left">'.$arrRow['matchtime'].' - '.$arrRow['action_type'].' - '.$arrRow['action_team'].' <span style="color:#999;padding-left:3px"> (Spieler: '.$arrRow['action_player'].' ('.$arrRow['action_player_number'].') | Spielstand:'.$arrRow['currentscore'].')</span>'."</div>\n";
    }
}
