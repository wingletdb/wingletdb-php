<?php

namespace WingletDB;

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
        if($db->schema->idType === "auto"){
          // id なければ発行
          $record->id = $db->meta->incrementCounter();

        }else{
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
      $full = $db->getData("full", []);

      if($isNew && isset($full[$record->id])){
        throw new \Exception(); // TODO
      }

      $full[$record->id] = $record->toArray();
      $this->save($db->getFilePath("full"), $full);

      /* --------------------------------------------------
       * misc
       */
      $this->updateLists();
      $this->updateMeta();
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
      $full = $db->getData("full", []);
      unset($full[$id]);
      $this->save($db->getFilePath("full"), $full);

      /* --------------------------------------------------
       * misc
       */
      $this->updateLists();
      $this->updateMeta();
    });
  }

  public function rebuild(){
    $this->transaction(function(){
      $db = $this->db;

      /* --------------------------------------------------
       * records/[id].json
       * */
      // TODO

      /* --------------------------------------------------
       * full.json
       * */
      // TODO

      /* --------------------------------------------------
       * misc
       * */
      $this->updateLists();
      $this->updateMeta();
    });
  }

  private function updateLists(){
    $db = $this->db;
    $fullRecords = $db->findFull();

    foreach($db->schema->getListStructures() as $name => $generator){
      $this->save($db->getFilePath($name), $generator($fullRecords, $db));
    }
  }

  private function updateMeta(){
    $db = $this->db;
    $db->meta->update();
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