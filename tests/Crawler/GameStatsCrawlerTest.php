<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Tests\Crawler;

use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
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
            ['126171', 'm-bol_hf', 'wuerttemberg', '7763026'],
            ['123391', 'f-vl-1_hvw', 'wuerttemberg',  '7791901'],
            ['118111', 'm-ll-rnt_bhv', 'baden', '7636856'],   
        ];
    }

    /**
     * @dataProvider gameProvider
     *
     * @param string $classID
     * @param string $className
     * @param string $verbandName
     * @param string $gameID
     */
     public function testcrawlAllGameStats($classID, $className, $verbandName, $gameID): void
    {
        $this->crawler = new GameStatsCrawler();

        // Suppress PHPStan undefined property errors @phpstan-ignore-next-line */
        $this->crawler->setclassID($classID);
        /** @phpstan-ignore-next-line */
        $this->crawler->setclassShortName($className);
        $this->crawler->setVerbandName($verbandName);
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
