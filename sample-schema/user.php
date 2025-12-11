<?php

use WingletDB\Record;

return [
  "id" => "auto",
  "fields" => [
    "name" => fn($v, $record) => $v,
    "email" => fn($v, $record) => $v,
    "password" => function($v, $record){
      if(!$v) return "";
      if(strpos($v, "hashed:") === FALSE){
        return "hashed:" . password_hash($v, PASSWORD_DEFAULT);
      }
      return $v;
    },
    "createdAt" => fn($v, $record) => $v ?? date("Y-m-d H:i:s"),
    "updatedAt" => fn($v, $record) => date("Y-m-d H:i:s")
  ],
  "lists" => [
    "list" => function($fullRecords, $db){
      $records = [];

      foreach($fullRecords as $id => $record){
        $records[$id] = [
          "name" => $record->name,
          "email" => $record->email,
          "createdAt" => $record->createdAt,
          "updatedAt" => $record->updatedAt
        ];
      }

      return $records;
    }
  ]
];
