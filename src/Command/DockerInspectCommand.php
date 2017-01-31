<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerInspectCommand extends GitWorkingDirectoryCommand {

  protected function configure() {
    $this->setName('docker:inspect')
      ->setDescription('Run docker inspect for a given instance.')
      ->addArgument('instance', InputArgument::REQUIRED)
      ->addArgument('number', InputArgument::OPTIONAL, 'The number of the instance.', 1);
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $instance = "{$client}{$project}{$branch}_{$input->getArgument('instance')}_{$input->getArgument('number')}";
    $commands[] = "docker inspect $instance";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

}