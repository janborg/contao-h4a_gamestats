<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Tests\HandballNet;

use Janborg\H4aGamestats\HandballNet\GameStatsCrawler;
use PHPUnit\Framework\TestCase;

class GameStatsCrawlerTest extends TestCase
{

    private GameStatsCrawler $crawler;

    /**
     * @return array<int, array<int, string>>
     */
    public static function gameProvider(): iterable
    {
        return [
            ['126171', 'm-bol', 'wuerttemberg', 'hf', '7763026'],
            ['123391', 'f-vl-1', 'wuerttemberg', 'hvw', '7791901'],
            ['118111', 'm-ll-rnt', 'baden', 'bhv', '7636856'],   
        ];
    }

    /**
     * @dataProvider gameProvider
     *
     * @param string $classID
     * @param string $className
     * @param string $verbandName
     * @param string $verbandShortName
     * @param string $gameID
     */
     public function testcrawlAllGameStats($classID, $className, $verbandName, $verbandShortName, $gameID): void
    {
        $this->crawler = new GameStatsCrawler();

        // Suppress PHPStan undefined property errors @phpstan-ignore-next-line */
        $this->crawler->setclassID($classID);
        /** @phpstan-ignore-next-line */
        $this->crawler->setclassShortName($className);
        $this->crawler->setVerbandName($verbandName);
        $this->crawler->setVerbandShortname($verbandShortName);
        $this->crawler->setgGameID($gameID);

        $this->crawler->crawlAllGameStats();

        $this->assertIsString($this->crawler->getHomeTeam());
        $this->assertIsString($this->crawler->getGuestTeam());
        $this->assertIsArray($this->crawler->getHomeLineup());
        $this->assertIsArray($this->crawler->getGuestLineup());
        $this->assertIsArray($this->crawler->getTimeline());
        $this->assertIsString($this->crawler->getMatchResult());
        
    }
}
