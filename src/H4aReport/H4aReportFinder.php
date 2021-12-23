<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace App\H4aReport;

use Symfony\Component\DomCrawler\Crawler;

class H4aReportFinder
{
    private $baseUrl = 'https://spo.handball4all.de/Spielbetrieb/index.php?orgGrpID=1&all=1&score=';

    private $leagueID;

    private $gameNo;

    public function setLeagueID($leagueID): void
    {
        $this->leagueID = $leagueID;
    }

    public function setgameNo($gameNo): void
    {
        $this->gameNo = $gameNo;
    }

    public function getGameTableByLeagueID()
    {
        $url = $this->baseUrl.$this->leagueID;
        dump($this->baseUrl, $this->leagueID, $url);
        $html = file_get_contents($url);

        $crawler = new Crawler($html);

        $crawler = $crawler->filterXPath('//table[@class="gametable"]/tr[position() > 1]');

        return $crawler->filterXPath('//tr')->each(
            static function ($tr, $i) {
                return $tr->filterXPath('//td')->each(
                    static function ($td, $i) {
                        $value['text'] = $td->text();

                        if ($td->filterXPath('//a')->count() > 0) {
                            //$value['href'] = $td->filterXPath('//a')->attr('href');
                            $parts = parse_url($td->filterXPath('//a')->attr('href'));
                            parse_str($parts['query'], $query);
                            $value['sGID'] = $query['sGID'];
                        }

                        return $value;
                    }
                );
            }
        );
    }

    public function getReportNoByGameNo()
    {
        $url = $this->baseUrl.$this->leagueID;
        dump($this->baseUrl, $this->leagueID, $url, $this->gameNo);
        $html = file_get_contents($url);

        $crawler = new Crawler($html);

        $crawler = $crawler->filterXPath('//table[@class="gametable"]/tr[position() > 1]');

        $allGames = $crawler->filterXPath('//tr')->each(
            static function ($tr, $i) {
                return $tr->filterXPath('//td')->each(
                    static function ($td, $i) {
                        $value['text'] = $td->text();

                        if ($td->filterXPath('//a')->count() > 0) {
                            $parts = parse_url($td->filterXPath('//a')->attr('href'));
                            parse_str($parts['query'], $query);
                            $value['sGID'] = $query['sGID'];
                        }

                        return $value;
                    }
                );
            }
        );

        foreach ($allGames as $game) {
            if ($game[1]['text'] === $this->gameNo) {
                $reportNo = $game[10]['sGID'];
            }
        }

        return $reportNo;
    }
}
