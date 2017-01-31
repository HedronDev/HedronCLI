<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class DockerDownCommand extends GitWorkingDirectoryCommand {

  protected function configure() {
    $this->setName('docker:down')
      ->setDescription('Executes docker-compose down on your branch specific environment for a project.');
    parent::configure();
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $commands = [];
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $environment_file = Yaml::parse(file_get_contents($this->getHedronDir('project', $client, $project, 'environment.yml')));
    $docker_dir = $this->getHedronDir('docker', $client, $project, $environment_file['client'] .'-'. $environment_file['name'] . '-' . $branch);
    $commands[] = "cd $docker_dir";
    $commands[] = "docker-compose down";
    $output->writeln("<info>" . shell_exec(implode("; ", $commands)) . "</info>");
  }

}