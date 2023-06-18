<?php

namespace sonfd\composer_unpack\Operations;

use Composer\Factory as ComposerFactory;
use Composer\Json\JsonManipulator;

class ComposerRemoveOperation implements OperationInterface {

  protected string $packageName;
  protected string $versionConstraint;
  protected array $typesToRemove;

  public function __construct(string $packageName, string $versionConstraint, array $typesToRemove = ['require', 'require-dev']) {
    $this->packageName = $packageName;
    $this->versionConstraint = $versionConstraint;
    $this->typesToRemove = $typesToRemove;
  }

  public function getId(): string {
    return 'composer_remove' .
      '__' . $this->packageName .
      '__' . $this->versionConstraint;
  }

  public function execute(): bool {
    $composerFile = ComposerFactory::getComposerFile();
    $composerJsonString = file_get_contents($composerFile);
    $jsonManipulator = new JsonManipulator($composerJsonString);

    foreach ($this->typesToRemove as $type) {
      $jsonManipulator->removeSubNode($type, $this->packageName);
    }

    file_put_contents($composerFile, $jsonManipulator->getContents());
  }
}
