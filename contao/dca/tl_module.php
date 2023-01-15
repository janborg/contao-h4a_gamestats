<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

use \Contao\Controller;
use Janborg\H4aGamestats\Controller\FrontendModule\H4aEventReportModuleController;

/**
 * Palettes
 */

$GLOBALS['TL_DCA']['tl_module']['palettes'][H4aEventReportModuleController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl,showGamescores,showTimeline;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';


/**
 * fields
 */

$GLOBALS['TL_DCA']['tl_module']['fields'] = array_merge(
    ['showGamescores' => [
        'label'                   => &$GLOBALS['TL_LANG']['tl_module']['showGamescores'],
        'default'                 => 1,
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('tl_class' => 'w50 clr'),
        'sql'                     => "char(1) NOT NULL default ''"
    ]],
    ['showTimeline' => [
        'label'                   => &$GLOBALS['TL_LANG']['tl_table_name']['showTimeline'],
        'default'                 => 1,
        'exclude'                 => true,
        'inputType'               => 'checkbox',
        'eval'                    => array('tl_class' => 'w50 clr'),
        'sql'                     => "char(1) NOT NULL default ''"
    ]],
    $GLOBALS['TL_DCA']['tl_module']['fields']
);
