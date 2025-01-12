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
use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\HandballNet\GameStatsCrawler;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateLineupCommand.
 *
 * @property SymfonyStyle $io
 * @property int          $statusCode
 */
class ShowGamestatsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'h4a:show:gamestats';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Update Lineup and scores from handballnet';

    public function __construct(
        private ContaoFramework $framework,
        private GameStatsCrawler $gameStatsCrawler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to update all Stats for game from handball.net.')
            ->addArgument('gGameID', InputArgument::REQUIRED, 'gGameID from handball.net')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $gGameID = $input->getArgument('gGameID');

        $objEvent = CalendarEventsModel::findby('gGameID', $gGameID);

        if (null === $objEvent) {
            $output->writeln('<info>Es wurde kein Event mit GameID '.$gGameID.' gefunden.</info>');

            return Command::SUCCESS;
        }

        // Suppress PHPStan undefined property errors @phpstan-ignore-next-line */
        $this->gameStatsCrawler->setclassID($objEvent->gClassID);
        /** @phpstan-ignore-next-line */
        $this->gameStatsCrawler->setclassShortName($objEvent->gClassName);

        $this->gameStatsCrawler->setVerbandName('wuerttemberg');
        $this->gameStatsCrawler->setVerbandShortname('hvw');
        $this->gameStatsCrawler->setgGameID($gGameID);

        $this->gameStatsCrawler->getAllGameStats();

        $output->writeln([
            'Heim: '.$this->gameStatsCrawler->getHomeTeam(),
            'Gast: '.$this->gameStatsCrawler->getGuestTeam(),
            'Ergebnis: '.$this->gameStatsCrawler->getMatchResult(),
            '',
            '============================================================',
            '',
            'Heim Aufstellung: ',
        ]);

        $tablehome = new Table($output);
        $tablehome->setHeaders(['Nr', 'Name', 'Tore', '2min', 'Karte']);
        $tablehome->setRows($this->gameStatsCrawler->getHomeLineup());
        $tablehome->render();

        $output->writeln([
            '',
            '============================================================',
            '',
            'Gast Aufstellung: ',
        ]);

        $tableguest = new Table($output);
        $tableguest->setHeaders(['Nr', 'Name', 'Tore', '2min', 'Karte']);
        $tableguest->setRows($this->gameStatsCrawler->getGuestLineup());
        $tableguest->render();

        $output->writeln([
            '',
            '============================================================',
            '',
            'Spielverlauf: ',
        ]);

        $tabletimeline = new Table($output);
        $tabletimeline->setHeaders(['Zeit', 'Spielstand', 'Text']);
        $tabletimeline->setRows($this->gameStatsCrawler->getTimeline());
        $tabletimeline->render();

        return Command::SUCCESS;
    }
}
