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

class TeamsCrawler
{
    private string $baseUrl = 'https://www.handball.net';

    private string $clubID;

    private $season;

    private string $verbandName;

    private array $teams;

    private string $verbandShortname;

    private Crawler $crawler;

    public function setClubID(string $clubID): void
    {
        $this->clubID = $clubID;
    }

    public function setVerbandName(string $verbandName): void
    {
        $this->verbandName = urlencode($verbandName);
    }

    public function setVerbandShortname(string $verbandShortname): void
    {
        $this->verbandShortname = urlencode($verbandShortname);
    }

    /**
     * creates a Crawler and crawls all the teams for a club. Relevant inputs must be
     * set upfront.
     */
    public function getAllTeams(): array
    {
        $this->getCrawler();

        $this->crawlTeams();

        return $this->teams;
    }

    private function getClubUrl(): string
    {
        $cluburl = $this->baseUrl.'/vereine/handball4all.'.$this->verbandName.'.'.$this->clubID;

        if (isset($this->season)) {
            $cluburl .= '?'.$this->season;
        }

        return $cluburl;
    }

    private function getCrawler(): void
    {
        $url = $this->getClubUrl();

        $httpClient = HttpClient::create();

        $response = $httpClient->request('GET', $url);

        $html = $response->getContent();

        $this->crawler = new Crawler($html);
    }

    private function crawlTeams(): void
    {
        $divMain = $this->crawler->filterXPath('//body//main');

        $arrTeams = [];

        $divMain->filter('a.list-item')->each(
            static function (Crawler $node, $i) use (&$arrTeams): void {
                $arrTeams[$i]['teamUrl'] = $node->attr('href');

                $arrTeams[$i]['teamName'] = $node->filter('div.list-item-title')->text();

                $arrTeams[$i]['districtAndClass'] = $node->filter('div.list-item-text')->text();
            }
        );

        foreach ($arrTeams as &$team) {
            $team['teamID'] = $this->extractTeamID($team['teamUrl']);
            $team['districtName'] = explode(' - ', $team['districtAndClass'])[0];
            $team['className'] = explode(' - ', $team['districtAndClass'])[1];
            unset($team['districtAndClass'], $team['teamUrl']);
        }

        $this->teams = $arrTeams;
    }

    private function extractTeamID(string $url): string
    {
        preg_match('/mannschaften\/\w+\.\w+\.(\d+)\//', $url, $matches);

        return $matches[1] ?? '';
    }
}
