<?php

namespace Starship\Console;

use Starship\Builder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ServeCommand extends BuildCommand
{
    protected function configure()
    {
        $this
            ->setName('serve')
            ->setDescription('Serves the web site on a local web server')
            ->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port to listen on.', 4000);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $root = getcwd() . DIRECTORY_SEPARATOR . '_site';
        if (!file_exists($root)) {
            $output->writeln("<comment>_site</comment> not found. Run <info>starship build</info> to generate content.");
            return;
        }

        $port = $input->getOption('port');

        $output->writeln("Listening on <info>http://localhost:$port/</info>");
        $output->writeln("Document root is <info>$root</info>");
        $output->writeln("Press Ctrl-C to quit.");
        $output->writeln("");

        `php -S localhost:$port -t $root`;
    }
}
