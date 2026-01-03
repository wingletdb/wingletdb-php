<?php

namespace WingletDB;

use WingletDB\Helper\ID;

class Updater {
  public function __construct(
    private Database $db
  ){}

  public function saveRecord(Record $record): void {
    $this->transaction(function()use($record){
      $db = $this->db;
      $isNew = !isset($record->id);

      $db->loadMeta();

      if($isNew){
        // id なければ発行
        switch($db->schema->idType){
          case "auto":
            $record->id = $db->meta->incrementCounter();
            break;

          case "uuid":
            $record->id = ID::uuidv4();
            break;

          default:
            throw new \Exception(); // TODO
        }
      }

      /* --------------------------------------------------
       * records/[id].json
       */
      if($isNew && $db->recordExists($record->id)){
        throw new \Exception(); // TODO
      }

      $record->normalize();
      $this->save($db->getRecordFilePath($record->id), $record->toArray());

      /* --------------------------------------------------
       * full.json
       */
      $fullArray = $db->getData("full", []);

      if($isNew && isset($fullArray[$record->id])){
        throw new \Exception(); // TODO
      }

      $fullArray[$record->id] = $record->toArray();
      $this->save($db->getFilePath("full"), $fullArray);

      /* --------------------------------------------------
       * other derived data
       */
      $records = $db->find();
      $this->updateDerived($records);

    });
  }

  public function deleteRecord(string $id): void {
    $this->transaction(function()use($id){
      $db = $this->db;

      /* --------------------------------------------------
       * records/[id].json
       */
      $this->delete($db->getRecordFilePath($id));

      /* --------------------------------------------------
       * full.json
       */
      $fullArray = $db->getData("full", []);
      unset($fullArray[$id]);
      $this->save($db->getFilePath("full"), $fullArray);

      /* --------------------------------------------------
       * other derived data
       */
      $records = $db->find();
      $this->updateDerived($records);
    });
  }

  public function rebuild(){
    $this->transaction(function(){
      $db = $this->db;
      $records = $db->find();

      /* --------------------------------------------------
       * records/[id].json
       * */
      // TODO

      /* --------------------------------------------------
       * full.json
       * */
      // TODO

      /* --------------------------------------------------
       * other derived data
       */
      $this->updateDerived($records);

    });
  }

  private function updateDerived($records) {
    $this->updateLists($records);
    $this->updateViews($records);
    $this->updateMeta($records);
  }

  private function updateLists($records) {
    $db = $this->db;

    foreach($db->schema->lists as $name => $conf){
      $filter = $conf["filter"] ?? null;
      $sort   = $conf["sort"] ?? null;
      $fields = $conf["fields"] ? ["id", ...$conf["fields"]] : null;

      if($filter){
        $records = array_filter($records, $filter);
      }

      if($sort){
        if(is_callable($sort)){
          uasort($records, $sort);

        }else{
          uasort($records, function($a, $b)use($sort) {
            foreach ($sort as $key => $dir) {
              $av = $a->{$key};
              $bv = $b->{$key};

              if ($av == $bv) continue;

              $cmp = $av <=> $bv;
              return $dir === "desc" ? -$cmp : $cmp;
            }

            return 0;
          });
        }
      }

      $result = [];

      foreach($records as $id => $record){
        if($fields){
          $result[$id] = array_combine(
            $fields,
            array_map(fn($prop) => $record->{$prop}, $fields)
          );
        }else{
          $result[$id] = $record->toArray();
        }
      }

      $this->save($db->getFilePath("lists/{$name}"), $result);
    }
  }

  private function updateViews($records){
    $db = $this->db;

    foreach($db->schema->views as $name => $generator){
      $this->save($db->getFilePath("views/{$name}"), $generator($records, $db));
    }
  }

  private function updateMeta($records){
    $db = $this->db;
    $db->meta->update($records);
    $this->save($db->getFilePath("meta"), $db->meta->data);
  }

  public function transaction($callback){
    // TODO
    $callback($this);
  }

  private function save($path, $data){
    if(!file_exists(dirname($path))){
      mkdir(dirname($path), 0755, true);
    }
    file_put_contents($path, empty($data) ? "{}" : json_encode($data));
  }

  private function delete($path){
    if(file_exists($path)) unlink($path);
  }

  /**
   * レコードを指定位置に移動
   *
   * @param string|int $id 移動するレコードのID
   * @param int $newPosition 新しい位置（0-indexed）
   */
  public function moveRecord(string|int $id, int $newPosition): void {
    $this->transaction(function() use ($id, $newPosition) {
      $db = $this->db;
      $fullArray = $db->getData("full", []);

      if (!isset($fullArray[$id])) {
        throw new \Exception("Record not found: {$id}");
      }

      // 配列を再構築
      $keys = array_keys($fullArray);
      $values = array_values($fullArray);

      // 現在の位置を取得
      $currentIndex = array_search($id, $keys);

      if ($currentIndex === false) {
        throw new \Exception("Record not found in keys: {$id}");
      }

      // 要素を削除
      $key = $keys[$currentIndex];
      $value = $values[$currentIndex];
      array_splice($keys, $currentIndex, 1);
      array_splice($values, $currentIndex, 1);

      // 新しい位置に挿入
      $newPosition = max(0, min($newPosition, count($keys)));
      array_splice($keys, $newPosition, 0, [$key]);
      array_splice($values, $newPosition, 0, [$value]);

      // 連想配列を再構築
      $fullArray = array_combine($keys, $values);

      // 保存
      $this->save($db->getFilePath("full"), $fullArray);

      // derived dataを更新
      $records = $db->find();
      $this->updateDerived($records);
    });
  }

  /**
   * 2つのレコードの位置を入れ替え
   *
   * @param string|int $id1 レコード1のID
   * @param string|int $id2 レコード2のID
   */
  public function swapRecords(string|int $id1, string|int $id2): void {
    $this->transaction(function() use ($id1, $id2) {
      $db = $this->db;
      $fullArray = $db->getData("full", []);

      if (!isset($fullArray[$id1]) || !isset($fullArray[$id2])) {
        throw new \Exception("One or both records not found");
      }

      // キーと値のペアの位置を入れ替える
      $keys = array_keys($fullArray);
      $values = array_values($fullArray);

      $index1 = array_search($id1, $keys);
      $index2 = array_search($id2, $keys);

      if ($index1 === false || $index2 === false) {
        throw new \Exception("Record IDs not found in keys");
      }

      // キーと値の両方を入れ替え
      [$keys[$index1], $keys[$index2]] = [$keys[$index2], $keys[$index1]];
      [$values[$index1], $values[$index2]] = [$values[$index2], $values[$index1]];

      // 新しい順序で連想配列を再構築
      $newFullArray = array_combine($keys, $values);

      // 保存
      $this->save($db->getFilePath("full"), $newFullArray);

      // derived dataを更新
      $records = $db->find();
      $this->updateDerived($records);
    });
  }

  /**
   * レコードを完全に並び替え
   *
   * @param array $orderedIds 新しい順序のID配列
   */
  public function reorderRecords(array $orderedIds): void {
    $this->transaction(function() use ($orderedIds) {
      $db = $this->db;
      $fullArray = $db->getData("full", []);

      // すべてのIDが存在するかチェック
      foreach ($orderedIds as $id) {
        if (!isset($fullArray[$id])) {
          throw new \Exception("Record not found: {$id}");
        }
      }

      // 指定されていないレコードがあればエラー
      if (count($orderedIds) !== count($fullArray)) {
        throw new \Exception("All records must be specified in reorder");
      }

      // 新しい順序で連想配列を再構築
      $newFullArray = [];
      foreach ($orderedIds as $id) {
        $newFullArray[$id] = $fullArray[$id];
      }

      // 保存
      $this->save($db->getFilePath("full"), $newFullArray);

      // derived dataを更新
      $records = $db->find();
      $this->updateDerived($records);
    });
  }
}
