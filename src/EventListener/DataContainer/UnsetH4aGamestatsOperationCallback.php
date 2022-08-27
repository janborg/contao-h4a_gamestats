<?php

namespace Janborg\H4aGamestats\EventListener\DataContainer;

use Contao\CalendarModel;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @Callback(table="tl_calendar_events", target="config.onload")
 */
class UnsetH4aGamestatsOperationCallback
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(DataContainer $dc = null): void
    {
        if (null === $dc || !$dc->id) {
            return;
        }

        $calendar = CalendarModel::findById($dc->id);

        if (null === $calendar || "" !== $calendar->h4a_imported) {
            return;
        }

        unset($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_playerscores']);
        unset($GLOBALS['TL_DCA']['tl_calendar_events']['list']['operations']['h4a_timeline']);
    }
}
