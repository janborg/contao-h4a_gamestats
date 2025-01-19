<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats\Tests\HandballNet;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\MockHttpClient;
use Janborg\H4aGamestats\HandballNet\TeamsCrawler;
use Symfony\Component\HttpClient\Response\MockResponse;


class TeamsCrawlerTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function clubProvider(): iterable
    {
        return [
            ['6201', 'wuerttemberg', 'HSG Heilbronn'],
            ['581', 'baden', 'TV Hemsbach'],
        ];
    }
    /**
     * @dataProvider clubProvider
     *
     * @param string $clubID
     * @param string $verbandName
     * @param string $clubName
     */
    public function testgetAllTeams($clubID, $verbandName, $clubName): void
    {
        $crawler = new TeamsCrawler();

        $crawler->setClubID($clubID);

        $crawler->setVerbandName($verbandName);

        $teams = $crawler->getAllTeams();

        $this->assertIsArray($teams);
        
        $this->assertArrayHasKey('teamID', $teams[0]);

        $this->assertArrayHasKey('teamName', $teams[0]);

        $this->assertArrayHasKey('teamUrl', $teams[0]);

        $this->assertContainsEquals($clubName, $teams[0]);
    }      
}