<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

$GLOBALS['TL_DCA']['tl_calendar_events']['config']['ctable'][] = 'tl_h4a_playerscores';
$GLOBALS['TL_DCA']['tl_calendar_events']['config']['ctable'][] = 'tl_h4a_timeline';

/*
 * Add list operations
 */
$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_playerscores'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['operationh4a_playerscores'],
    'href' => 'table=tl_h4a_playerscores',
    'icon' => 'bundles/janborgh4agamestats/icon/edit-list.svg',
];
$GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_timeline'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_calendar_events']['operationh4a_timeline'],
    'href' => 'table=tl_h4a_timeline',
    'icon' => 'bundles/janborgh4agamestats/icon/timeline.svg',
];
