<?php

namespace sonfd\composer_unpack\Operations;

use Composer\Factory as ComposerFactory;
use Composer\Json\JsonManipulator;

/**
 * Class ComposerRequireOperation.
 */
class ComposerRequireOperation implements OperationInterface {

  protected string $id;
  protected string $packageName;
  protected string $versionConstraint;
  protected string $requirementType;

  /**
   * Construct a new ComposerRequireOperation object.
   *
   * @param string $packageName
   * @param string $versionConstraint
   * @param string $requirementType
   */
  public function __construct(string $packageName, string $versionConstraint, string $requirementType = 'require') {
    $this->packageName = $packageName;
    $this->versionConstraint = $versionConstraint;
    $this->requirementType = $requirementType;
  }

  /**
   * {@inheritdoc}
   */
  public function getId(): string {
    return 'composer_require'
      . '__' . $this->requirementType
      . '__' . $this->packageName
      . '__' . $this->versionConstraint;
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): bool {
    $composerFile = ComposerFactory::getComposerFile();
    $composerJsonString = file_get_contents($composerFile);
    $composerJsonArray = json_decode($composerJsonString, TRUE);
    $jsonManipulator = new JsonManipulator($composerJsonString);

    // Check that the package is not already required.
    if (isset($composerJsonArray[$this->requirementType][$this->packageName])) {
      // Package already required.
      // TODO: check constraints.
      return TRUE;
    }

    $jsonManipulator->addLink($this->requirementType, $this->packageName, $this->versionConstraint, TRUE);
    return (bool) file_put_contents($composerFile, $jsonManipulator->getContents());
  }

}
