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

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\ModuleModel;
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=H4aEventReportModuleController::TYPE, category="events", template="mod_h4a_event_report")
 */
class H4aEventReportModuleController extends AbstractFrontendModuleController
{
    public const TYPE = 'h4a_event_report';

    /**
     * @var H4aEventGamestats
     */
    private $h4aEventGamestats;

    public function __construct(H4aEventGamestats $h4aEventGamestats)
    {
        $this->h4aEventGamestats = $h4aEventGamestats;
    }

    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        $event = $this->h4aEventGamestats->getCurrentEvent();

        if (null === $event) {
            return new Response();
        }

        $this->h4aEventGamestats->addHomeStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addGuestStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addTimelineToTemplate($template, $event);

        return $template->getResponse();
    }
}
