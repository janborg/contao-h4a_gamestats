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
        
        $template->homePlayers = $this->isPlayer($hometeamscores);

        $template->homeOfficials = $this->isOfficial($hometeamscores);
    }

    /**
     * Adds stats for Guest Team Players and Officials to template.
     */
    public function addGuestStatsToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $guestteamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '2');

        $template->guest_team = $event->gGuestTeam;

        $template->guestPlayers = $this->isPlayer($guestteamscores);

        $template->guestOfficials = $this->isOfficial($guestteamscores);
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
}
