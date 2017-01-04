<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerDownCommand extends DockerCommand {

  protected function configure() {
    $this->setName('docker:down')
      ->setDescription('Executes docker-compose down on your branch specific environment for a project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $docker_dir = $this->getHedronDir('docker', $client, $project, $client .'-'. $project . '-' . $branch);
    $commands[] = "cd $docker_dir";
    $commands[] = "docker-compose down";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

}