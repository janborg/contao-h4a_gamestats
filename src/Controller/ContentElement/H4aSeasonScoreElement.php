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
use Contao\CalendarModel;
use Contao\ContentModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aTabellen\Model\H4aSeasonModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(H4aSeasonScoreElement::TYPE, 'handball4all', 'ce_h4a_seasonscore')]
class H4aSeasonScoreElement extends AbstractContentElementController
{
    public const TYPE = 'h4a_seasonscore';

    public function __construct(
        private ScopeMatcher $scopeMatcher,
        private EntityCacheTags $entityCacheTags,
    ) {
    }

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // get h4a_classID and h4aseason from calendar
        $objCalendar = CalendarModel::findById($model->team_calendar);

        $seasons = unserialize($objCalendar->h4a_seasons);

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $objSeason = H4aSeasonModel::findById($model->h4a_season);

            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = $objCalendar->title.' | '.$objSeason->season;

            return new Response($template->parse());
        }

        $saison = array_values(
            array_filter($seasons, static fn ($season) => (int) $season['h4a_saison'] === $model->h4a_season),
        );

        $className = $saison[0]['liga_shortname'] ?? null;

        $saison_id = $saison[0]['h4a_saison'] ?? null;

        $playerscores = H4aPlayerscoresModel::findScoresBySeasonAndClassNameAndTeamName($saison_id, $className, $model->my_team_name);

        $template->playerscores = $playerscores;

        $this->entityCacheTags->tagWith($objCalendar);

        return $template->getResponse();
    }
}
