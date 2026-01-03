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

  /**
   * IDで単一レコードを取得
   */
  public function get(mixed $id): Record|null {
    if($this->recordExists($id)){
      return Record::load($this->schema, $this->getRecordData($id));
    }

    return null;
  }

  /**
   * フィールド条件で単一レコードを取得（最初の1件）
   */
  public function getBy($fieldName, $value): Record|null {
    $records = $this->find(fn($record) => $record->{$fieldName} === $value);
    return array_shift($records);
  }

  /**
   * 全レコードを連想配列で検索（ID => Record）
   *
   * @param callable|null $filter フィルター関数
   * @return array 連想配列（ID => Record）
   */
  public function find($filter=null): array {
    $records = [];

    foreach($this->getData("full") as $id => $data){
      $record = Record::load($this->schema, ["id" => $id, ...$data]);
      if(!$filter || $filter($record)){
        $records[$id] = $record;
      }
    }

    return $records;
  }

  /**
   * 全レコードをインデックス配列で検索（順序保証）
   *
   * @param callable|null $filter フィルター関数
   * @return array インデックス配列（0 => Record, 1 => Record, ...）
   */
  public function findOrdered($filter=null): array {
    $records = [];

    foreach($this->getData("full") as $id => $data){
      $record = Record::load($this->schema, ["id" => $id, ...$data]);
      if(!$filter || $filter($record)){
        $records[] = $record;
      }
    }

    return $records;
  }

  /**
   * リストから検索
   *
   * @param string $listName リスト名
   * @param callable|null $filter フィルター関数
   * @return array 連想配列（ID => Record）
   */
  public function list($listName, $filter=null): array {
    $records = [];

    foreach($this->getData("lists/{$listName}") as $id => $data){
      $record = Record::load($this->schema, ["id" => $id, ...$data]);
      if(!$filter || $filter($record)){
        $records[$id] = $record;
      }
    }

    return $records;
  }

  /**
   * ビューを取得
   *
   * @param string $viewName ビュー名
   * @return mixed ビューデータ
   */
  public function view($viewName): mixed {
    return $this->getData("views/{$viewName}");
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