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
use Symfony\Component\HttpClient\HttpClient;

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
     * @phpstan-ignore missingType.return
     */
    public function getGameTableByLeagueID()
    {
        $url = $this->baseUrl.$this->leagueID;

        $httpClient = HttpClient::create();

        $response = $httpClient->request('GET', $url);

        $html = $response->getContent();

        $crawler = new Crawler($html);

        $crawler = $crawler->filterXPath('//table[@class="gametable"]/tr[position() > 1]');

        return $crawler->filterXPath('//tr')->each(
            static fn ($tr, $i) => $tr->filterXPath('//td')->each(
                static function ($td, $i) {
                    $value['text'] = $td->text();

                    if ($td->filterXPath('//a')->count() > 0) {
                        // $value['href'] = $td->filterXPath('//a')->attr('href');
                        $parts = parse_url($td->filterXPath('//a')->attr('href'));
                        parse_str($parts['query'], $query);
                        $value['sGID'] = $query['sGID'];
                    }

                    return $value;
                },
            ),
        );
    }

    /**
     * @phpstan-ignore missingType.return
     */
    public function getReportNoByGameNo()
    {
        $url = $this->baseUrl.$this->leagueID;

        $httpClient = HttpClient::create();

        $response = $httpClient->request('GET', $url);

        $html = $response->getContent();

        $crawler = new Crawler($html);

        $crawler = $crawler->filterXPath('//table[@class="gametable"]/tr[position() > 1]');

        $allGames = $crawler->filterXPath('//tr')->each(
            static fn ($tr, $i) => $tr->filterXPath('//td')->each(
                static function ($td, $i) {
                    $value['text'] = $td->text();

                    if ($td->filterXPath('//a')->count() > 0) {
                        $parts = parse_url($td->filterXPath('//a')->attr('href'));
                        parse_str($parts['query'], $query);
                        $value['sGID'] = $query['sGID'];
                    }

                    return $value;
                },
            ),
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
