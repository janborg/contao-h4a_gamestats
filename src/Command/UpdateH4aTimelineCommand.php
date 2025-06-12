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

use Contao\Date;
use Contao\CalendarEventsModel;
use Contao\CoreBundle\Cache\EntityCacheTags;
use Symfony\Component\Console\Command\Command;
use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\Model\H4aTimelineModel;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Attribute\AsCommand;
use Janborg\H4aGamestats\H4aReport\H4aReportParser;
use Janborg\H4aTabellen\Crawler\H4aReportNoCrawler;
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
        private H4aReportNoCrawler $h4aReportNoCrawler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to update all Timeline Data for h4a-Events, that have no timelines yet.')
            ->addOption('update-all', null, InputOption::VALUE_NONE, 'Force Update for already existing timelines.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $objEvents = CalendarEventsModel::findby(
            ['DATE(FROM_UNIXTIME(startDate)) <= ?', 'h4a_resultComplete = ?'],
            [date('Y-m-d'), true],
            ['eager' => true],
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
                Date::parse('d.m.Y', $objEvent->startDate).': Spiel '.$objEvent->gGameID.' '.$objEvent->title.':',
                '-----------------------------------------------------',
            ]);

            if (isset($objEvent->sGID) && '' === $objEvent->sGID) {
                $output->writeln('Keine ReportNo (sGID) vorhanden. Versuche ReportNo zu finden ...');

                $this->h4aReportNoCrawler->setProvider($objEvent->provider);
                $this->h4aReportNoCrawler->setClassID($objEvent->gClassID);
                $this->h4aReportNoCrawler->setClassShortName($objEvent->gClassName);
                $this->h4aReportNoCrawler->setgGameID($objEvent->gGameID);
                $this->h4aReportNoCrawler->setVerbandName($objEvent->verband);

                try {
                    $this->h4aReportNoCrawler->crawlReportNo();
                } catch (\Exception $e) {
                    $output->writeln('<error>Fehler beim Crawl der ReportNo: '.$e->getMessage().'</error>');
                    continue;
                }

                $sGID = $this->h4aReportNoCrawler->getSGid();

                if (null !== $sGID && '' !== $sGID) {
                    $objEvent->sGID = $sGID;
                    $objEvent->save();
                    $output->writeln('<info>ReportNo (sGID) '.$sGID.' gefunden.</info>');
                } else {
                    $output->writeln('<error>Keine Reportnummer vorhanden... Skipped</error>');
                    continue;
                }
            }

            $output->writeln('Timeline aus Spielbericht '.$objEvent->sGID.' abrufen...');

            // check, ob bereits Timeline zum H4a-Event vorhanden sind:
            if (!$input->getOption('update-all')) {
                $objPlayerscores = H4aTimelineModel::findBy('pid', $objEvent->id);

                if (null !== $objPlayerscores) {
                    $output->writeln('<comment>Timeline Daten bereits vorhanden. Überspringe Spielbericht...</comment>');

                    continue;
                }
            }

            $h4areportparser = new H4aReportParser($objEvent->sGID);

            try {
                $h4areportparser->parseReport();
            } catch (\Exception $e) {
                $output->writeln('<error>Fehler beim Parsing des Spielberichts für Spiel '.$objEvent->gGameNo.' ['.$objEvent->title.']: '.$e->getMessage().'</error>');
                continue;
            }
            // Spieler der Heim Mannschaft speichern
            H4aTimelineModel::saveTimeline($h4areportparser->timeline, $objEvent->id);

            $output->writeln('<info>Timeline für Spiel '.$objEvent->gGameID.' gespeichert.</info>');

            $this->entityCacheTags->invalidateTagsFor($objEvent);
        }

        return Command::SUCCESS;
    }
}
