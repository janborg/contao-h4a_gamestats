<?php

declare(strict_types=1);

use Janborg\H4aGamestats\Controller\FrontendModule\H4aEventReportController;

$GLOBALS['TL_DCA']['tl_module']['palettes'][H4aEventReportController::TYPE] = '{title_legend},name,headline,type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID';