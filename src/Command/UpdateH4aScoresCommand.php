<?php

declare(strict_types=1);

/*
 * This file is part of hsg-heilbronn website.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\Command;

use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aPlayerscoresModel;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateH4aScoresCommand extends Command
{
    private $io;

    private $statusCode = 0;

    /**
     * @var ContaoFramework
     */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        parent::__construct();
    }

    protected function configure(): void
    {
        $commandHelp = 'Update Playerscores der H4a-Events';

        $this->setName('h4a:updatescores')
            ->setDescription($commandHelp)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $this->framework->initialize();

        $this->io = new SymfonyStyle($input, $output);

        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?', 'sGID != ?'],
            [date('Y-m-d'), true, '']
        );

        if (null === $objEvents) {
            $this->io->text('Es wurden keine Events mit ReportNo (sGID) gefunden.');

            return $this->statusCode;
        }

        $this->io->text('Es wurden '.\count($objEvents).' H4a-Events mit ReportNo (sGID) gefunden. Versuche ReportNo abzurufen ...');

        foreach ($objEvents as $objEvent) {
            $this->io->text('Playerscores aus Spielbericht '.$objEvent->sGID.' abrufen...');

            //check, ob bereits Scores zum H4a-Event vorhanden sind: 
            $objPlayerscores = H4aPlayerscoresModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                $this->io->text('Playerscores für Spiel '.$objEvent->gGameNo.' bereits vorhanden. Überspringe Spielbericht...');

                continue;
            }
            $h4areportparser = new H4aReportParser($objEvent->sGID);
            $h4areportparser->parseReport();

            //Spieler der Heim Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->heim_players, $objEvent->id, $h4areportparser->heim_name);

            $this->io->text('Playerscores für '.$h4areportparser->heim_name.' in Spiel '.$objEvent->gGameNo.' gespeichert.');

            //Spieler der Gast Mannschaft speichern
            H4aPlayerscoresModel::savePlayerscores($h4areportparser->gast_players, $objEvent->id, $h4areportparser->gast_name);

            $this->io->text('Playerscores für '.$h4areportparser->gast_name.' in Spiel '.$objEvent->gGameNo.' gespeichert.');
        }

        return $this->statusCode;
    }
}