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
            ->setDescription('dumps fixtures in yml files')
            ->addArgument(
                'location_id',
                InputOption::VALUE_REQUIRED,
                <<<'EOD'
The location id which shall be exported
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
            )->addArgument(
                'writer_service_id',
                InputOption::VALUE_OPTIONAL,
                <<<'EOD'
Service id of write to be used: default is siso.content_loader.fixture_writer option siso.content_loader.textmodule_writer
EOD
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo: Check if environment is test
        // Warn about data removal

        $serviceId = $input->getArgument('writer_service_id');

        if (!$serviceId) {
            $serviceId = 'siso.content_loader.fixture_writer';
        } else {
            $serviceId = $serviceId[0];
        }

        $fixtureWriter = $this->getContainer()->get($serviceId);

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