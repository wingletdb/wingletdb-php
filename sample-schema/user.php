<?php

use WingletDB\Helper\Date;
use WingletDB\Helper\Password;

return [
  "id" => "auto",
  "fields" => [
    "name" => true,
    "email" => true,
    "password" => Password::normalize(...),
    "createdAt" => Date::normalizeCreatedAt(...),
    "updatedAt" => Date::normalize(...)
  ],
  "views" => [
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
