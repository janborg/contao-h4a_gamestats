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
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Janborg\H4aTabellen\Helper\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class UpdateH4aTimelineCommand.
 *
 * @property int $statusCode
 */
class UpdateH4aTimelineCommand extends Command
{
    protected static $defaultName = 'h4a:update:timeline';

    protected static $defaultDescription = 'Update Games Timelines from h4a';

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
        $this->setHelp('This command allows you to update all Timeline Data for h4a-Events, that have no timelines yet.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true]
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

            if (isset($objEvent->sGID) && '' === $objEvent->sGID) {
                $output->writeln('Keine ReportNo (sGID) vorhanden. Versuche ReportNo zu finden ...');
                $sGID = Helper::getReportNo($objEvent->gClassID, $objEvent->gGameNo);

                if (null !== $sGID) {
                    $objEvent->sGID = $sGID;
                    $objEvent->save();
                    $output->writeln('<info>ReportNo (sGID) '.$sGID.' gefunden.</info>');
                } else {
                    $output->writeln('<error>Keine Reportnummer vorhanden... Skipped</error>');
                    continue;
                }
            }

            $output->writeln('Timeline aus Spielbericht '.$objEvent->sGID.' abrufen...');

            //check, ob bereits Timeline zum H4a-Event vorhanden sind:
            $objPlayerscores = H4aTimelineModel::findBy('pid', $objEvent->id);

            if (null !== $objPlayerscores) {
                $output->writeln('<comment>Timeline Daten bereits vorhanden. Überspringe Spielbericht...</comment>');

                continue;
            }
            $h4areportparser = new H4aReportParser($objEvent->sGID);
            $h4areportparser->parseReport();

            //Spieler der Heim Mannschaft speichern
            H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objEvent->id);

            $output->writeln('<info>Timeline für Spiel '.$objEvent->gGameID.' gespeichert.</info>');
        }

        return Command::SUCCESS;
    }
}
