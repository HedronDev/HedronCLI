<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerExecCommand extends GitWorkingDirectoryCommand {

  protected function configure() {
    $this->setName('docker:exec')
      ->setDescription('Run docker-compose exec for a given project.')
      ->addArgument('instance', InputArgument::REQUIRED)
      ->addArgument('number', InputArgument::OPTIONAL, 'The number of the instance.', 1);
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $instance = "{$client}{$project}{$branch}_{$input->getArgument('instance')}_{$input->getArgument('number')}";
    $commands[] = "docker exec -it $instance /bin/bash";
    $output->writeln("<info>" . passthru(implode("; ", $commands)) . "</info>");
  }

}