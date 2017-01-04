<?php

namespace Hedron\CLI\Command;

use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class DockerCommand extends HedronCommand {

  protected function configure() {
    $this->addArgument('client', InputArgument::OPTIONAL, 'The client.', '')
      ->addArgument('project', InputArgument::OPTIONAL, 'The project.', '')
      ->addArgument('branch', InputArgument::OPTIONAL, 'The branch.', '')
      ->addArgument('remote', InputArgument::OPTIONAL, 'Custom remote.', 'origin');
  }

  protected function getClientProjectBranch(InputInterface $input, OutputInterface $output) {
    $client = $input->getArgument('client');
    $project = $input->getArgument('project');
    $branch = $input->getArgument('branch');
    if (!$client && !$project) {
      $output->writeln("<info>Attempting to extract client, project and branch information from local git working directory.</info>");
      $remote = $input->getArgument('remote');
      if ($remote == 'origin') {
        $output->writeln("<info>Assuming your remote origin is pointing at a typical hedron repository. If this is not the case, you may set this by passing a custom remote argument.</info>");
      }
      $remotes = shell_exec("git remote -v");
      foreach (explode("\n", $remotes) as $line) {
        $fetch_pos = strrpos($line, '(fetch)');
        if (strpos($line, $remote) === 0 && $fetch_pos) {
          $remote_len = strlen($remote);
          $length = $fetch_pos - $remote_len;
          $line = trim(substr($line, $remote_len, $length));
          $directory = explode(DIRECTORY_SEPARATOR, $line);
          $project = array_pop($directory);
          $client = array_pop($directory);
          break;
        }
      }
    }
    if (!$project || !$client) {
      throw new RuntimeException("The client or project could not be determined. Try manually passing them as arguments to this command.");
    }
    if (!$branch) {
      $branch = trim(shell_exec("git rev-parse --abbrev-ref HEAD"));
    }
    if (!$branch) {
      throw new RuntimeException("The branch could not be determined. Try manually passing it as an argument to this command.");
    }
    return [$client, $project, $branch];
  }
}