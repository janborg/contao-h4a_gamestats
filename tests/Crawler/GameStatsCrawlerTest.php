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
    public static function h4aGameProvider(): iterable
    {
        return [
            ['118111', 'm-ll-rnt_bhv', 'baden', '7636856', 'handball4all'],
            ['126896', 'f-ol-200_hhv', 'hamburg', '7839341', 'handball4all'],
            ['115991', 'm-ol_pfhv', 'pfalz', '7421121', 'handball4all'],
            ['122376', 'm-ol_hvr', 'rheinhessen', '7473806', 'handball4all'],
            ['123521', 'f-ol_hvsl', 'saar', '7412746', 'handball4all'],
            ['124361', 'm-ol_hvsh', 'schleswig-holstein', '7626741', 'handball4all'],
            ['124981', 'm-ol_sbhv', 'suedbaden', '7861736', 'handball4all'],
            ['120936', 'hvw-fol1_hvwf', 'westfalen', '7389871', 'handball4all'],
            ['126171', 'm-bol_hf', 'wuerttemberg', '7763026', 'handball4all'],
            ['123391', 'f-vl-1_hvw', 'wuerttemberg',  '7791901', 'handball4all'],       
            ['379357.14-runde', '157646', 'bhv', '7767122', 'nuliga'],
            ['366210.1-runde', '152393', 'hvbr', '7671726', 'nuliga'], //brandenburg
            ['372105.3-runde', '155063', 'hvberlin', '7784509', 'nuliga'], //berlin
            ['371042.6-runde', '154669', 'hhv', '7623845', 'nuliga'], //hessen
            ['371868.4-runde', '154802', 'hvmv', '7652794', 'nuliga'], //mecklenburg-vorpommern
            ['372705.3-runde', '155319', 'hvn', '7668661', 'nuliga'], //niedersachsen
            ['374269.6-runde', '155966', 'hvr', '7782979', 'nuliga'], //rheinland
            ['367548.8-runde', '153074', 'hvs', '7654809', 'nuliga'], //sachsen
            ['376187.7-runde', '156890', 'thv', '7658971', 'nuliga'], //thueringen
            ['363743.6-runde', '151636', 'hnr', '7650477', 'nuliga'], //nordrhein
            //['', '', 'sachsen-anhalt', ''],
            ['18208', '16059', 'dhbdata', '83588', 'sportradar'],
            //['', '', 'ehf', '', 'sportradar'],
            //['wm', 'wm_2025.hauptrunde-gruppe-i.2', 'ihf', '2375931', 'sid'], // wettbewerbe statt ligen
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function nuligaGameProvider(): iterable
    {
        return [
            ['379357.14-runde', '157646', 'bhv', '7767122'],
            ['', '', 'berlin', '', 'nuliga'],
            ['', '', 'brandenburg', '', 'nuliga'],
            ['', '', 'hessen', '', 'nuliga'],
            ['', '', 'mecklenburg-vorpommern', '', 'nuliga'],
            ['', '', 'niedersachsen', '', 'nuliga'],
            ['', '', 'rheinland', '', 'nuliga'],
            ['', '', 'sachsen', '', 'nuliga'],
            ['', '', 'sachsen-anhalt', '', 'nuliga'],
            ['', '', 'thueringer', '', 'nuliga'],
            ['', '', 'nordrhein', '', 'nuliga'],
        ];
    }

    /**
     * @dataProvider h4aGameProvider
     *
     * @param string $classID
     * @param string $className
     * @param string $verbandName
     * @param string $gameID
     * @param string $provider
     */
     public function testcrawlAllGameStats($classID, $className, $verbandName, $gameID, $provider): void
    {
        $this->crawler = new GameStatsCrawler();

        // Suppress PHPStan undefined property errors @phpstan-ignore-next-line */
        $this->crawler->setclassID($classID);
        /** @phpstan-ignore-next-line */
        $this->crawler->setclassShortName($className);
        $this->crawler->setVerbandName($verbandName);
        $this->crawler->setProvider($provider);
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
