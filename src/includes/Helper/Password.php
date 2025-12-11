<?php

namespace WingletDB\Helper;

class Password {
  public static function hash($password): string {
    return "hashed:bcrypt:" . password_hash($password, PASSWORD_BCRYPT);
  }

  public static function verify($password, $hash): bool {
    $hash = preg_replace("/^hashed:bcrypt:(.+)$/", '$1', $hash);
    return password_verify($password, $hash);
  }

  public static function normalize($v, $record): string {
    if(!$v) return "";
    if(str_starts_with($v, "hashed:")) return $v;
    return self::hash($v);
  }
}
