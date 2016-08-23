<?php

namespace Siso\Bundle\ContentLoaderBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Stopwatch\Stopwatch;

class FixtureWriterCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('siso:fixtures:write')
            ->setDescription('stroes fixtures in yml files')
            ->addArgument(
                'location_id',
                InputOption::VALUE_REQUIRED,
                <<<'EOD'
The location id which sall be exported
EOD
            )
            ->addArgument(
                'depth',
                InputOption::VALUE_REQUIRED,
                <<<'EOD'
The depth to be used
EOD
            )
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

        $fixtureWriter = $this->getContainer()->get('siso.content_loader.fixture_writer');

        $fixtureWriter->setProgressCallback(
        function ($message) use ($output) {
            $output->writeln($message);
        }
        );

        $watch = new Stopwatch();
        $watch->start('All');


        $fixtureWriter->saveToFile(
            $input->getArgument('path'),
            $input->getArgument('location_id'),
            $input->getArgument('depth'));

        $result = $watch->stop('All');
        $output->writeln('Script time: ' . $result->getDuration() / 1000 .' sec');
    }
}