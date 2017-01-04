<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class DeleteProjectCommand extends HedronCommand {
  protected function configure() {
    $this->setName('project:delete')
      ->setDescription('Delete a project')
      ->addArgument('server', InputArgument::OPTIONAL, 'The server on which this project exists.', 'local');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $file = $this->getHedronDir('hedron.yml');
    $helper = $this->getHelper('question');
    $question = new Question('Client: ', '');
    $question->setValidator([$this, 'validateClient']);
    $yaml = Yaml::parse(file_get_contents($file));
    $question->setAutocompleterValues($yaml['client']);
    $client = $helper->ask($input, $output, $question);
    // @todo validate project name
    $question = new Question('Project Name: ', '');
    $projectName = $helper->ask($input, $output, $question);

    $project_dir = strtolower($client) . DIRECTORY_SEPARATOR . strtolower($projectName);

    $commands = [];
    $commands[] = "rm -Rf {$this->getHedronDir('working_dir', $project_dir)}";
    $commands[] = "rm -Rf {$this->getHedronDir('repositories', $project_dir)}";
    $docker_dir = $this->getHedronDir('docker', $project_dir);
    foreach (array_diff(scandir($docker_dir), ['.', '..']) as $dir) {
      $dir = $docker_dir . DIRECTORY_SEPARATOR . $dir;
      $commands[] = "cd $dir";
      $commands[] = "docker-compose down";
      $commands[] = "docker-compose rm -v";
    }
    $commands[] = "rm -Rf {$this->getHedronDir('docker', $project_dir)}";
    $commands[] = "rm -Rf {$this->getHedronDir('data', $project_dir)}";
    // @todo data/docker/repo/working_dir should all be documented in the
    // project environment.yml file, so load that instead and delete the
    // project files out appropriately from there.
    $commands[] = "rm -Rf {$this->getHedronDir('project', $project_dir)}";
    shell_exec(implode('; ', $commands));
  }

  public function validateClient($answer) {
    $file = $this->getHedronDir('hedron.yml');
    $yaml = Yaml::parse(file_get_contents($file));
    if (!in_array($answer, $yaml['client'])) {
      throw new RuntimeException("The selected client does not exists, run the client:create command to add the client first.");
    }
    return $answer;
  }

}