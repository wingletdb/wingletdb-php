```php
<?php

require_once __DIR__ . "/vendor/autoload.php"

$winglet = new WingletDB\Manager([
  "schemaDir" => __DIR__ . "/vendor/wingletdb/wingletdb-php/sample-schema",
  "databaseDir" => __DIR__ . "/db"
]);

$userDB = $winglet->getDB("user");
```

# add record

```php
$user = $userDB->create([
  "name" => "admin",
  "email" => "admin@example.com",
]);

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
```

# findBy

```php
$users = $userDB->findBy("list");
```