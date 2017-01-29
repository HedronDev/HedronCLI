<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;

class CreateProjectCommand extends HedronCommand {

  protected function configure() {
    $this->setName('project:create')
      ->setDescription('Create a new project')
      ->addArgument('server', InputArgument::OPTIONAL, 'The server on which this project should be created.', 'local');
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
    // @todo validate project type
    $question = new Question('Project Type: ', '');
    $projectType = $helper->ask($input, $output, $question);
    $server = $input->getArgument('server');

    $project_dir = strtolower($client) . DIRECTORY_SEPARATOR . strtolower($projectName);

    $environment = [];
    $environment['client'] = $client;
    $environment['name'] = $projectName;
    $environment['projectType'] = $projectType;
    $environment['host'] = $server;
    $question = new Question("Preferred Project IDE Location ({$yaml['preferredIDE']}): ", $yaml['preferredIDE']);
    $question->setValidator([$this, 'validateIDELocation']);
    $environment['projectIDE'] = $helper->ask($input, $output, $question);
    $environment['gitDirectory'] = $this->getHedronDir('working_dir', $project_dir);
    if (mkdir($this->getHedronDir('working_dir', $project_dir), 0777, TRUE)) {
      $output->writeln('<info>Project working directory successfully created.</info>');
    }
    $environment['gitRepository'] = $this->getHedronDir('repositories', $project_dir);
    if (mkdir($this->getHedronDir('repositories', $project_dir), 0777, TRUE)) {
      $output->writeln('<info>Project repository directory successfully created.</info>');
    }
    $environment['dockerDirectory'] = $this->getHedronDir('docker', $project_dir);
    if (mkdir($this->getHedronDir('docker', $project_dir), 0777, TRUE)) {
      $output->writeln('<info>Project docker directory successfully created.</info>');
    }
    $environment['dataDirectory'] = $this->getHedronDir('data', $project_dir, '{branch}', 'web');
    if (mkdir($this->getHedronDir('data', $project_dir), 0777, TRUE)) {
      $output->writeln('<info>Project data directory successfully created.</info>');
    }
    if (mkdir($this->getHedronDir('data', $project_dir, 'master', 'web'), 0777, TRUE)) {
      $output->writeln('<info>Project data web directory successfully created.</info>');
    }
    if (mkdir($this->getHedronDir('data', $project_dir, 'master', 'sql'), 0777, TRUE)) {
      $output->writeln('<info>Project data sql directory successfully created.</info>');
    }

    // Make the project.
    $dir = $this->getHedronDir('project', $project_dir);
    if (mkdir($dir, 0777, TRUE)) {
      $environment_file = $this->getHedronDir('project', $project_dir, 'environment.yml');
      file_put_contents($environment_file, Yaml::dump($environment, 10));
    }

    // Make the repository.
    $dir = $this->getHedronDir('repositories', $project_dir);
    $commands = [];
    $commands[] = "cd $dir";
    $commands[] = "git init --bare";
    shell_exec(implode('; ', $commands));
    unset($commands);
    if (file_exists($dir . DIRECTORY_SEPARATOR . 'hooks')) {
      $hedron_hooks = $this->getHedronDir('hedron', 'vendor', 'hedron', 'hedron', 'hooks');
      $git_hooks_dir = $dir . DIRECTORY_SEPARATOR . 'hooks';
      foreach (array_diff(scandir($hedron_hooks), array('..', '.')) as $file_name) {
        $file = "#!{$yaml['php']}\n";
        $file .= file_get_contents($hedron_hooks . DIRECTORY_SEPARATOR . $file_name);
        file_put_contents($git_hooks_dir . DIRECTORY_SEPARATOR . $file_name, $file);
        $file_name = $git_hooks_dir . DIRECTORY_SEPARATOR . $file_name;
        shell_exec("chmod a+x $file_name");
      }
      $clone_dir = strtolower($client) . '_' . strtolower($projectName);
      $output->writeln("<info>To begin working:
git clone $dir $clone_dir
OR in the existing git working directory you wish sync with this repository:
git remote add origin $dir
git push -u origin master

Your website volume for docker-compose.yml configuration is: \${HEDRON_WEB_VOL}
Your sql volume for docker-compose.yml configuration is: \${HEDRON_SQL_VOL}</info>");
    }
  }

  public function validateClient($answer) {
    $file = $this->getHedronDir('hedron.yml');
    $yaml = Yaml::parse(file_get_contents($file));
    if (!in_array($answer, $yaml['client'])) {
      throw new RuntimeException("The selected client does not exists, run the client:create command to add the client first.");
    }
    return $answer;
  }

  public function validateIDELocation($answer) {
    if (!$answer) {
      return;
    }
    if (!file_exists(str_replace('\\', '', $answer))) {
      throw new RuntimeException("The location of your IDE could not be validated, please check and try again.");
    }
    return $answer;
  }
}