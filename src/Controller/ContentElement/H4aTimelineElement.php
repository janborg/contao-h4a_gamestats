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
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(type=H4aTimelineElement::TYPE,
 *   category="handball4all",
 *   template="ce_h4a_timeline",
 * )
 */
class H4aTimelineElement extends AbstractContentElementController
{
    public const TYPE = 'h4a_timeline';

    public function __construct(
        private H4aEventGamestats $h4aEventGamestats,
        private ScopeMatcher $scopeMatcher,
        private EntityCacheTags $entityCacheTags,
    ) {
    }

    public function getResponse(Template $template, ContentModel $model, Request $request): Response
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '## H4a Timeline ##';

            return new Response($template->parse());
        }

        $event = CalendarEventsModel::findByIdOrAlias($model->h4a_event_id);

        $this->h4aEventGamestats->addTimelineToTemplate($template, $event);

        $GLOBALS['TL_JAVASCRIPT'][] = 'bundles/janborgh4agamestats/js/chart.umd.min.js';

        $this->entityCacheTags->tagWith($event);

        return $template->getResponse();
    }
}
