<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class ListProjectCommand extends HedronCommand {

  protected function configure() {
    $this->setName('project:list')
      ->setDescription('List all projects.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $projects = [];
    $projects_dir = $this->getHedronDir('project');
    foreach (scandir($projects_dir) as $client_dir) {
      $client_dir = $projects_dir . DIRECTORY_SEPARATOR . $client_dir;
      if (is_dir($client_dir)) {
        foreach (scandir($client_dir) as $project_dir) {
          $project_dir = $client_dir . DIRECTORY_SEPARATOR . $project_dir;
          $environment = $project_dir . DIRECTORY_SEPARATOR . 'environment.yml';
          if (is_dir($project_dir) && file_exists($environment)) {
            $yaml = Yaml::parse(file_get_contents($environment));
            $projects[] = [
              $yaml['client'],
              $yaml['name'],
              $yaml['projectType'],
              $yaml['host']
            ];
          }
        }
      }
    }
    if ($projects) {
      $headers = [
        'Client',
        'Project',
        'Project Type',
        'Server'
      ];
      $table = new Table($output);
      $table->setHeaders($headers);
      $table->addRows($projects);
      $table->render();
    }
    else {
      $helper = $this->getHelper('question');
      $question = new Question('No projects created yet. Would you like to create a new project? ', 'y');
      $question->setValidator([$this, 'validateCreate']);
      if ($helper->ask($input, $output, $question)) {
        $create = $this->getApplication()->find('project:create');
        $arguments = new ArrayInput([]);
        $create->run($arguments, $output);
      }
    }
  }

  public function validateCreate($answer) {
    $answer = strtolower($answer);
    $answers = [
      'y',
      'yes',
      'n',
      'no'
    ];
    if (!in_array($answer, $answers)) {
      throw new RuntimeException("Choices are limited to \"y\", \"yes\", \"n\" or \"no\"");
    }
    if ($answer == 'y' || $answer == 'yes') {
      return TRUE;
    }
    if ($answer == 'n' || $answer == 'no') {
      return FALSE;
    }
  }

}
