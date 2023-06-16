<?php

namespace sonfd\composer_unpack\Commands;

use Composer\Command\BaseCommand;
use Composer\Factory as ComposerFactory;
use Composer\Package\CompletePackageInterface;
use Composer\Repository\RepositoryManager;
use sonfd\composer_unpack\Unpacker;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "unpack" command class.
 */
class UnpackCommand extends BaseCommand {

  protected string $composerFile;
  protected RepositoryManager $repositoryManager;
  protected Unpacker $unpacker;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    parent::__construct();
    $this->composerFile = ComposerFactory::getComposerFile();
    $this->repositoryManager = $this->requireComposer()->getRepositoryManager();
    $this->unpacker = new Unpacker($this->composerFile, $this->repositoryManager);
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('composer_unpack:unpack')
      ->setAliases(['unpack'])
      ->setDefinition([
        new InputArgument('packages', InputArgument::IS_ARRAY | InputArgument::REQUIRED, "Installed packages to unpack.")
    ]);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output): int {
    $packageNames = $input->getArgument('packages');
    $packages = array_map(fn(string $packageName) =>
      $this->repositoryManager
        ->findPackage($packageName, '*'),
      $packageNames
    );
    array_walk($packages, fn(CompletePackageInterface $package) =>
      $this->unpacker->unpack($package));

    return 0;
  }

}
