<?php

namespace WingletDB;

class Record {
  public int|string|null $id;
  public array $data;
  private bool $isNormalized = false;

  public function __construct(
    private Schema $schema,
    array $data
  ){
    $this->bind($data);
  }

  public static function load(Schema $schema, array $data){
    $record = new Record($schema, $data);
    $record->isNormalized = true;
    return $record;
  }

  public function get(string $fieldName): mixed {
    if(!$this->isNormalized){
      throw new \Exception("Record is not normalized"); // TODO
    }

    if(!$this->schema->has($fieldName)){
      throw new \Exception(); // TODO
    }

    return $this->data[$fieldName] ?? null;
  }

  public function __get($property){
    if(!isset($this->data[$property])){
      throw new \Exception("Undefined record field '{$property}'"); // TODO
    }

    return $this->get($property);
  }

  public function set(string $fieldName, mixed $value): void {
    $this->isNormalized = false;

    if($fieldName === "id"){
      $this->id = $value;
      return;
    }

    if(!$this->schema->has($fieldName)){
      throw new \Exception(); // TODO
    }

    $this->data[$fieldName] = $value;
  }

  public function bind(array $data): void {
    $this->id = $data["id"] ?? null;

    foreach($this->schema->fields as $name => $spec){
      $this->set($name, $data[$name] ?? null);
    }
  }

  public function normalize(){
    foreach($this->schema->fields as $name => $normalize){
      if($normalize === true) $normalize = fn($v, $record) => $v;
      $this->data[$name] = $normalize($this->data[$name], $this->data);
    }

    $this->isNormalized = true;
  }

  public function toArray(){
    return ["id" => $this->id, ... $this->data];
  }

  public function __debugInfo() : array {
    return [
      "id" => $this->id,
      "data" => $this->data
    ];
  }
}
