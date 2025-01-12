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

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GameStatsCrawler
{
    private string $baseUrl = 'https://www.handball.net';

    private string $verbandName;

    private string $verbandShortname;

    private string $classID;

    private string $classShortName;

    private string $gGameID;

    /**
     * @var array<mixed>
     */
    private array $homeLineup;

    /**
     * @var array<mixed>
     */
    private array $guestLineup;

    /**
     * @var array<mixed>
     */
    private array $timeline;

    /**
     * @var array<mixed>
     */
    private array $matchInfo;

    private Crawler $crawler;

    public function setClassID(string $classID): void
    {
        $this->classID = $classID;
    }

    public function setClassShortName(string $classShortName): void
    {
        $this->classShortName = urlencode($classShortName);
        $this->classShortName = strtolower($this->classShortName);
    }

    public function setgGameID(string $gGameID): void
    {
        $this->gGameID = $gGameID;
    }

    public function setVerbandName(string $verbandName): void
    {
        $this->verbandName = urlencode($verbandName);
    }

    public function setVerbandShortname(string $verbandShortname): void
    {
        $this->verbandShortname = urlencode($verbandShortname);
    }

    public function getHomeTeam(): string
    {
        return $this->matchInfo['homeTeam'];
    }

    public function getGuestTeam(): string
    {
        return $this->matchInfo['guestTeam'];
    }

    public function getMatchResult(): string
    {
        return $this->matchInfo['homeTeamResult'].' : '.$this->matchInfo['guestTeamResult'];
    }

    /**
     * Undocumented function.
     *
     * @return array<mixed>
     */
    public function getHomeLineup(): array
    {
        return $this->homeLineup;
    }

    /**
     * Undocumented function.
     *
     * @return array<mixed>
     */
    public function getGuestLineup(): array
    {
        return $this->guestLineup;
    }

    /**
     * Undocumented function.
     *
     * @return array<mixed>
     */
    public function getTimeline(): array
    {
        return $this->timeline;
    }

    /**
     * creates a Crawler and crawls all the game stats for a game from handball.net.
     * Relevant inputs must be set upfront.
     */
    public function getAllGameStats(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlLineups();

        $this->crawlTimeline();
    }

    /**
     * Creates a Crawler and crawls the lineup for both teams of a game from
     * handball.net. Relevant inputs must be set upfront.
     */
    public function getGameLineups(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlLineups();
    }

    /**
     * Creates a Crawler and crawls the timeline of a game from handball.net. Relevant
     * inputs must be set upfront.
     */
    public function getGameTimeline(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlTimeline();
    }

    private function getGameUrl(): string
    {
        return $this->baseUrl.'/ligen/handball4all.'.$this->verbandName.'.'.$this->classShortName.'_'.$this->verbandShortname.'/spielplan/spieltage/handball4all.'.$this->verbandName.'.'.$this->classID.'/spiele/handball4all.'.$this->verbandName.'.'.$this->gGameID;
    }

    private function getCrawler(): void
    {
        $url = $this->getGameUrl();

        $httpClient = HttpClient::create();

        $response = $httpClient->request('GET', $url);

        $html = $response->getContent();

        $this->crawler = new Crawler($html);
    }

    private function crawlMatch(): void
    {
        $matchTable = $this->crawler->filterXPath('//div[@id="tickaroo-liveblog"]//table')->first();

        $matchInfo = [];

        try {
            $matchTable->filter('td')->each(
                static function (Crawler $node, $i) use (&$matchInfo): void {
                    switch ($i) {
                        case 0:
                            $matchInfo['homeTeam'] = $node->text();
                            break;
                        case 1:
                            $matchInfo['homeTeamResult'] = $node->text();
                            break;
                        case 2:
                            $matchInfo['guestTeam'] = $node->text();
                            break;
                        case 3:
                            $matchInfo['guestTeamResult'] = $node->text();
                            break;
                    }
                },
            );
            $this->matchInfo = $matchInfo;
        } catch (\InvalidArgumentException $e) {
            $this->matchInfo = $matchInfo;
        }
    }

    private function crawlLineups(): void
    {
        // find all tables inside div with id 'aufstellung'
        $allTables = $this->crawler->filterXPath('//div[@id="aufstellung"]//table/tbody');

        // loop through all tables and return the node values as array
        $arrTables = $allTables->each(static fn (Crawler $tableCrawler) => $tableCrawler->filter('tr')->each(static fn (Crawler $rowCrawler) => $rowCrawler->filter('td')->each(static fn (Crawler $cellCrawler) => $cellCrawler->text())));

        $this->homeLineup = $arrTables[0];
        $this->guestLineup = $arrTables[1];
    }

    private function crawlTimeline(): void
    {
        // find the ul element inside the div with id 'tickaroo-liveblog'
        $ulTimeline = $this->crawler->filterXPath('//div[@id="tickaroo-liveblog"]//ul');

        $timelineEvents = [];

        try {
            $ulTimeline->filter('li')->each(
                static function (Crawler $liCrawler, $i) use (&$timelineEvents): void {
                    $timelineEvents[$i]['timestamp'] = $liCrawler->filter('div')->eq(0)->filter('span')->text();

                    $timelineEvents[$i]['standing'] = $liCrawler->filter('div > p')->eq(0)->text();

                    $timelineEvents[$i]['eventText'] = $liCrawler->filter('div >p')->eq(1)->text();
                },
            );
        } catch (\InvalidArgumentException $e) {
            $this->timeline = array_reverse($timelineEvents, false);
        }
    }
}
