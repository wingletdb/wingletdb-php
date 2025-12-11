<?php

use WingletDB\Helper\DateTime;
use WingletDB\Helper\Password;
use WingletDB\Helper\Text;

return [
  "id" => "auto",
  "fields" => [
    "name" => Text::normalize(...),
    "email" => Text::normalize(...),
    "password" => Password::normalize(...),
    "createdAt" => DateTime::normalizeCreatedAt(...),
    "updatedAt" => DateTime::normalize(...)
  ],
  "lists" => [
    "summary" => [
      "fields" => ["name", "email"],
      "filter" => fn($record) => $record->email !== "",
      "sort" => ["updatedAt" => "desc"]
    ]
  ],
  "views" => [
    "count" => fn($db) => ["count" => count($db->findFull())]
  ]
];
