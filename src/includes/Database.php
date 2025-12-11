<?php

namespace WingletDB;

class Database {
  private string $dir;
  public Schema $schema;
  public Meta $meta;
  public Updater $updater;

  public function __construct(string $dir, array $schema){
    $this->dir = rtrim($dir, "/");
    $this->schema = new Schema($schema);
    $this->loadMeta();
    $this->updater = new Updater($this);
  }

  public function create(array $data=[]): Record {
    return new Record($this->schema, $data);
  }

  public function findOne(mixed $id): Record|null {
    if($this->recordExists($id)){
      return Record::load($this->schema, $this->getRecordData($id));
    }

    return null;
  }

  public function findBy($listName, $filter=null): array {
    $list = $this->getData($listName);
    $records = [];

    foreach($list as $id => $data){
      $record = Record::load($this->schema, ["id" => $id, ...$data]);
      if(!$filter || $filter($record)){
        $records[$id] = $record;
      }
    }

    return $records;
  }

  public function findFull($filter=null): array {
    $list = $this->getData("full");
    $records = [];

    foreach($list as $id => $data){
      $record = Record::load($this->schema, ["id" => $id, ...$data]);
      if(!$filter || $filter($record)){
        $records[$id] = $record;
      }
    }

    // TODO

    return $records;
  }

  public function loadMeta(){
    $data = null;

    if($this->fileExists("meta")){
      $data = $this->getData("meta");
    }

    $this->meta = new Meta($data);
  }

  public function getRecordFilePath(int|string $id): string {
    return $this->getFilePath("records/{$id}");
  }

  public function recordExists(int|string $id): bool {
    return file_exists($this->getRecordFilePath($id));
  }

  private function getRecordData(int|string $id): array {
    return ["id" => $id, ...json_decode(file_get_contents($this->getRecordFilePath($id)), true)];
  }

  public function getFilePath(string $name): string {
    return "{$this->dir}/{$name}.json";
  }

  public function fileExists(string $name): bool {
    return file_exists($this->getFilePath($name));
  }

  public function getData(string $name, mixed $default=[]): array {
    if(!$this->fileExists($name)) return $default;
    return json_decode(file_get_contents($this->getFilePath($name)), true);
  }
}