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
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(type=H4aGameScoreElement::TYPE,
 *   category="handball4all",
 *   template="ce_h4a_gamescore",
 * )
 */
class H4aGameScoreElement extends AbstractContentElementController
{
    public const TYPE = 'h4a_gamescore';

    /**
     * @var H4aEventGamestats
     */
    private $h4aEventGamestats;

    public function __construct(H4aEventGamestats $h4aEventGamestats)
    {
        $this->h4aEventGamestats = $h4aEventGamestats;
    }

    public function getResponse(Template $template, ContentModel $model, Request $request): Response|null
    {
        $event = CalendarEventsModel::findByIdOrAlias($model->h4a_event_id);

        $this->h4aEventGamestats->addHomeStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addGuestStatsToTemplate($template, $event);

        return $template->getResponse();
    }
}
