<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

use Janborg\H4aGamestats\Controller\FrontendModule\H4aEventReportModuleController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][H4aEventReportModuleController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';
