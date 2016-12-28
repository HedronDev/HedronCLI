<?php

namespace Hedron\CLI\Command;

use Hedron\CLI\Exception\MissingDockerException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class InstallHedronCommand extends Command {
  protected function configure() {
    $this->setName('core:install')
      ->setDescription('Install Hedron')
      ->setHelp('Installs the core (hedron/hedron) for this user on this machine.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $docker = shell_exec("docker --version");
    if (strpos($docker, 'Docker version ') !== 0) {
      throw new MissingDockerException("Docker must be installed for Hedron to be installed.");
    }
    // @todo check minimum docker version
    // Setup hedron configuration.
    $yaml = [];
    $helper = $this->getHelper('question');
    $question = new Question('PHP 7 Location: ', '');
    $question->setValidator([$this, 'validatePHPLocation']);
    $yaml['php'] = $helper->ask($input, $output, $question);
    $yaml['client'] = [];

    $user_directory = trim(shell_exec("cd ~; pwd"));
    $hedron_directory = $user_directory . DIRECTORY_SEPARATOR . '.hedron';
    if (!file_exists($hedron_directory)) {
      mkdir($hedron_directory);
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'hedron';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Hedron directory successfully created.");
      }
      $commands = [];
      $commands[] = "cd $dir";
      $commands[] = "composer create-project hedron/hedron --no-interaction -s dev .";
      shell_exec(implode("; ", $commands));
      if (file_exists($dir . DIRECTORY_SEPARATOR . 'vendor')) {
        $output->writeln("Hedron successfully installed.");
      }
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'project';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Project directory successfully created.");
      }
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'docker';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Docker directory successfully created.");
      }
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'repositories';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Git Repositories directory successfully created.");
      }
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'working_dir';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Git Working directory successfully created.");
      }
    }
    $dir = $hedron_directory . DIRECTORY_SEPARATOR . 'data';
    if (!file_exists($dir)) {
      if (mkdir($dir)) {
        $output->writeln("Data directory successfully created.");
      }
    }
    // Write hedron configuration.
    $file = $hedron_directory . DIRECTORY_SEPARATOR . 'hedron.yml';
    file_put_contents($file, Yaml::dump($yaml, 10));
  }

  public function validatePHPLocation($answer) {
    if (!file_exists($answer)) {
      throw new LogicException("The PHP path given does not appear to exist.");
    }
    $php_version = trim(shell_exec("$answer --version"));
    if (strpos($php_version, "PHP 7.") !== 0) {
      throw new LogicException("The specified PHP path is either the wrong version or is not PHP.");
    }
    return $answer;
  }

}