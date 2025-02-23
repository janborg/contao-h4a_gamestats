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

use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\Crawler\GameStatsCrawler;
use Janborg\H4aTabellen\HandballNet\Provider;
use Janborg\H4aTabellen\HandballNet\Verband;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class ShowGamestatsCommand.
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
    protected static $defaultDescription = 'Show Lineups and Timeline for a Game from handball.net';

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
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'Provider', '')
            ->addOption('verband', null, InputOption::VALUE_REQUIRED, 'Verband', '')
            ->addOption('classID', null, InputOption::VALUE_REQUIRED, 'Liga ID', '')
            ->addOption('classShort', null, InputOption::VALUE_REQUIRED, 'Ligakürzel mit Bezirk', '')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $io = new SymfonyStyle($input, $output);

        $gGameID = $input->getArgument('gGameID');

        if (!$gGameID) {
            $io->error('Bitte Game ID angeben!');

            return Command::FAILURE;
        }

        $this->gameStatsCrawler->setgGameID($gGameID);

        if ($input->getOption('provider')) {
            $this->gameStatsCrawler->setProvider($input->getOption('provider'));
        } else {
            $question = new ChoiceQuestion(
                'Bitte wählen Sie den Provider des Vereins:',
                array_column(Provider::cases(), 'value'),
                null,
            );
            $question->setErrorMessage('Bitte gültigen Provider angeben');

            $provider = $io->askQuestion($question);
            $this->gameStatsCrawler->setProvider($provider);
        }

        if ($input->getOption('verband')) {
            $this->gameStatsCrawler->setVerbandName($input->getOption('verband'));
        } else {
            $question = new ChoiceQuestion(
                'Bitte wählen Sie den Verband aus, in dem der Verein spielt:',
                array_column(Verband::cases(), 'value'),
                null,
            );

            $question->setErrorMessage('Verband %s ist ungültig.');

            $verband = $io->askQuestion($question);

            $this->gameStatsCrawler->setVerbandName($verband);
        }

        if ($input->getOption('classID')) {
            $this->gameStatsCrawler->setClassShortName($input->getOption('classID'));
        } else {
            $classShortName = $io->ask('Liga ID: ', '126171');
            $this->gameStatsCrawler->setClassID($classShortName);
        }

        if ($input->getOption('classShort')) {
            $this->gameStatsCrawler->setclassShortName($input->getOption('classShort'));
        } else {
            $classShort = $io->ask('Ligakürzel mit Bezirk: ', 'm-bol_hf');
            $this->gameStatsCrawler->setclassShortName($classShort);
        }

        $this->gameStatsCrawler->crawlAllGameStats();

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
        $tabletimeline->setHeaders(['Zeit', 'Spielstand', 'Typ', 'Team', 'Nr.', 'Name', 'Text']);
        $tabletimeline->setRows($this->gameStatsCrawler->getTimeline());
        $tabletimeline->render();

        return Command::SUCCESS;
    }
}
