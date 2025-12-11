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

# findMany

```php
$users = $userDB->findMany();

// filter
$users = $userDB->findMany(fn($user) => $user->status === "active");
```

# findList

```php
$users = $userDB->findList("summary");
```

# getView

```php
$view = $userDB->getView("count");
```
