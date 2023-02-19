<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats;

use Contao\CalendarEventsModel;
use Contao\Input;
use Contao\Template;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Janborg\H4aGamestats\Model\H4aTimelineModel;

class H4aEventGamestats
{
    /**
     * Adds stats for Home Team Players and Officials to template.
     */
    public function addHomeStatsToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $hometeamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '1');

        $template->home_team = $event->gHomeTeam;
        
        $template->home_players = $this->isPlayer($hometeamscores);

        $template->home_officials = $this->isOfficial($hometeamscores);

        $template->home_yellow_cards = $this->hasYellowCard($hometeamscores);

        $template->home_suspensions = $this->hasSuspensions($hometeamscores);

        $template->home_red_cards = $this->hasRedCard($hometeamscores);

        $template->home_blue_cards = $this->hasBlueCard($hometeamscores);
    }

    /**
     * Adds stats for Guest Team Players and Officials to template.
     */
    public function addGuestStatsToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $guestteamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '2');

        $template->guest_team = $event->gGuestTeam;

        $template->guest_players = $this->isPlayer($guestteamscores);

        $template->guest_officials = $this->isOfficial($guestteamscores);

        $template->guest_yellow_cards = $this->hasYellowCard($guestteamscores);

        $template->guest_suspensions = $this->hasSuspensions($guestteamscores);

        $template->guest_red_cards = $this->hasRedCard($guestteamscores);

        $template->guest_blue_cards = $this->hasBlueCard($guestteamscores);
    }

    /**
     * Adds timeline to template.
     */
    public function addTimelineToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $timeline = H4aTimelineModel::findAllGoalsByCalendarEvent($event->id);

        $arrChartData[] = [
            'x' => '00:00',
            'home' => 0,
            'guest' => 0,
        ];


        foreach ($timeline as $goal) {
            $arrChartData[] = [
                'x' => $goal['matchtime'],
                'home' => explode(':', $goal['currentscore'])[0],
                'guest' => explode(':', $goal['currentscore'])[1],
            ];
            // if its the last array item, then add x = 60:00 and currentscore = currentscore
            if ($goal === end($timeline)) {
                $arrChartData[] = [
                    'x' => '60:00',
                    'home' => explode(':', $goal['currentscore'])[0],
                    'guest' => explode(':', $goal['currentscore'])[1],
                ];
            }
        }

        $objCalEvent = CalendarEventsModel::findByPk($event->id);

        $template->chartData = json_encode($arrChartData);
        $template->timeline = $timeline;
        $template->home_team = $objCalEvent->gHomeTeam;
        $template->guest_team = $objCalEvent->gGuestTeam;
    }

    /**
     * Returns stats for Home And Guest Team Players and Officials of a given event. 
     * @param array CalendarEventsModel $event
     * @return array<mixed>
     */
    public function getTeamsStats(CalendarEventsModel $event): array
    {
        $hometeamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '1');
        $guestteamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '2');

        $gameStats['homeTeam']['team_name'] = $event->gHomeTeam;
        $gameStats['homeTeam']['players'] = $this->isPlayer($hometeamscores);
        $gameStats['homeTeam']['officials'] = $this->isOfficial($hometeamscores);
        $gameStats['homeTeam']['yellow_cards'] = $this->hasYellowCard($hometeamscores);
        $gameStats['homeTeam']['suspensions'] = $this->hasSuspensions($hometeamscores);
        $gameStats['homeTeam']['red_cards'] = $this->hasRedCard($hometeamscores);
        $gameStats['homeTeam']['blue_cards'] = $this->hasBlueCard($hometeamscores);

        $gameStats['guestTeam']['team_name'] = $event->gGuestTeam;
        $gameStats['guestTeam']['players'] = $this->isPlayer($guestteamscores);
        $gameStats['guestTeam']['officials'] = $this->isOfficial($guestteamscores);
        $gameStats['guestTeam']['yellow_cards'] = $this->hasYellowCard($guestteamscores);
        $gameStats['guestTeam']['suspensions'] = $this->hasSuspensions($guestteamscores);
        $gameStats['guestTeam']['red_cards'] = $this->hasRedCard($guestteamscores);
        $gameStats['guestTeam']['blue_cards'] = $this->hasBlueCard($guestteamscores);

        return $gameStats;
    }

    /**
     * Returns the current event based on the auto_item.
     */
    public function getCurrentEvent(): CalendarEventsModel|null
    {
        $item = Input::get('auto_item');

        if (empty($item)) {
            return null;
        }

        return CalendarEventsModel::findByIdOrAlias($item);
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function isPlayer($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '' !== $teammember['name'] && (
                        'A' === $teammember['number'] ||
                        'B' === $teammember['number'] ||
                        'C' === $teammember['number'] ||
                        'D' === $teammember['number']
                    )
                ) {
                    return false;
                }

                return true;
            }
        );
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function isOfficial($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '' !== $teammember['name'] && (
                        'A' === $teammember['number'] ||
                    'B' === $teammember['number'] ||
                    'C' === $teammember['number'] ||
                    'D' === $teammember['number']
                    )
                ) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function hasYellowCard($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '0' !== $teammember['yellow_cards'] 
                ) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function hasSuspensions($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '0' !== $teammember['suspensions'] 
                ) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function hasRedCard($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '0' !== $teammember['red_cards'] 
                ) {
                    return true;
                }

                return false;
            }
        );
    }

    /**
     * @param array<mixed> $teammembers
     *
     * @return array<mixed>
     */
    private function hasBlueCard($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '0' !== $teammember['blue_cards'] 
                ) {
                    return true;
                }

                return false;
            }
        );
    }
}
