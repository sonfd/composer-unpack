<?php

namespace sonfd\composer_unpack\Commands;

use Composer\Command\BaseCommand;
use Composer\Factory;
use Composer\Json\JsonManipulator;
use Composer\Package\CompletePackageInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * The "unpack" command class.
 */
class UnpackCommand extends BaseCommand {

  /**
   * The types of packages that should be auto-unpacked.
   *
   * @todo Make this configurable.
   *
   * @var string[]
   */
  protected array $autoRecurseTypes = ['drupal-recipe'];

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
    $packages = array_map([$this, 'getPackageByName'], $packageNames);
    array_walk($packages, [$this, 'doUnpackPackage']);

    return 0;
  }

  /**
   * Get an installed composer package by name.
   *
   * @param string $packageName
   *   The name of the package to search for.
   *
   * @return CompletePackageInterface|null
   *   If the package exists, a CompletePackageInterface object, otherwise null.
   */
  protected function getPackageByName(string $packageName): ?CompletePackageInterface {
    $composerJson = $this->requireComposer()->getRepositoryManager();
    return $composerJson->findPackage($packageName, '*');
  }

  /**
   * Perform all steps necessary to unpack a single package.
   *
   * @param CompletePackageInterface $package
   *   The package to unpack.
   */
  protected function doUnpackPackage(CompletePackageInterface $package) {
    $this->getIO()->write("Unpacking package: {$package->getName()}.");
    $this->unpackRequirements($package, 'require');
    $this->unpackRequirements($package, 'require-dev');
    $this->removePackageRequirement($package);
  }

  /**
   * Unpack a package's requirements.
   *
   * @todo convert this to its own 'Operation' class.
   *
   * @param CompletePackageInterface $package
   *   The package to unpack.
   * @param string $reqType
   *  The type of requirements to unpack, either 'require' or 'require-dev'.
   */
  protected function unpackRequirements(CompletePackageInterface $package, string $reqType = 'require') {
    $requirements = $reqType === 'require' ? $package->getRequires() : $package->getDevRequires();
    foreach($requirements as $requirement) {
      $reqName = $requirement->getTarget();
      $reqVersion = $requirement->getPrettyConstraint();

      $reqPackage = $this->getPackageByName($reqName);
      if ($this->shouldRecursivelyUnpack($reqPackage)) {
        // If the package is of a package type that should be auto-unpacked,
        // recursively unpack it rather than merging it.
        $this->doUnpackPackage($reqPackage);
      }

      $this->mergeToProjectComposer($reqName, $reqVersion, $reqType);
    }
  }

  /**
   * Remove a requirement from composer.json.
   *
   * @todo convert this to its own 'Operation' class.
   *
   * @param Composer\Package\CompletePackageInterface|null $package
   *   The package to remove, or null.
   */
  protected function removePackageRequirement(?CompletePackageInterface $package) {
    $composerFile = Factory::getComposerFile();
    $composerJsonString = file_get_contents($composerFile);
    $composerJsonArray = json_decode($composerJsonString, TRUE);
    $jsonManipulator = new JsonManipulator($composerJsonString);

    if (isset($composerJsonArray['require'][$package->getName()])) {
      $jsonManipulator->removeSubNode('require', $package->getName());
    }
    if (isset($composerJsonArray['require-dev'][$package->getName()])) {
      $jsonManipulator->removeSubNode('require-dev', $package->getName());
    }

    file_put_contents($composerFile, $jsonManipulator->getContents());
  }

  /**
   * Add a new package to the composer.json.
   *
   * @param string $packageName
   *   The name of the package to merge into composer.json.
   * @param string $packageVersion
   *   The version constraint to use.
   * @param string $reqType
   *   The type of requirement to merge: 'require' or 'require-dev'.
   */
  protected function mergeToProjectComposer(string $packageName, string $packageVersion, string $reqType):void {
    $composerFile = Factory::getComposerFile();
    $composerJsonString = file_get_contents($composerFile);
    $composerJsonArray = json_decode($composerJsonString, TRUE);
    $jsonManipulator = new JsonManipulator($composerJsonString);

    // Check that the package is not already required.
    if (isset($composerJsonArray[$reqType][$packageName])) {
      // Package already required.
      // TODO: check constraints.
      return;
    }

    $jsonManipulator->addLink($reqType, $packageName, $packageVersion, TRUE);
    file_put_contents($composerFile, $jsonManipulator->getContents());
  }

  /**
   * Determine if a package should be auto-unpacked.
   *
   * @param CompletePackageInterface|null $package
   *   The package to check.
   * @return bool
   *   TRUE if the package should be auto-unpacked, otherwise FALSE.
   */
  protected function shouldRecursivelyUnpack(?CompletePackageInterface $package): bool {
    if ($package instanceof CompletePackageInterface) {
      return in_array($package->getType(), $this->autoRecurseTypes);
    }

    // We probably shouldn't ever be here because it should mean that the
    // package is not installed.
    return FALSE;
  }

}
