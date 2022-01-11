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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;

/**
 * @ContentElement("h4a_gamescore",
 *   category="handball4all",
 *   template="ce_h4a_gamescore",
 * )
 */
class H4aGameScoreElement extends AbstractContentElementController
{
    public function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $playerscores = H4aPlayerscoresModel::findScoresByCalendarEvent($model->h4a_event_id);

        $team_1_players = "";
        $team_2_players = "";
        $team_1_suspensions = "";
        $team_2_suspensions = "";
        $team_1_yellow_cards = "";
        $team_2_yellow_cards = "";

        $team_1_name = $playerscores[0]['team_name'];
        $team_2_name = end($playerscores)['team_name'];

        foreach ($playerscores as $player) {
            if ($player['team_name'] === $team_1_name) {
                $team_1_players .= $player['name'];
                
                if("0" !== $player['goals']) {
                    $team_1_players .= " (" . $player['goals'];

                    if("0" !== $player['penalty_goals']) {
                        $team_1_players .= "/" . $player['penalty_goals'] ;
                    }

                    $team_1_players .= "), ";
                } else {
                    $team_1_players .= ", ";
                }

                if ("0" !== $player['suspensions']) {
                    $team_1_suspensions .= $player['name'].' ('.$player['suspensions'].'), ';
                }

                if ("0" !== $player['yellow_cards']) {
                    $team_1_yellow_cards .= $player['name'].' ('.$player['yellow_cards'].'), ';
                }
            }

            if ($player['team_name'] === $team_2_name) {
                $team_2_players .= $player['name'];
                
                if("0" !== $player['goals']) {
                    $team_2_players .= " (" . $player['goals'];

                    if("0" !== $player['penalty_goals']) {
                        $team_2_players .= "/" . $player['penalty_goals'] ;
                    }

                    $team_2_players .= "), ";
                } else {
                    $team_2_players .= ", ";
                }

                if ("0" !== $player['suspensions']) {
                    $team_2_suspensions .= $player['name'].' ('.$player['suspensions'].'), ';
                }

                if ("0" !== $player['yellow_cards']) {
                    $team_2_yellow_cards .= $player['name'].' ('.$player['yellow_cards'].'), ';
                }
            }
        }

        $template->team_1_name = $team_1_name;
        $template->team_2_name = $team_2_name;
        $template->team_1_players = null !== $team_1_players ? substr($team_1_players, 0, -2) : '';
        $template->team_2_players = null !== $team_2_players ? substr($team_2_players, 0, -2) : '';

        $template->team_1_suspensions = null !== $team_1_suspensions ? substr($team_1_suspensions, 0, -2) : '';
        $template->team_2_suspensions = null !== $team_2_suspensions ? substr($team_2_suspensions, 0, -2) : '';

        $template->team_1_yellow_cards = null !== $team_1_yellow_cards ? substr($team_1_yellow_cards, 0, -2) : '';
        $template->team_2_yellow_cards = null !== $team_2_yellow_cards ? substr($team_2_yellow_cards, 0, -2) : '';

        $template->class = 'ce_h4a_gamescore';

        return $template->getResponse();
    }
}
