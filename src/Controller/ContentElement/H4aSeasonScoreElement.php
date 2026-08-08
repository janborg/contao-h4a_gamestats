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
use Janborg\H4aTabellen\Model\HandballnetTeamsModel;
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

        if ($this->scopeMatcher->isBackendRequest($request)) {
            $team = HandballnetTeamsModel::findOneByHandballnet_tournament_id($model->handballnet_game_tournament);

            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = $objCalendar->title.' | '.$team->liga_name;

            return new Response($template->parse());
        }

        $playerscores = H4aPlayerscoresModel::findScoresByTournamentIdAndTeamName($model->handballnet_game_tournament, $model->my_team_name);

        $template->playerscores = $playerscores;

        $this->entityCacheTags->tagWith($objCalendar);

        return $template->getResponse();
    }
}
