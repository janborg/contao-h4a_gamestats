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

use Contao\CalendarModel;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement(type=H4aSeasonScoreElement::TYPE,
 *   category="handball4all",
 *   template="ce_h4a_seasonscore",
 * )
 */
class H4aSeasonScoreElement extends AbstractContentElementController
{
    public const TYPE = 'h4a_seasonscore';

    public function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        //get h4a_classID and h4aseason from calendar
        $objCalendar = CalendarModel::findById($model->team_calendar);

        $seasons = unserialize($objCalendar->h4a_seasons);

        $saison = array_values(
            array_filter($seasons, function ($season) use ($model) {
            return $season['h4a_saison'] == $model->h4a_season;
        }));

        $classID = $saison[0]['h4a_liga'] ?? null;

        $playerscores = H4aPlayerscoresModel::findScoresByClassIdAndTeamName($classID, $model->my_team_name);

        $template->playerscores = $playerscores;

        return $template->getResponse();
    }
}
