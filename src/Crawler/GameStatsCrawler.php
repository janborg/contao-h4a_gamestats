<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Crawler;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

class GameStatsCrawler
{
    private string $baseUrl = 'https://www.handball.net';

    private string $verbandName;

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
    public function crawlAllGameStats(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlLineups();

        $this->crawlTimeline();

        $this->parseTimeline();
    }

    /**
     * Creates a Crawler and crawls the lineup for both teams of a game from
     * handball.net. Relevant inputs must be set upfront.
     */
    public function crawlGameLineups(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlLineups();
    }

    /**
     * Creates a Crawler and crawls the timeline of a game from handball.net. Relevant
     * inputs must be set upfront.
     */
    public function crawlGameTimeline(): void
    {
        $this->getCrawler();

        $this->crawlMatch();

        $this->crawlTimeline();

        $this->parseTimeline();
    }

    private function getGameUrl(): string
    {
        return $this->baseUrl.'/ligen/handball4all.'.$this->verbandName.'.'.$this->classShortName.'/spielplan/spieltage/handball4all.'.$this->verbandName.'.'.$this->classID.'/spiele/handball4all.'.$this->verbandName.'.'.$this->gGameID;
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
            $matchTable->filterXpath('//td')->each(
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
        $arrTables = $allTables->each(static fn (Crawler $tableCrawler) => $tableCrawler->filterXPath('//tr')->each(
            static fn (Crawler $rowCrawler) => $rowCrawler->filterXPath('//td')->each(
                static fn (Crawler $cellCrawler) => $cellCrawler->text())));

        $this->homeLineup = $arrTables[0];
        $this->guestLineup = $arrTables[1];
    }

    private function crawlTimeline(): void
    {
        // find the ul element inside the div with id 'tickaroo-liveblog'
        $ulTimeline = $this->crawler->filterXPath('//div[@id="tickaroo-liveblog"]//ul');

        $timelineEvents = [];

        try {
            $ulTimeline->filterXPath('//li')->each(
                static function (Crawler $liCrawler, $i) use (&$timelineEvents): void {
                    
                    $timelineEvents[$i]['timestamp'] = $liCrawler->filterXPath('li/div//span')->text();
                    
                    $timelineEvents[$i]['standing'] = $liCrawler->filterXPath('li/div/p[1]')->text();
                    
                    $timelineEvents[$i]['eventText'] = $liCrawler->filterXPath('li/div/p[2]')->text();
                },
            );
        } catch (\InvalidArgumentException $e) {
            $this->timeline = array_reverse($timelineEvents, false);
        }
    }

    private function parseTimeline(): void
    {
        $timeline = $this->timeline;

        $timelineEvents = [];

        foreach ($timeline as $event) {
            $team = $this->parseTeam($event['eventText']);
            $playerNo = $this->parsePlayerNo($event['eventText']);
            $timelineEvents[] = [
                'timestamp' => $event['timestamp'],
                'standing' => $event['standing'],
                'eventType' => $this->parseEventType($event['eventText']),
                'team' => $team,
                'playerNo' => $playerNo,
                'playerName' => $this->parsePlayerName($playerNo, $team),
                'eventText' => $event['eventText'],
            ];
        }

        $this->timeline = $timelineEvents;
    }

    private function parseEventType(string $eventText): string
    {
        $eventText = strtolower($eventText);

        if (str_contains($eventText, '7-meter tor durch')) {
            return '7m-Tor';
        }

        if (str_contains($eventText, '7-meter verworfen')) {
            return '7m-Versuch';
        }

        if (str_contains($eventText, 'tor durch')) {
            return 'Tor';
        }

        if (str_contains($eventText, 'erhält eine 2-minuten strafe')) {
            return '2-min';
        }

        if (str_contains($eventText, 'wurde verwarnt')) {
            return 'Gelb';
        }

        if (str_contains($eventText, 'wurde disqualifiziert')) {
            return 'Rot';
        }

        if (str_contains($eventText, 'auszeit')) {
            return 'Auszeit';
        }

        if (str_contains($eventText, 'spielstand 1. halbzeit')) {
            return 'Halbzeit';
        }

        if (str_contains($eventText, 'spielstand 2. halbzeit')) {
            return 'Endstand';
        }

        return 'unknown';
    }

    private function parsePlayerNo(string $eventText): string
    {
        $eventText = strtolower($eventText);

        $player = '';

        if (preg_match('/\((\d+\.)\)/', $eventText, $matches)) {
            $player = $matches[1];
        }

        return $player;
    }

    private function parsePlayerName(string $playerNo, string $team): string
    {
        $player = '';

        if ('home' === $team) {
            $player = array_filter($this->homeLineup, static fn ($lineup) => $lineup[0] === $playerNo);
        }

        if ('guest' === $team) {
            $player = array_filter($this->guestLineup, static fn ($lineup) => $lineup[0] === $playerNo);
        }

        if (!is_array($player)) {
            return '';
        } else {
            $player = array_values($player);
        }
        
        return isset($player[0][1]) ? $player[0][1] : '';
    }

    private function parseTeam(string $eventText): string
    {
        $team = '';

        if (str_contains($eventText, $this->matchInfo['homeTeam'])) {
            $team = 'home';
        }

        if (str_contains($eventText, $this->matchInfo['guestTeam'])) {
            $team = 'guest';
        }

        return $team;
    }
}
