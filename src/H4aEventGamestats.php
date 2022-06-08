<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Contao\Template;

class H4aEventGamestats
{
    private $db;
    private $bundles;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Adds stats for Home Team Players and Officials to template
     */
    public function addHomeStatsToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $hometeamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '1');

        $template->homePlayers= $this->isPlayer($hometeamscores);
        
        $template->homeOfficials= $this->isOfficial($hometeamscores);

    }

    /**
     * Adds stats for Guest Team Players and Officials to template
     */
    public function addGuestStatsToTemplate(Template $template, CalendarEventsModel $event): void
    {
        $guestteamscores = H4aPlayerscoresModel::findTeamScoresByCalendarEvent($event->id, '2');

        $template->guestPlayers= $this->isPlayer($guestteamscores);

        $template->guestOfficials= $this->isOfficial($guestteamscores);
    }

     /**
     * Returns the current event based on the auto_item.
     */
    public function getCurrentEvent(): ?CalendarEventsModel
    {
        $item = Input::get('auto_item');

        if (empty($item)) {
            return null;
        }

        return CalendarEventsModel::findByIdOrAlias($item);
    }

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
