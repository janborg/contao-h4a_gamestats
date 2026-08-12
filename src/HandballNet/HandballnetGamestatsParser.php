<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\HandballNet;

use Janborg\H4aTabellen\HandballnetApiClient;

/**
 * Builds playerscores and timeline arrays from the handball.net game API.
 *
 * Replaces the former PDF-based H4aReportParser. It fetches the combined game
 * endpoint (summary + lineup + events) once and maps it into the same array
 * shapes that H4aPlayerscoresModel::savePlayerscores() and
 * H4aTimelineModel::saveTimeline() expect.
 *
 * @property array<int, array<string, mixed>> $home_team
 * @property array<int, array<string, mixed>> $guest_team
 * @property array<int, array<string, mixed>> $timeline
 * @property string                           $heim_name
 * @property string                           $gast_name
 * @property string                           $heim_id
 * @property string                           $gast_id
 */
class HandballnetGamestatsParser
{
    /**
     * Maps handball.net event types to the German action strings used throughout the
     * templates and timeline queries.
     */
    private const ACTION_TYPE_MAP = [
        'Goal' => 'Tor',
        'SevenMeterGoal' => '7m-Tor',
        'SevenMeterMissed' => '7m-Versuch',
        'TwoMinutePenalty' => '2-min',
        'Timeout' => 'Auszeit',
        'YellowCard' => 'Verwarnung',
        'Warning' => 'Verwarnung',
        'Disqualification' => 'Disqualifikation',
    ];

    public string $heim_name = '';

    public string $gast_name = '';

    public string $heim_id = '';

    public string $gast_id = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $home_team = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $guest_team = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $timeline = [];

    public function __construct(private readonly HandballnetApiClient $handballnetApiClient)
    {
    }

    /**
     * Fetches and parses the combined game data for the given handball.net game id.
     */
    public function parseGame(string $gameId, bool $cache = true): self
    {
        $json = $this->handballnetApiClient->getGameCombinedData($gameId, $cache);

        if (null === $json || '' === $json) {
            throw new \RuntimeException(\sprintf('No data returned from handball.net for game "%s".', $gameId));
        }

        $data = json_decode($json, true);

        if (!\is_array($data) || !isset($data['data']) || !\is_array($data['data'])) {
            throw new \RuntimeException(\sprintf('Invalid data returned from handball.net for game "%s".', $gameId));
        }

        $summary = $data['data']['summary'] ?? [];
        $lineup = $data['data']['lineup'] ?? [];
        $events = $data['data']['events'] ?? [];

        $this->heim_name = (string) ($summary['homeTeam']['name'] ?? '');
        $this->gast_name = (string) ($summary['awayTeam']['name'] ?? '');

        $this->heim_id = (string) ($summary['homeTeam']['id'] ?? '');
        $this->gast_id = (string) ($summary['awayTeam']['id'] ?? '');

        $this->home_team = array_merge(
            $this->mapPlayers($lineup['home'] ?? [], $this->heim_name),
            $this->mapOfficials($lineup['homeOfficials'] ?? [], $this->heim_name),
        );

        $this->guest_team = array_merge(
            $this->mapPlayers($lineup['away'] ?? [], $this->gast_name),
            $this->mapOfficials($lineup['awayOfficials'] ?? [], $this->gast_name),
        );

        $this->timeline = $this->mapTimeline($events, $lineup);

        return $this;
    }

    /**
     * @param array<int, array<string, mixed>> $players
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapPlayers(array $players, string $teamName): array
    {
        $mapped = [];

        foreach ($players as $player) {
            $mapped[] = [
                'team' => $teamName,
                'player_id' => (string) ($player['id'] ?? ''),
                'number' => (string) ($player['number'] ?? ''),
                'name' => trim(($player['firstname'] ?? '').' '.($player['lastname'] ?? '')),
                'goals' => (int) ($player['goals'] ?? 0),
                'penalty_goals' => (int) ($player['penaltyGoals'] ?? 0),
                'penalty_tries' => (int) ($player['penaltyGoals'] ?? 0) + (int) ($player['penaltyMissed'] ?? 0),
                'yellow_card' => ($player['yellowCards'] ?? 0) > 0 ? 1 : 0,
                'suspensions' => (int) ($player['penalties'] ?? 0),
                'red_card' => ($player['redCards'] ?? 0) > 0 ? 1 : 0,
                'blue_card' => ($player['blueCards'] ?? 0) > 0 ? 1 : 0,
            ];
        }

        return $mapped;
    }

    /**
     * @param array<int, array<string, mixed>> $officials
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapOfficials(array $officials, string $teamName): array
    {
        $mapped = [];

        foreach ($officials as $official) {
            $mapped[] = [
                'team' => $teamName,
                'player_id' => (string) ($official['id'] ?? ''),
                // position "OA".."OD" -> "A".."D"
                'number' => substr((string) ($official['position'] ?? ''), -1),
                'name' => trim(($official['firstname'] ?? '').' '.($official['lastname'] ?? '')),
                'goals' => 0,
                'penalty_goals' => 0,
                'penalty_tries' => 0,
                'yellow_card' => ($official['warnings'] ?? 0) > 0 ? 1 : 0,
                'suspensions' => (int) ($official['timePenalties'] ?? 0),
                'red_card' => ($official['disqualifications'] ?? 0) > 0 ? 1 : 0,
                'blue_card' => ($official['disqualificationWithBlueCards'] ?? 0) > 0 ? 1 : 0,
            ];
        }

        return $mapped;
    }

    /**
     * @param array<int, array<string, mixed>> $events
     * @param array<string, mixed>             $lineup
     *
     * @return array<int, array<string, mixed>>
     */
    private function mapTimeline(array $events, array $lineup): array
    {
        // handball.net delivers events newest first; store them chronologically.
        usort($events, static fn ($a, $b): int => ($a['id'] ?? 0) <=> ($b['id'] ?? 0));

        $timeline = [];

        foreach ($events as $event) {
            $actionType = self::ACTION_TYPE_MAP[$event['type'] ?? ''] ?? null;

            if (null === $actionType) {
                // StopPeriod and unknown types carry no player action.
                continue;
            }

            $side = $event['team'] ?? null;
            $number = $this->parsePlayerNumber((string) ($event['message'] ?? ''));

            $timeline[] = [
                'matchtime' => (string) ($event['time'] ?? ''),
                'currentscore' => (string) ($event['score'] ?? ''),
                'action_team' => $this->resolveTeamName($side),
                'action_player_number' => $number,
                'action_player' => $this->resolvePlayerName($side, $number, $lineup, (string) ($event['message'] ?? '')),
                'action_type' => $actionType,
            ];
        }

        return $timeline;
    }

    private function resolveTeamName(string|null $side): string
    {
        return match ($side) {
            'Home' => $this->heim_name,
            'Away' => $this->gast_name,
            default => '',
        };
    }

    /**
     * Extracts the player number from an event message, e.g. "Tor durch Carolin
     * Lösch (17.) (SG …)" or "Tor durch 66. (SG …)".
     */
    private function parsePlayerNumber(string $message): string
    {
        if (preg_match('/(\d+)\./', $message, $matches)) {
            return $matches[1];
        }

        return '';
    }

    /**
     * Resolves the player name primarily via lineup lookup by side and number,
     * falling back to the name parsed from the message.
     *
     * @param array<string, mixed> $lineup
     */
    private function resolvePlayerName(string|null $side, string $number, array $lineup, string $message): string
    {
        $players = match ($side) {
            'Home' => $lineup['home'] ?? [],
            'Away' => $lineup['away'] ?? [],
            default => [],
        };

        if ('' !== $number) {
            foreach ($players as $player) {
                if ((string) ($player['number'] ?? '') === $number) {
                    return trim(($player['firstname'] ?? '').' '.($player['lastname'] ?? ''));
                }
            }
        }

        // Fallback: "… durch <Name> (12.) …" or "<Name> (12.) … erhält …"
        if (
            preg_match('/durch (.+?) \(\d+\.\)/u', $message, $matches)
            || preg_match('/^(.+?) \(\d+\.\)/u', $message, $matches)
        ) {
            return trim($matches[1]);
        }

        return 'unbekannt';
    }
}
