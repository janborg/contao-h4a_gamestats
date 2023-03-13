<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\H4aReport;

use Contao\System;
use Janborg\H4aGamestats\Tabula\TabulaConverter;

/**
 * Class H4aReportParser.
 *
 * @property array        $home_team
 * @property array        $guest_team
 * @property string       $heim_name
 * @property string       $gast_name
 * @property array        $timeline
 * @property string       $reportUrl
 * @property json         $jsonReport
 * @property array<mixed> $arrReport
 * @property string       $gameNo
 * @property array<mixed> $zuschauer
 * @property array<mixed> $schiedsrichter
 */
class H4aReportParser
{
    private string $reportID;

    private string $base_url = 'https://spo.handball4all.de/misc/sboPublicReports.php?sGID=';

    public function __construct(string $reportID)
    {
        $this->reportID = $reportID;

        $this->reportUrl = $this->base_url.$this->reportID;
    }

    /**
     * Konvertiert einen Handball4All Spielbericht.
     */
    public function convertPdfReport(): void
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');
        $outfilename = 'report_'.$this->reportID.'.pdf';
        $outputPath = $projectDir.'/'.$outfilename;

        $ch = curl_init($this->reportUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $data = curl_exec($ch);

        curl_close($ch);

        if ('' !== $data) {
            file_put_contents($outputPath, $data);

            $tabula = new TabulaConverter();

            $this->jsonReport = $tabula->setPdf($outputPath)
                ->setOptions(
                    [
                        'format' => 'json',
                        'pages' => 'all',
                        'lattice' => true,
                        'stream' => true,
                    ]
                )
                ->convert()
            ;

            $this->arrReport = json_decode($this->jsonReport, true);

            unlink($outputPath);
        } else {
            throw new \Exception('Report is empty.');
        }
    }

    public function parseReport(): self
    {
        $this->convertPdfReport();

        //gameInfo
        $gameInfo = $this->parseGameInfo();
        $this->gameNo = $gameInfo['SpielNr'];
        $this->heim_name = $gameInfo['Heim'];
        $this->gast_name = $gameInfo['Gast'];
        $this->zuschauer = $gameInfo['Zuschauer'];
        //schiedsrichter
        $this->schiedsrichter = $this->parseReferees();

        //heim_team
        $this->home_team = $this->parseHomePlayerStats();

        //gast_team
        $this->guest_team = $this->parseGuestPlayerStats();

        $this->timeline = $this->parseTimeline();

        return $this;
    }

    /**
     * @return array<mixed>
     */
    private function parseGameInfo(): array
    {
        $gameInfo = $this->arrReport[0]['data'];

        $SpielDatum = explode(' , ', $gameInfo[1][1]['text']);
        $HeimGast = explode(' - ', $gameInfo[3][1]['text']);

        return [
            'SpielNr' => $SpielDatum[0],
            'Heim' => $HeimGast[0],
            'Gast' => $HeimGast[1],
            'Zuschauer' => $gameInfo[4][2]['text'],
        ];
    }

    /**
     * @return array<mixed>
     */
    private function parseHomePlayerStats(): array
    {
        $teamstats = $this->arrReport[2]['data'];

        $players_team = $this->heim_name;

        return $this->parsePlayerStats($teamstats, $players_team);
    }

    /**
     * @return array<mixed>
     */
    private function parseGuestPlayerStats(): array
    {
        $teamstats = $this->arrReport[3]['data'];

        $players_team = $this->gast_name;

        return $this->parsePlayerStats($teamstats, $players_team);
    }

    /**
     * @param string       $players_team
     * @param array<mixed> $teamstats
     *
     * @return array<mixed>
     */
    private function parsePlayerStats($teamstats, $players_team): array
    {
        $teamstats = \array_slice($teamstats, 2);

        $playerstats = [];

        foreach ($teamstats as $key => $teammember) {
            //leere Spalten oder fehlerhafte Spalten überspringen (Bsp. Report No. 158918, https://spo.handball4all.de/misc/sboPublicReports.php?sGID=158918)
            if (
                empty($teammember[1]['text'])
                || !empty($teammember[2]['text'])
            ) {
                continue;
            }
            $penalties = explode('/', $teammember[6]['text']);
            $playerstats[$key] = [
                'team' => $players_team,
                'number' => $teammember[0]['text'],
                'name' => $teammember[1]['text'],
                'goals' => empty($teammember[5]['text']) ? 0 : $teammember[5]['text'],
                'penalty_goals' => empty($penalties[1]) ? 0 : $penalties[1],
                'penalty_tries' => empty($penalties[0]) ? 0 : $penalties[0],
                'yellow_card' => empty($teammember[7]['text']) ? 0 : 1,
                '1st_suspension' => $teammember[8]['text'],
                '2nd_suspension' => $teammember[9]['text'],
                '3rd_suspension' => $teammember[10]['text'],
                'red_card' => empty($teammember[11]['text']) ? 0 : 1,
                'blue_card' => empty($teammember[12]['text']) ? 0 : 1,
            ];
        }

        return $playerstats;
    }

    /**
     * @return array<mixed>
     */
    private function parseTimeline(): array
    {
        $timeline = $this->arrReport[4]['data'];

        if (isset($this->arrReport[5]['data'])) {
            $timeline = array_merge($timeline, $this->arrReport[5]['data']);
        }

        $timeline = \array_slice($timeline, 1);

        $parsedTimeline = [];

        foreach ($timeline as $key => $value) {
            $matchTime = $value[1]['text'];

            if ('' !== $value[3]['text']) {
                $action = $value[3]['text'];
            } else {
                continue;
            }

            $parsedTimeline[$key]['matchtime'] = $this->parseMatchTime($matchTime);

            $parsedTimeline[$key]['currentscore'] = $value[2]['text'];

            $parsedTimeline[$key]['action_type'] = $this->parseActionType($action);

            $arrplayer = $this->parseActionPlayer($action);

            $parsedTimeline[$key]['action_team'] = $arrplayer['team'];

            $parsedTimeline[$key]['action_player_number'] = $arrplayer['number'];

            if (isset($arrplayer['number']) && '' !== $arrplayer['number']) {
                $parsedTimeline[$key]['action_player'] = $this->parseActionPlayerName($arrplayer);
            } else {
                $parsedTimeline[$key]['action_player'] = '';
            }
        }

        return $parsedTimeline;
    }

    /**
     * @* @param array $referees array from converted H4a report
     *
     * @return array<mixed>
     */
    private function parseReferees(): array
    {
        $referees = $this->arrReport[1]['data'];

        $header = array_shift($referees);

        return [
            $header[1]['text'] = [
                $referees[0][0]['text'] => $referees[0][1]['text'],
                $referees[1][0]['text'] => $referees[1][1]['text'],
            ],
            $header[2]['text'] = [
                $referees[0][0]['text'] => $referees[0][2]['text'],
                $referees[1][0]['text'] => $referees[1][2]['text'],
            ],
        ];
    }

    private function parseActionType(string $action): string
    {
        $action_exploded = explode(' ', $action);

        //type of action
        switch ($action_exploded[0]) {
            case 'Tor':
                $parsedactiontype = 'Tor';
                break;
            case '7m-Tor':
                $parsedactiontype = '7m-Tor';
                break;
            case '7m,':
                $parsedactiontype = '7m-Versuch';
                break;
            case 'Verwarnung':
                $parsedactiontype = 'Verwarnung';
                break;
            case '2-min':
                $parsedactiontype = '2-min';
                break;
            case 'Auszeit':
                $parsedactiontype = 'Auszeit';
                break;
            case 'Disqualifikation':
                $parsedactiontype = 'Disqualifikation';
                break;

            default:
                $parsedactiontype = '';
        }

        return $parsedactiontype;
    }

    /**
     * @return array<mixed>
     */
    private function parseActionPlayer(string $action): array
    {
        $action_exploded = explode(' ', $action);

        if ('Auszeit' === $action_exploded[0]) {
            $parsedPlayer['team'] = str_replace('Auszeit ', '', $action);
            $parsedPlayer['number'] = '';
            $parsedPlayer['name'] = '';
        } else {
            // Spielernummer und Team (zwischen den Klammern) => (?:\((.*?)\))?
            preg_match('/
            (?:\s*(.*?))?        # was vor den klammern ist
            (?:\((.*?)\))?       # was in den klammern ist
            (?:$)                 # ende des strings erwartet
            /isx', $action, $matches);

            if (isset($matches[2]) && null !== $matches[2]) {
                $arrNumberAndTeam = explode(', ', $matches[2]);

                $parsedPlayer['number'] = $arrNumberAndTeam[0];

                $parsedPlayer['team'] = $arrNumberAndTeam[1];
            } else {
                $parsedPlayer['number'] = '';

                $parsedPlayer['team'] = '';
            }
        }

        return $parsedPlayer;
    }

    /**
     * @param string $matchTime
     */
    private function parseMatchTime($matchTime): string //int
    {
        //$matchTime = explode(':', $matchTime);

        //return $matchTime[0] * 60 + $matchTime[1];
        return $matchTime;
    }

    /**
     * @param array<mixed> $arrplayer
     */
    private function parseActionPlayerName(array $arrplayer): string
    {
        $allplayers = array_merge($this->home_team, $this->guest_team);
        $player_name = array_filter(
            $allplayers,
            static function ($player) use ($arrplayer) {
                if (
                    // mehrstufiges filter_array
                    $arrplayer['number'] === $player['number'] &&
                    $arrplayer['team'] === $player['team']
                ) {
                    return true;
                }

                return false;
            }
        );
        // Array neu ordnen
        $player_name = array_values($player_name);

        if (!empty($player_name)) {
            return $player_name[0]['name'];
        }

        return 'unbekannt';
    }
}
