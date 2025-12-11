<?php

namespace WingletDB;

class Schema {
  public string $idType; // auto | uuid | string
  public array $fields;
  public array $lists;
  public array $views;

  public function __construct(array $schema){
    $this->idType = $schema["id"];
    $this->fields = $schema["fields"];
    $this->lists = $schema["lists"] ?? [];
    $this->views = $schema["views"] ?? [];

    // TODO
    // indexes
  }

  public function has(string $fieldName): bool {
    foreach($this->fields as $name => $normalize){
      if($name === $fieldName) return true;
    }

    return false;
  }
}