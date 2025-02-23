<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Backend;

use Contao\Backend;
use Contao\BackendUser;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\Input;
use Contao\Message;
use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
use Janborg\H4aGamestats\Model\H4aTimelineModel;

class LookupTimelineController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private GameStatsCrawler $gameStatsCrawler,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupTimeline(): void
    {
        $id = [Input::get('id')];

        $objCalendarEvent = CalendarEventsModel::findById($id);

        $this->gameStatsCrawler->setgGameID($objCalendarEvent->gGameID);
        $this->gameStatsCrawler->setProvider($objCalendarEvent->provider);
        $this->gameStatsCrawler->setVerbandName($objCalendarEvent->verband);
        $this->gameStatsCrawler->setClassShortName($objCalendarEvent->gClassName);
        $this->gameStatsCrawler->setClassID($objCalendarEvent->gClassID);

        $this->gameStatsCrawler->crawlAllGameStats();

        $timeline = $this->gameStatsCrawler->getTimeline();

        H4aTimelineModel::saveTimeline($timeline, $objCalendarEvent->id);

        Message::addConfirmation('Timeline für Spiel '.$objCalendarEvent->gGameNo.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->getReferer());
    }
}
