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

use Symfony\Component\DomCrawler\Crawler;

class H4aReportFinder
{
    private string $baseUrl = 'https://spo.handball4all.de/Spielbetrieb/index.php?orgGrpID=1&all=1&score=';

    private string $leagueID;

    private string $gameNo;

    public function setLeagueID(string $leagueID): void
    {
        $this->leagueID = $leagueID;
    }

    public function setgameNo(string $gameNo): void
    {
        $this->gameNo = $gameNo;
    }

    /**
     * @return array<string>
     */
    public function getGameTableByLeagueID(): array
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

    /**
     * @return array<string>
     */
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

        return $this->getReportNoByGameNoFromAllGames($allGames, $this->gameNo);
    }

    /**
     * @param array<mixed> $allGames
     * @param string       $gameNo
     *
     * @return array<string>
     */
    public function getReportNoByGameNoFromAllGames(array $allGames, $gameNo): array
    {
        $sgame = array_filter($allGames, static fn ($game) => $game[1]['text'] === $gameNo);

        return $sgame[10]['sGID'] ?? null;
    }
}
