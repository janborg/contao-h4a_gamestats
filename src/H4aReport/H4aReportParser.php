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

use Janborg\H4aGamestats\Tabula\TabulaConverter;
use Contao\File;
use Contao\FilesModel;
use Contao\System;

class H4aReportParser
{
    private $reportID;

    private $base_url = 'https://spo.handball4all.de/misc/sboPublicReports.php?sGID=';

    public function __construct($reportID)
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
        
        if ($data !== "") {
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
        }
        else {
            throw new \Exception('Report is empty.');
        }
    }

    public function parseReport()
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
        $home_team = $this->parsePlayerStats('home_team');
        $this->heim_players = $this->isPlayer($home_team);
        $this->heim_officials = $this->isOfficial($home_team);
        //gast_team
        $guest_team = $this->parsePlayerStats('guest_team');
        $this->gast_players = $this->isPlayer($guest_team);
        $this->gast_officials = $this->isOfficial($guest_team);

        $this->timeline = $this->parseTimeline();

        return $this;
    }

    /**
     * Konvertiert einen Handball4All Spielbericht in einen JsonString anhand eines FilesModel.
     *
     * @param FilesModel $file Contao filesModel of pdf-Report to be converted
     *
     * @return Json
     */
    private function convertPdfReportFileToJson(FilesModel $file)
    {
        $projectDir = System::getContainer()->getParameter('kernel.project_dir');

        $filePath = $projectDir.'/'.$file->path;

        $tabula = new TabulaConverter();

        return $tabula->setPdf($filePath)
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
    }

    /**
     * @* @param array $gameInfo Table from converted H4a report, home or guest team
     */
    private function parseGameInfo()
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
     * @* @param string $team 'home_team' or 'guest_team'
     */
    private function parsePlayerStats($team)
    {
        if ('home_team' === $team) {
            $teamstats = $this->arrReport[2]['data'];
        }

        if ('guest_team' === $team) {
            $teamstats = $this->arrReport[3]['data'];
        }

        $teamstats = \array_slice($teamstats, 2);

        $playerstats = [];

        foreach ($teamstats as $key => $teammember) {
            if (empty($teammember[1]['text'])) {
                continue;
            }
            $penalties = explode('/', $teammember[6]['text']);
            $playerstats[$key] = [
                'nummer' => $teammember[0]['text'],
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
     * @param array $timeline array from converted H4a report
     */
    private function parseTimeline()
    {
        $timeline = $this->arrReport[4]['data'];

        if (isset($this->arrReport[5]['data'])) {
            $timeline = array_merge($timeline, $this->arrReport[5]['data']);
        }

        $header = array_shift($timeline);

        $parsedTimeline = [];

        foreach ($timeline as $key => $value) {
            $parsedTimeline[$key] = [
                $header[0]['text'] => $value[0]['text'],
                $header[1]['text'] => $value[1]['text'],
                $header[2]['text'] => $value[2]['text'],
                $header[3]['text'] => $value[3]['text'],
            ];
        }

        return $parsedTimeline;
    }

    /**
     * @* @param array $referees array from converted H4a report
     */
    private function parseReferees()
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

    private function isPlayer($teammembers)
    {
        return array_filter(
            $teammembers,
            static function ($teammember) {
                if (
                    '' !== $teammember['name'] && (
                        'A' === $teammember['nummer'] ||
                        'B' === $teammember['nummer'] ||
                        'C' === $teammember['nummer'] ||
                        'D' === $teammember['nummer']
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
                        'A' === $teammember['nummer'] ||
                    'B' === $teammember['nummer'] ||
                    'C' === $teammember['nummer'] ||
                    'D' === $teammember['nummer']
                    )
                ) {
                    return true;
                }

                return false;
            }
        );
    }
}
