```shellscript
$ composer require wingletdb/wingletdb .
```

```php
<?php

require_once __DIR__ . "/vendor/autoload.php"

$winglet = new WingletDB\Manager([
  "schemaDir" => __DIR__ . "/vendor/wingletdb/wingletdb-php/sample-schema",
  "databaseDir" => __DIR__ . "/db"
]);

$userDB = $winglet->getDB("user");
// $blogDB = $winglet->getDB("blog");
// $termDB = $winglet->getDB("term");
```

# add record

```php
$user = $userDB->create();
$user->set("name", "admin");
$user->set("email", "admin@example.com");

$userDB->updater->saveRecord($user);
```

# delete record

```php
$userDB->updater->deleteRecord(1);
```

# get

```php
$user = $userDB->get(1);
echo $user->email;
```

# getBy

```php
$user = $userDB->getBy("email", "info@example.com");
echo $user->name;
```

# find

```php
// 連想配列（ID => Record）で取得
$users = $userDB->find();

// filter
$users = $userDB->find(fn($user) => $user->status === "active");
```

# findOrdered

```php
// インデックス配列（0 => Record, 1 => Record, ...）で取得（順序保証）
$users = $userDB->findOrdered();

// filter
$users = $userDB->findOrdered(fn($user) => $user->status === "active");
```

# list

```php
// リストから検索
$users = $userDB->list("summary");
$users = $userDB->list("summary", fn($user) => $user->status === "active");
```

# view

```php
// ビューを取得
$view = $userDB->view("count");
```

# order records

```php
// レコードを指定位置に移動
$userDB->updater->moveRecord($id, 2);

// 2つのレコードの位置を入れ替え
$userDB->updater->swapRecords($id1, $id2);

// レコードを完全に並び替え
$userDB->updater->reorderRecords([3, 1, 2]);
```
