<?php

namespace WingletDB;

class Schema {
  public string $idType; // auto | string
  public array $fields;
  private array $listStructures;

  public function __construct(array $schema){
    $this->idType = $schema["id"];
    $this->fields = $schema["fields"];
    $this->listStructures = $schema["lists"];
  }

  public function has(string $fieldName): bool {
    foreach($this->fields as $name => $normalize){
      if($name === $fieldName) return true;
    }

    return false;
  }

  public function getListStructures(): array {
    return $this->listStructures;
  }
}