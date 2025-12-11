<?php

namespace WingletDB;

class Manager {
  public string $schemaDir;
  public string $databaseDir;

  public function __construct($config){
    $this->schemaDir = rtrim($config["schemaDir"], "/");
    $this->databaseDir = rtrim($config["databaseDir"], "/");
  }

  public function getDB($name){
    $schemaFile = "{$this->schemaDir}/{$name}.php";

    if (!file_exists($schemaFile)) {
      throw new \Exception("Schema not found: $schemaFile");
    }

    return new Database(
      "{$this->databaseDir}/{$name}",
      include $schemaFile
    );
  }
}
