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
use Contao\Date;
use Janborg\H4aGamestats\HandballNet\HandballnetGamestatsParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
        private readonly HandballnetGamestatsParser $gamestatsParser,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to update all Scores for h4a-Events, that have no scores yet.')
            ->addOption('update-all', null, InputOption::VALUE_NONE, 'Force Update for already existing timelines.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'hn_resultComplete = ?'],
            [date('Y-m-d'), true],
            ['eager' => true],
        );

        if (null === $objEvents) {
            $output->writeln('<info>Es wurden keine Events mit handball.net Spiel-ID gefunden.</info>');

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
                Date::parse('d.m.Y', $objEvent->startDate).': Spiel '.$objEvent->handballnet_game_id.' '.$objEvent->title.':',
                '-----------------------------------------------------',
            ]);

            if (!isset($objEvent->handballnet_game_id) || '' === $objEvent->handballnet_game_id) {
                $output->writeln('Keine handball.net Spiel-ID vorhanden.');
                continue;
            }

            $output->writeln('Playerscores für Spiel '.$objEvent->handballnet_game_id.' abrufen...');

            // check, ob bereits Scores zum H4a-Event vorhanden sind:
            if (!$input->getOption('update-all')) {
                $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

                if (null !== $objPlayerscores) {
                    $output->writeln('<comment>Playerscores bereits vorhanden. Überspringe Spielbericht...</comment>');
                    continue;
                }
            }

            try {
                $this->gamestatsParser->parseGame($objEvent->handballnet_game_id);
            } catch (\Exception $e) {
                $output->writeln('<error>Fehler beim Abrufen des Spielberichts für Spiel '.$objEvent->handballnet_game_id.' ['.$objEvent->title.']: '.$e->getMessage().'</error>');

                continue;
            }

            // Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->home_team, $objEvent->id, $this->gamestatsParser->heim_name, 1, $this->gamestatsParser->heim_id);

            $output->writeln('<info>Playerscores für '.$this->gamestatsParser->heim_name.' in Spiel '.$objEvent->handballnet_game_id.' gespeichert.</info>');

            // Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($this->gamestatsParser->guest_team, $objEvent->id, $this->gamestatsParser->gast_name, 2, $this->gamestatsParser->gast_id);

            $output->writeln('<info>Playerscores für '.$this->gamestatsParser->gast_name.' in Spiel '.$objEvent->handballnet_game_id.' gespeichert.</info>');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }

        return Command::SUCCESS;
    }
}
