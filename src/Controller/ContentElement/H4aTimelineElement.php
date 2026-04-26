<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\CalendarEventsModel;
use Contao\ContentModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Date;
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(H4aTimelineElement::TYPE, 'handball4all', 'ce_h4a_timeline')]
class H4aTimelineElement extends AbstractContentElementController
{
    public const TYPE = 'h4a_timeline';

    public function __construct(
        private H4aEventGamestats $h4aEventGamestats,
        private ScopeMatcher $scopeMatcher,
        private EntityCacheTags $entityCacheTags,
    ) {
    }

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        $event = CalendarEventsModel::findByIdOrAlias($model->h4a_event_id);

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = Date::parse('d.m.Y', $event->startDate).' | '.$event->title;

            return new Response($template->parse());
        }

        $this->h4aEventGamestats->addTimelineToTemplate($template, $event);

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/janborgh4agamestats/js/chart.umd.min.js';

        $this->entityCacheTags->tagWith($event);

        return $template->getResponse();
    }
}
