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

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Template;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @ContentElement("h4a_seasonscore",
 *   category="handball4all",
 *   template="ce_h4a_seasonscore",
 * )
 */
class H4aSeasonScoreElement extends AbstractContentElementController
{
    public function getResponse(Template $template, ContentModel $model, Request $request): Response|null
    {
        $playerscores = H4aPlayerscoresModel::findScoresBySeasonAndTeamName($model->h4a_season, $model->my_team_name);

        $template->playerscores = $playerscores;

        return $template->getResponse();
    }
}
