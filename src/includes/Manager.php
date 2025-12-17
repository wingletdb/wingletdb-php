<?php

namespace WingletDB;

class Manager {
  public string $schemaDir;
  public string $databaseDir;

  public function __construct(array $config){
    $this->schemaDir = rtrim($config["schemaDir"], "/");
    $this->databaseDir = rtrim($config["databaseDir"], "/");
  }

  public function getDB(string $name): Database{
    $schemaFile = "{$this->schemaDir}/{$name}.php";

    if (!file_exists($schemaFile)) {
      throw new \Exception("Schema not found: $schemaFile");
    }

    return new Database(
      "{$this->databaseDir}/{$name}",
      include $schemaFile
    );
  }

  public function getDBBySchema(string $name, array $schema): Database {
    return new Database(
      "{$this->databaseDir}/{$name}",
      $schema
    );
  }
}
