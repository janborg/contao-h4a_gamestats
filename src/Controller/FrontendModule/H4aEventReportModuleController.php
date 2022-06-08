<?php

declare(strict_types=1);


namespace Janborg\H4aGamestats\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Template;
use Contao\CalendarEventsModel;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Janborg\H4aGamestats\H4aEventGamestats;

/**
 * @FrontendModule(type=H4aEventReportModuleController::TYPE, category="events")
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

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $event = $this->h4aEventGamestats->getCurrentEvent();

        if (null === $event)  { 
            return new Response();
        }

        $this->h4aEventGamestats->addHomeStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addGuestStatsToTemplate($template, $event);

        return $template->getResponse();

    }
}