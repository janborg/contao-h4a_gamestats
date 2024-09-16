<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Controller\FrontendModule;

use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\Template;
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=H4aEventReportModuleController::TYPE, category="events", template="mod_h4a_event_report")
 */
class H4aEventReportModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'h4a_event_report';

    public function __construct(
        private H4aEventGamestats $h4aEventGamestats,
        private EntityCacheTags $entityCacheTags,
    ) {
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): Response
    {
        $event = $this->h4aEventGamestats->getCurrentEvent();

        if (null === $event) {
            return new Response();
        }

        $this->h4aEventGamestats->addHomeStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addGuestStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addTimelineToTemplate($template, $event);

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/janborgh4agamestats/js/chart.umd.min.js';

        $this->entityCacheTags->tagWith($event);

        return $template->getResponse();
    }
}
