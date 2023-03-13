<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\EventListener\DataContainer;

use Contao\CalendarModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

/**
 * @Callback(table="tl_calendar_events", target="config.onload")
 */
class UnsetH4aGamestatsOperationCallback
{
    public function __construct()
    {
    }

    public function __invoke(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id) {
            return;
        }

        $calendar = CalendarModel::findById($dc->id);

        if (null === $calendar || '' !== $calendar->h4a_imported) {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_playerscores'], $GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_timeline']);
    }
}
