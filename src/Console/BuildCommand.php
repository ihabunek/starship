<?php

namespace Starship\Console;

use Starship\Builder;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('build')
            ->setDescription('Renders the web site');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $source = getcwd();
        $config = $source . DIRECTORY_SEPARATOR . '_config.yml';
        $target = $source . DIRECTORY_SEPARATOR . '_site';

        if (file_exists($config)) {
            $output->writeln("Configuration: <info>$config</info>");
        } else {
            $output->writeln("Configuration: <comment>none</comment>");
        }

        $output->writeln("       Source: <info>$source</info>");
        $output->writeln("       Target: <info>$target</info>");
        $output->writeln("");

        $builder = new Builder($source, $target, $output);
        $builder->build();
    }
}
