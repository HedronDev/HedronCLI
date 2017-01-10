<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class ProjectOpenCommand extends GitWorkingDirectoryCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('project:open')
      ->setDescription('Open current project in preferred editor.');
    parent::configure();
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    list($client, $project, $branch) = $this->getClientProjectBranch($input, $output);
    $environment = $this->getHedronDir('project', $client, $project, 'environment.yml');
    if (!file_exists($environment)) {
      throw new RuntimeException(sprintf("The project does not have any environment settings. Please check %s and ensure it exists, is readable, and contains a proper environment.yml file.", $environment));
    }
    $yaml = Yaml::parse(file_get_contents($environment));
    if (!isset($yaml['projectIDE'])) {
      throw new RuntimeException(sprintf("No IDE was specified for this project. To perform this operation, edit %s, add a \"projectIDE\" key and path to your preferred IDE.", $environment));
    }
    $data_directory = $this->getHedronDir('data', $client, $project, $branch, 'web');
    shell_exec("{$yaml['projectIDE']} $data_directory");
  }


}