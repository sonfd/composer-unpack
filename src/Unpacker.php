<?php

namespace sonfd\composer_unpack;

use Composer\Package\CompletePackageInterface;
use Composer\Repository\RepositoryManager;
use sonfd\composer_unpack\Operations\ComposerRequireOperation;

/**
 * An Unpacker class to unpack a composer package.
 */
class Unpacker implements UnpackerInterface {

  protected string $composerFile;
  protected array $packageNames = [];
  protected OperationManagerInterface $operationManager;
  protected RepositoryManager $repositoryManager;
  protected array $autoRecurseTypes = ['drupal-recipe'];

  /**
   * Construct a new Unpacker object.
   *
   * @param string $composerFile
   *  The path to the composer.json file.
   * @param Composer\Repository\RepositoryManager $repositoryManager
   *   A composer repository manager class.
   */
  public function __construct(string $composerFile, RepositoryManager $repositoryManager) {
    $this->composerFile = $composerFile;
    $this->repositoryManager = $repositoryManager;
    $this->operationManager = new OperationManager();
  }

  /**
   * {@inheritdoc}
   */
  public function unpack(CompletePackageInterface $package): bool {
    array_walk($packages, fn(CompletePackageInterface $package) =>
      $this->operationManager
        ->addOperations($this->generateRequireOps($package)));

    return $this->operationManager->executeOperations();
  }

    /**
   * Unpack a package's requirements.
   *
   * @todo convert this to its own 'Operation' class.
   *
   * @param Composer\Package\CompletePackageInterface $package
   *   The package to unpack.
   * @param string $reqType
   *  The type of requirements to unpack, either 'require' or 'require-dev'.
   *
   * @return OperationInterface[]
   *   An array of operations.
   */
  protected function generateRequireOps(CompletePackageInterface $package, string $reqType = 'require'): array {
    $ops = [];

    // @todo: this foreach feels sus.
    $requirements = $reqType === 'require' ? $package->getRequires() : $package->getDevRequires();
    foreach ($requirements as $requirement) {
      $reqName = $requirement->getTarget();
      $reqVersion = $requirement->getPrettyConstraint();

      $reqPackage = $this->repositoryManager->findPackage($reqName, '*');
      if ($reqPackage instanceof CompletePackageInterface && $this->shouldRecursivelyUnpack($reqPackage)) {
        $this->unpack($reqPackage);
      }

      $ops[] = new ComposerRequireOperation($reqName, $reqVersion, $reqType);
    }

    return $ops;
  }

  /**
   * Determine if a package should be auto-unpacked.
   *
   * @param Composer\Package\CompletePackageInterface $package
   *   The package to check.
   * @return bool
   *   TRUE if the package should be auto-unpacked, otherwise FALSE.
   */
  protected function shouldRecursivelyUnpack(CompletePackageInterface $package): bool {
    return in_array($package->getType(), $this->autoRecurseTypes);
  }

}
