<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Janborg\H4aGamestats\JanborgH4aGamestatsBundle;
use Janborg\H4aTabellen\JanborgH4aTabellenBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(JanborgH4aGamestatsBundle::class)->setLoadAfter(
                [
                    ContaoCoreBundle::class,
                    JanborgH4aTabellenBundle::class,
                ]
            ),
        ];
    }
}
