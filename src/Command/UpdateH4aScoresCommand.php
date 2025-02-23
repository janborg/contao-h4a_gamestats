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
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateH4aScoresCommand.
 *
 * @property SymfonyStyle $io
 * @property int          $statusCode
 */
#[AsCommand(
    name: 'h4a:update:scores',
    description: 'Update Scores from h4a.',
)]
class UpdateH4aScoresCommand extends Command
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
        $this->setHelp('This command allows you to update all Scores for h4a-Events, that have no scores yet.');
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

            // check, ob bereits Scores zum H4a-Event vorhanden sind:
            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                $output->writeln('<comment>Playerscores bereits vorhanden. Überspringe Spiel...</comment>');

                continue;
            }

            $this->gameStatsCrawler->setgGameID($objEvent->gGameID);
            $this->gameStatsCrawler->setProvider($objEvent->provider);
            $this->gameStatsCrawler->setVerbandName($objEvent->verband);
            $this->gameStatsCrawler->setClassShortName($objEvent->gClassName);
            $this->gameStatsCrawler->setClassID($objEvent->gClassID);

            try {
                $this->gameStatsCrawler->crawlGameLineups();
            } catch (\Exception $e) {
                $output->writeln('<error>Fehler beim Abrufen der Playerscores für Spiel '.$objEvent->gGameNo.' ['.$objEvent->title.']: '.$e->getMessage().'</error>');

                continue;
            }

            // Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::saveHandballnetPlayerscores(
                $this->gameStatsCrawler->getHomeLineup(),
                $objEvent->id,
                $this->gameStatsCrawler->getHomeTeam(),
                1);

            $output->writeln('<info>Playerscores für '.$this->gameStatsCrawler->getHomeTeam().' in Spiel '.$objEvent->gGameID.' gespeichert.</info>');

            // Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::saveHandballnetPlayerscores(
                $this->gameStatsCrawler->getGuestLineup(),
                $objEvent->id,
                $this->gameStatsCrawler->getGuestTeam(),
                2);

            $output->writeln('<info>Playerscores für '.$this->gameStatsCrawler->getGuestTeam().' in Spiel '.$objEvent->gGameID.' gespeichert.</info>');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }

        return Command::SUCCESS;
    }
}
