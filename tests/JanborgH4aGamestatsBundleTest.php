<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Tests;

use Janborg\H4aGamestats\JanborgH4aGamestatsBundle;
use PHPUnit\Framework\TestCase;

class JanborgH4aGamestatsBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new JanborgH4aGamestatsBundle();

        $this->assertInstanceOf('Janborg\H4aGamestats\JanborgH4aGamestatsBundle', $bundle);
    }
}
