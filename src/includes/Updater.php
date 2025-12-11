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
      $records = $db->findMany();
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
      $records = $db->findMany();
      $this->updateDerived($records);
    });
  }

  public function rebuild(){
    $this->transaction(function(){
      $db = $this->db;
      $records = $db->findMany();

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
}
