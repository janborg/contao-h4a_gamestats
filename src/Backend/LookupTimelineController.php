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
use Contao\CoreBundle\Monolog\SystemLogger;
use Contao\Input;
use Contao\Message;
use Janborg\H4aGamestats\HandballNet\HandballnetGamestatsParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class LookupTimelineController extends Backend
{
    public function __construct(
        private EntityCacheTags $entityCacheTags,
        private readonly SystemLogger $systemLogger,
        private UrlGeneratorInterface $urlGenerator,
        private readonly HandballnetGamestatsParser $gamestatsParser,
    ) {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    public function lookupTimeline(): void
    {
        $id = Input::get('id');

        $objCalendarEvent = CalendarEventsModel::findById($id);

        // check if handballnet_game_id is set and not empty
        if (isset($objCalendarEvent->handballnet_game_id) && '' !== $objCalendarEvent->handballnet_game_id) {
            $gameId = $objCalendarEvent->handballnet_game_id;
        } else {
            Message::addError('handball.net Spiel-ID nicht vorhanden.');

            $this->redirect($this->urlGenerator->generate('contao_backend', ['do' => 'calendar', 'table' => 'tl_h4a_timeline', 'id' => $id]));
        }

        try {
            $this->gamestatsParser->parseGame($gameId);
        } catch (\Exception $e) {
            $this->systemLogger->error('Fehler beim Abrufen des Spielberichts für Spiel '.$objCalendarEvent->handballnet_game_id.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            Message::addError('Fehler beim Abrufen des Spielberichts für Spiel '.$objCalendarEvent->handballnet_game_id.' ['.$objCalendarEvent->title.']: '.$e->getMessage());

            $this->redirect($this->urlGenerator->generate('contao_backend', ['do' => 'calendar', 'table' => 'tl_h4a_timeline', 'id' => $id]));
        }

        H4aTimelineModel::saveTimeline($this->gamestatsParser->timeline, $objCalendarEvent->id);

        Message::addConfirmation('Timeline für Spiel '.$objCalendarEvent->handballnet_game_id.' ['.$objCalendarEvent->title.'] gespeichert.');

        $this->entityCacheTags->invalidateTagsFor($objCalendarEvent);

        $this->redirect($this->urlGenerator->generate('contao_backend', ['do' => 'calendar', 'table' => 'tl_h4a_timeline', 'id' => $id]));
    }
}
