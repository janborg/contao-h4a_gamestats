<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Command;

use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateH4aTimelineCommand.
 *
 * @property int $statusCode
 */
#[AsCommand(
    name: 'h4a:update:timeline',
    description: 'Update Games Timelines from h4a.',
)]
class UpdateH4aTimelineCommand extends Command
{
    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private GameStatsCrawler $gameStatsCrawler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to update all Timeline Data for h4a-Events, that have no timelines yet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true],
        );

        if (null === $objEvents) {
            $output->writeln('<info>Es wurden keine Events mit ReportNo (sGID) gefunden.</info>');

            return Command::SUCCESS;
        }

        $output->writeln([
            'Es wurden '.\count($objEvents).' H4a-Events mit Ergebnis gefunden.',
            'Versuche nun die Reports abzurufen ...',
            '============================================================',
        ]);

        foreach ($objEvents as $objEvent) {
            $output->writeln([
                '',
                'Spiel '.$objEvent->gGameID.' '.$objEvent->title.':',
                '-----------------------------------------------------',
            ]);

            // check, ob bereits Timeline zum H4a-Event vorhanden sind:
            $objPlayerscores = H4aTimelineModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                $output->writeln('<comment>Timeline Daten bereits vorhanden. Überspringe Spielbericht...</comment>');

                continue;
            }

            $this->gameStatsCrawler->setgGameID($objEvent->gGameID);
            $this->gameStatsCrawler->setProvider($objEvent->provider);
            $this->gameStatsCrawler->setVerbandName($objEvent->verband);
            $this->gameStatsCrawler->setClassShortName($objEvent->gClassName);
            $this->gameStatsCrawler->setClassID($objEvent->gClassID);

            $this->gameStatsCrawler->crawlAllGameStats();

            $timeline = $this->gameStatsCrawler->getTimeline();

            if (empty($timeline)) {
                $output->writeln('<error>Keine Timeline-Daten gefunden.</error>');

                continue;
            }

            H4aTimelineModel::saveTimeline($timeline, $objEvent->id);

            $output->writeln('<info>Timeline für Spiel '.$objEvent->gGameID.' gespeichert.</info>');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }

        return Command::SUCCESS;
    }
}
