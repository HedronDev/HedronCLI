<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerUpCommand extends DockerCommand {

  protected function configure() {
    $this->setName('docker:up')
      ->setDescription('Executes docker-compose up on your branch specific environment for a project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $docker_dir = $this->getHedronDir('docker', $client, $project, $client .'-'. $project . '-' . $branch);
    $commands[] = "cd $docker_dir";
    $commands[] = "docker-compose up -d";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

}