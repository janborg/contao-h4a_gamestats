<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

use Janborg\H4aGamestats\Controller\ContentElement\H4aGameScoreElement;
use Janborg\H4aGamestats\Controller\ContentElement\H4aSeasonScoreElement;
use Janborg\H4aGamestats\Controller\ContentElement\H4aTimelineElement;

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

/*
 * palettes
 */

$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aGameScoreElement::TYPE] = '
    {type_legend},type,headline;
    {h4a_legend},team_calendar,handballnet_club,handballnet_season,handballnet_game_id;
    {template_legend:hide},customTpl;
    {expert_legend:hide},cssID
';
$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aSeasonScoreElement::TYPE] = '
    {type_legend},type,headline;
    {h4a_legend},team_calendar,handballnet_club,handballnet_season,my_team_name;
    {template_legend:hide}customTpl;
    {expert_legend:hide},cssID
';
$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aTimelineElement::TYPE] = '
    {type_legend},type,headline;
    {h4a_legend},team_calendar,handballnet_club,handballnet_season,handballnet_game_id;
    {template_legend:hide},customTpl;
    {expert_legend:hide},cssID
';

/*
 * Fields
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['team_calendar'] = [
    'search' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_calendar.title',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];
$GLOBALS['TL_DCA']['tl_content']['fields']['handballnet_club'] = [
    'foreignKey' => 'tl_hn_clubs.name',
    'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
    'inputType' => 'select',
    'eval' => [
        'mandatory' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'chosen' => true,
        'submitOnChange' => true,
    ],
    'sql' => "varchar(10) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['handballnet_season'] = [
    'inputType' => 'select',
    'eval' => [
        'mandatory' => true,
        'tl_class' => 'w50',
        'includeBlankOption' => true,
        'chosen' => true,
        'submitOnChange' => true,
    ],
    'sql' => "varchar(10) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['handballnet_tournament_id'] = [
    'inputType' => 'select',
    'eval' => [
        'mandatory' => false,
        'includeBlankOption' => true,
        'maxlength' => 255,
        'tl_class' => 'w50',
    ],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_content']['fields']['handballnet_game_id'] = [
    'inputType' => 'text',
    'eval' => [
        'mandatory' => false,
        'unique' => true,
        'tl_class' => 'w50',
    ],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];