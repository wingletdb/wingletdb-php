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

# findOne

```php
$user = $userDB->findOne(1);
echo $user->email;
```

# findFull

```php
$users = $userDB->findFull();

// filter
$users = $userDB->findFull(fn($user) => $user->status === "active");
```

# findList

```php
$users = $userDB->findList("summary");
```

# getView

```php
$view = $userDB->getView("count");
```
