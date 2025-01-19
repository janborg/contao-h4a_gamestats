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

use Janborg\H4aGamestats\HandballNet\VerbandsCrawler;
use PHPUnit\Framework\TestCase;

class VerbandsCrawlerTest extends TestCase
{
    /**
     * @return array<int, array<int, string>>
     */
    public static function verbandProvider(): iterable
    {
        return [
            ['/verbaende/Baden', 'Badischer Handball-Verband', 'Baden'],
            ['/verbaende/Bayern', 'Bayerischer Handball-Verband', 'Bayern'],
            ['/verbaende/Berlin', 'Handball-Verband Berlin', 'Berlin'],
            ['/verbaende/Brandenburg', 'Handball-Verband Brandenburg', 'Brandenburg'],
            ['/verbaende/Hamburg', 'Hamburger Handball-Verband', 'Hamburg'],
            ['/verbaende/Hessen', 'Hessischer Handball-Verband', 'Hessen'],
            ['/verbaende/Mecklenburg-Vorpommern', 'Handball-Verband Meck.-Vorpommern', 'Mecklenburg-Vorpommern'],
            ['/verbaende/Niedersachsen', 'Handballverband Niedersachsen-Bremen', 'Niedersachsen'],
            ['/verbaende/Pfalz', 'Pfälzer Handball-Verband', 'Pfalz'],
            ['/verbaende/Rheinhessen', 'Handball-Verband Rheinhessen', 'Rheinhessen'],
            ['/verbaende/Rheinland', 'Handball-Verband Rheinland', 'Rheinland'],
            ['/verbaende/Saar', 'Handball-Verband Saar', 'Saar'],
            ['/verbaende/Sachsen', 'Handball-Verband Sachsen', 'Sachsen'],
            ['/verbaende/Sachsen-Anhalt', 'Handball-Verband Sachsen-Anhalt', 'Sachsen-Anhalt'],
            ['/verbaende/Schleswig-Holstein', 'Handballverband Schleswig-Holstein', 'Schleswig-Holstein'],
            ['/verbaende/Suedbaden', 'Südbadischer Handball-Verband', 'Suedbaden'],
            ['/verbaende/Thueringer', 'Thüringer Handball-Verband', 'Thueringer'],
            ['/verbaende/Westfalen', 'Handball-Verband Westfalen', 'Westfalen'],
            ['/verbaende/Wuerttemberg', 'Handballverband Württemberg', 'Wuerttemberg'],
            ['/verbaende/Nordrhein', 'Handball Nordrhein', 'Nordrhein'],
            ['/verbaende/BW-OL', 'Oberliga Baden-Württemberg', 'BW-OL'],
            ['/verbaende/HHSH-Ligen', 'Oberliga Hamburg - Schleswig-Holstein', 'HHSH-Ligen'],
            ['/verbaende/Oberliga-Ostsee-Spree', 'Oberliga Ostsee-Spree', 'Oberliga-Ostsee-Spree'],
            ['/verbaende/RPS-Ligen', 'Oberliga Rheinland-Pfalz/Saar', 'RPS-Ligen'],
            ['/verbaende/DHB', 'Deutscher Handballbund', 'DHB'],
            ['/verbaende/IHF', 'International Handball Federation', 'IHF'],
            ['/verbaende/EHF', 'European Handball Federation', 'EHF'],
        ];
    }

    /**
     * @dataProvider verbandProvider
     *
     * @param string $verbandsUrl
     * @param string $verbandName
     * @param string $verbandShortName
     */
    public function testGetAllVerbaende($verbandsUrl, $verbandName, $verbandShortName): void
    {
        $crawler = new VerbandsCrawler();

        $verbaende = $crawler->getAllVerbaende();

        $this->assertCount(27, $verbaende);

        $this->assertIsArray($verbaende);

        $this->assertArrayHasKey('verbandName', $verbaende[0]);

        $this->assertArrayHasKey('verbandShortName', $verbaende[0]);

        $this->assertArrayHasKey('verbandsUrl', $verbaende[0]);

        $this->assertContains(
            ['verbandsUrl' => $verbandsUrl,
                'verbandName' => $verbandName,
                'verbandShortName' => $verbandShortName,
            ], $verbaende);
    }
}
