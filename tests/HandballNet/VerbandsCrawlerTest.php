<?php

declare(strict_types=1);

namespace Janborg\H4aGamestats\Tests\HandballNet;

use Janborg\H4aGamestats\HandballNet\VerbandsCrawler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;


class VerbandsCrawlerTest extends TestCase
{
    public function testGetAllVerbaende(): void
    {
        $crawler = new VerbandsCrawler();

        $verbaende = $crawler->getAllVerbaende();

        $this->assertCount(27, $verbaende);
        $expected = [
                    ['verbandsUrl' => '/verbaende/Baden', 'verbandName' => 'Badischer Handball-Verband', 'verbandShortName' => 'Baden'],
                    ['verbandsUrl' => '/verbaende/Bayern', 'verbandName' => 'Bayerischer Handball-Verband', 'verbandShortName' => 'Bayern'],
                    ['verbandsUrl' => '/verbaende/Berlin', 'verbandName' => 'Handball-Verband Berlin', 'verbandShortName' => 'Berlin'],
                    ['verbandsUrl' => '/verbaende/Brandenburg', 'verbandName' => 'Handball-Verband Brandenburg', 'verbandShortName' => 'Brandenburg'],
                    ['verbandsUrl' => '/verbaende/Hamburg', 'verbandName' => 'Hamburger Handball-Verband', 'verbandShortName' => 'Hamburg'],
                    ['verbandsUrl' => '/verbaende/Hessen', 'verbandName' => 'Hessischer Handball-Verband', 'verbandShortName' => 'Hessen'],
                    ['verbandsUrl' => '/verbaende/Mecklenburg-Vorpommern', 'verbandName' => 'Handball-Verband Meck.-Vorpommern', 'verbandShortName' => 'Mecklenburg-Vorpommern'],
                    ['verbandsUrl' => '/verbaende/Niedersachsen', 'verbandName' => 'Handballverband Niedersachsen-Bremen', 'verbandShortName' => 'Niedersachsen'],
                    ['verbandsUrl' => '/verbaende/Pfalz', 'verbandName' => 'Pfälzer Handball-Verband', 'verbandShortName' => 'Pfalz'],
                    ['verbandsUrl' => '/verbaende/Rheinhessen', 'verbandName' => 'Handball-Verband Rheinhessen', 'verbandShortName' => 'Rheinhessen'],
                    ['verbandsUrl' => '/verbaende/Rheinland', 'verbandName' => 'Handball-Verband Rheinland', 'verbandShortName' => 'Rheinland'],
                    ['verbandsUrl' => '/verbaende/Saar', 'verbandName' => 'Handball-Verband Saar', 'verbandShortName' => 'Saar'],
                    ['verbandsUrl' => '/verbaende/Sachsen', 'verbandName' => 'Handball-Verband Sachsen', 'verbandShortName' => 'Sachsen'],
                    ['verbandsUrl' => '/verbaende/Sachsen-Anhalt', 'verbandName' => 'Handball-Verband Sachsen-Anhalt', 'verbandShortName' => 'Sachsen-Anhalt'],
                    ['verbandsUrl' => '/verbaende/Schleswig-Holstein', 'verbandName' => 'Handballverband Schleswig-Holstein', 'verbandShortName' => 'Schleswig-Holstein'],
                    ['verbandsUrl' => '/verbaende/Suedbaden', 'verbandName' => 'Südbadischer Handball-Verband', 'verbandShortName' => 'Suedbaden'],
                    ['verbandsUrl' => '/verbaende/Thueringer', 'verbandName' => 'Thüringer Handball-Verband', 'verbandShortName' => 'Thueringer'],
                    ['verbandsUrl' => '/verbaende/Westfalen', 'verbandName' => 'Handball-Verband Westfalen', 'verbandShortName' => 'Westfalen'],
                    ['verbandsUrl' => '/verbaende/Wuerttemberg', 'verbandName' => 'Handballverband Württemberg', 'verbandShortName' => 'Wuerttemberg'],
                    ['verbandsUrl' => '/verbaende/Nordrhein', 'verbandName' => 'Handball Nordrhein', 'verbandShortName' => 'Nordrhein'],
                    ['verbandsUrl' => '/verbaende/BW-OL', 'verbandName' => 'Oberliga Baden-Württemberg', 'verbandShortName' => 'BW-OL'],
                    ['verbandsUrl' => '/verbaende/HHSH-Ligen', 'verbandName' => 'Oberliga Hamburg - Schleswig-Holstein', 'verbandShortName' => 'HHSH-Ligen'],
                    ['verbandsUrl' => '/verbaende/Oberliga-Ostsee-Spree', 'verbandName' => 'Oberliga Ostsee-Spree', 'verbandShortName' => 'Oberliga-Ostsee-Spree'],
                    ['verbandsUrl' => '/verbaende/RPS-Ligen', 'verbandName' => 'Oberliga Rheinland-Pfalz/Saar', 'verbandShortName' => 'RPS-Ligen'],
                    ['verbandsUrl' => '/verbaende/DHB', 'verbandName' => 'Deutscher Handballbund', 'verbandShortName' => 'DHB'],
                    ['verbandsUrl' => '/verbaende/IHF', 'verbandName' => 'International Handball Federation', 'verbandShortName' => 'IHF'],
                    ['verbandsUrl' => '/verbaende/EHF', 'verbandName' => 'European Handball Federation', 'verbandShortName' => 'EHF'],
                ];

        $this->assertSame($expected, $verbaende);
    }
}