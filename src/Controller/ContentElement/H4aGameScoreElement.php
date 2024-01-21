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
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Janborg\H4aGamestats\H4aEventGamestats;
use Symfony\Bridge\Twig\NodeVisitor\Scope;
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
     * @var ScopeMatcher
     */
    private $h4aEventGamestats;
    private $scopeMatcher;

    public function __construct(H4aEventGamestats $h4aEventGamestats, ScopeMatcher $scopeMatcher)
    {
        $this->h4aEventGamestats = $h4aEventGamestats;
        $this->scopeMatcher = $scopeMatcher;
    }

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        if ($this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '## H4a Gamescores ##';

            return new Response($template->parse());
        }


        $event = CalendarEventsModel::findByIdOrAlias($model->h4a_event_id);

        $this->h4aEventGamestats->addHomeStatsToTemplate($template, $event);
        $this->h4aEventGamestats->addGuestStatsToTemplate($template, $event);

        return $template->getResponse();
    }
}
