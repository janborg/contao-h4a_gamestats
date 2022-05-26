<?php

declare(strict_types=1);


namespace Janborg\H4aGamestats\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\Input;
use Contao\ModuleModel;
use Contao\Template;
use Contao\CalendarEventsModel;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule(type=H4aEventReportController::TYPE, category="events")
 */
class H4aEventReportController extends AbstractFrontendModuleController
{
    public const TYPE = 'h4a_event_report';

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $item = Input::get('auto_item');

        if (empty($item)) {
            return null;
        }

        $event = CalendarEventsModel::findByIdOrAlias($item);

        if (null === $event)  { 
            return new Response();
        }

        $playerscores = H4aPlayerscoresModel::findScoresByCalendarEvent($event->id);

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

        return $template->getResponse();

    }
}