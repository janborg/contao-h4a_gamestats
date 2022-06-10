<?php

declare(strict_types=1);

/*
 * This file is part of hsg-heilbronn website.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Controller\ContentElement;

use Contao\CalendarEventsModel;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("h4a_timeline",
 *   category="handball4all",
 *   template="ce_h4a_timeline",
 * )
 */
class H4aTimelineElement extends AbstractContentElementController
{
    public function getResponse(Template $template, ContentModel $model, Request $request): Response|null
    {
        $timeline = H4aTimelineModel::findAllGoalsByCalendarEvent($model->h4a_event_id);
        $objCalEvent = CalendarEventsModel::findByPk($model->h4a_event_id);

        $template->timeline = $timeline;
        $template->home_team = $objCalEvent->gHomeTeam;
        $template->guest_team = $objCalEvent->gGuestTeam;

        return $template->getResponse();
    }
}
