<?php

/**
 * Add child table
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['config']['ctable'][] = 'tl_h4a_playerscores';


/**
 * Add list operations
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_playerscores'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['operationh4a_playerscores'],
    'href' => 'table=tl_h4a_playerscores',
    'icon' => 'bundles/janborgh4agamestats/icon/h4a_playerscores.svg',
];
