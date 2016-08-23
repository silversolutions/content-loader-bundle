<?php

namespace Siso\Bundle\ContentLoaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Stopwatch\Stopwatch;

class FixtureLoaderCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('siso:fixtures:load')
            ->setDescription('Load fixtures for integration tests')
            ->addOption('removedatabase','r',
                InputOption::VALUE_NONE,
                'if database should be replaces by fresh one, default no')
            ->addArgument(
                'path',
                InputOption::VALUE_REQUIRED,
                <<<'EOD'
Path to the fixture file. It is allowed to use @Bundle shortcut syntax,
e.g. @SisoTestToolsBundle/Resources/fixtures/default/all.yml
EOD
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo: Check if environment is test
        // Warn about data removal


        $fixtureLoader = $this->getContainer()->get('siso.content_loader.fixture_loader');

        $fixtureLoader->setProgressCallback(
            function ($message) use ($output) {
                $output->writeln($message);
            }
        );

        $watch = new Stopwatch();
        $watch->start('All');

        $removedatabase = false;
        if ($input->getOption('removedatabase')) {
            $removedatabase = true;
        }

        $fixtureLoader->loadFromFile($input->getArgument('path'), $removedatabase);

        $result = $watch->stop('All');
        $output->writeln('Script time: ' . $result->getDuration() / 1000 .' sec');
    }
}