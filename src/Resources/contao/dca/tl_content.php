<?php

/*
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_content']['palettes']['h4a_gamescore'] = '{type_legend},type,headline;{h4a_legend},team_calendar,h4a_season, gGameNo';


/*
 * Fields
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['team_calendar'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['team_calendar'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_calendar.title',
    'eval' => ['includeBlankOption'=>true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
    'sql' => "varchar(10) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['h4a_season'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['h4a_season'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_h4a_seasons.season',
    'eval' => ['includeBlankOption'=>true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
    'sql' => "varchar(10) NOT NULL default ''"
];

$GLOBALS['TL_DCA']['tl_content']['fields']['gGameNo'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['gGameNo'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'eval' => ['includeBlankOption'=>true, 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => "varchar(255) NOT NULL default ''"
];