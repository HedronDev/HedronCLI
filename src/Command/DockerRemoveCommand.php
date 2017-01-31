<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DockerRemoveCommand extends GitWorkingDirectoryCommand {

  protected function configure() {
    $this->setName('docker:remove')
      ->setDescription('Executes docker-compose down/remove on your branch specific environment for a project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $environment_file = Yaml::parse(file_get_contents($this->getHedronDir('project', $client, $project, 'environment.yml')));
    $docker_dir = $this->getHedronDir('docker', $client, $project, $environment_file['client'] .'-'. $environment_file['name'] . '-' . $branch);
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