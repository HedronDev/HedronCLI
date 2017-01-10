<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DockerRemoveCommand extends GitWorkingDirectoryCommand {

  protected function configure() {
    $this->setName('docker:remove')
      ->setDescription('Executes docker-compose down/remove on your branch specific environment for a project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $docker_dir = $this->getHedronDir('docker', $client, $project, $client .'-'. $project . '-' . $branch);
    $this->runDown($client, $project, $branch, $output);
    $commands[] = "cd $docker_dir";
    $commands[] = "docker-compose rm -v";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

  protected function runDown($client, $project, $branch, OutputInterface $output) {
    $arguments = [
      'client' => $client,
      'project' => $project,
      'branch' => $branch
    ];
    $arguments = new ArrayInput($arguments);
    $down = $this->getApplication()->find('docker:down');
    $down->run($arguments, $output);
  }

}