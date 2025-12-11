<?php

namespace WingletDB;

class Meta {
  public array $data;

  public function __construct(array|null $data=null){
    if($data){
      $this->data = $data;
    }else{
      $this->data = [
        "counter" => 0,
        "updatedAt" => time()
        // TODO
      ];
    }
  }

  public function incrementCounter(): int {
    $this->data["counter"]++;
    return $this->data["counter"];
  }

  public function update(): void {
    $this->data["updatedAt"] = time();
  }
}
