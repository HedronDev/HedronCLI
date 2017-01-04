<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class CreateClientCommand extends HedronCommand {

  protected function configure() {
    $this->setName('client:create')
      ->setDescription('Create a new client');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $hedron_directory = $this->getHedronDir();
    if (!file_exists($hedron_directory)) {
      mkdir($hedron_directory);
    }
    $file = $hedron_directory . DIRECTORY_SEPARATOR . 'hedron.yml';
    $helper = $this->getHelper('question');
    $question = new Question('Client name: ', '');
    $question->setValidator([$this, 'validateClient']);
    $client = $helper->ask($input, $output, $question);
    if (!file_exists($file)) {
      $yaml = [];
    }
    else {
      $yaml = Yaml::parse(file_get_contents($file));
    }
    $yaml['client'][] = $client;
    file_put_contents($file, Yaml::dump($yaml, 10));
  }

  public function validateClient($answer) {
    if (strpos($answer, ' ') !== FALSE) {
      throw new RuntimeException("Client names may not contain spaces.");
    }
    $user_directory = trim(shell_exec("cd ~; pwd"));
    $hedron_directory = $user_directory . DIRECTORY_SEPARATOR . '.hedron';
    $file = $hedron_directory . DIRECTORY_SEPARATOR . 'hedron.yml';
    if (!file_exists($file)) {
      return $answer;
    }
    $yaml = Yaml::parse(file_get_contents($file));
    if (empty($yaml['client'])) {
      return $answer;
    }
    if (in_array($answer, $yaml['client'])) {
      throw new RuntimeException("That client name already exists.");
    }
    return $answer;
  }

}
