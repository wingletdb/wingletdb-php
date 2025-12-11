<?php

namespace WingletDB;

class Meta {
  public array $data;

  public function __construct(array|null $data=null){
    $this->data = [
      "counter" => $data["counter"] ?? 0,
      "updatedAt" => $data["updatedAt"] ?? time()
    ];
  }

  public function incrementCounter(): int {
    $this->data["counter"]++;
    return $this->data["counter"];
  }

  public function update($records): void {
    $this->data["updatedAt"] = time();
  }
}
