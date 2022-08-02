<?php

declare(strict_types=1);

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

$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aGameScoreElement::TYPE] = '{type_legend},type,headline;{h4a_legend},team_calendar,h4a_season, h4a_event_id;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aSeasonScoreElement::TYPE] = '{type_legend},type,headline;{h4a_legend},team_calendar,h4a_season, my_team_name;{expert_legend:hide},cssID';
$GLOBALS['TL_DCA']['tl_content']['palettes'][H4aTimelineElement::TYPE] = '{type_legend},type,headline;{h4a_legend},team_calendar,h4a_season, h4a_event_id;{expert_legend:hide},cssID';

/*
 * Fields
 */

$GLOBALS['TL_DCA']['tl_content']['fields']['team_calendar'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['team_calendar'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_calendar.title',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['h4a_season'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['h4a_season'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_h4a_seasons.season',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'maxlength' => 10, 'tl_class' => 'w50', 'chosen' => true, 'submitOnChange' => true],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['h4a_event_id'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_content']['h4a_event_id'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'select',
    'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'chosen' => true],
    'sql' => 'int(10) unsigned NOT NULL default 0',
];
