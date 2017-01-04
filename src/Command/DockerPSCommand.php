<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerPSCommand extends DockerCommand {
  protected function configure() {
    $this->setName('docker:ps')
      ->setDescription('Run docker-compose ps for a given project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $docker_dir = $this->getHedronDir('docker', $client, $project, $client .'-'. $project . '-' . $branch);
    $commands[] = "cd $docker_dir";
    $commands[] = "docker-compose ps";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

}
