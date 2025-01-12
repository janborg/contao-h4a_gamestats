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

use Contao\CoreBundle\Cache\EntityCacheTags;
use Contao\CoreBundle\Framework\ContaoFramework;
use Janborg\H4aGamestats\HandballNet\TeamsCrawler;
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
class ShowTeamsCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'h4a:show:teams';

    /**
     * @var string
     */
    protected static $defaultDescription = 'Show teams of a given club from handballnet';

    public function __construct(
        private ContaoFramework $framework,
        private EntityCacheTags $entityCacheTags,
        private TeamsCrawler $teamsCrawler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command allows you to show all Teams for a club from handball.net.')
            ->addArgument('clubID', InputArgument::REQUIRED, 'clubID from handball.net')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();

        $this->teamsCrawler->setVerbandName('wuerttemberg');

        $this->teamsCrawler->setClubID($input->getArgument('clubID'));

        $teams = $this->teamsCrawler->getAllTeams();

        $tablehome = new Table($output);
        $tablehome->setHeaders(['Team', 'ID', 'Bezirk', 'Liga']);
        $tablehome->setRows($teams);
        $tablehome->render();

        return Command::SUCCESS;
    }
}
