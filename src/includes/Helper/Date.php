<?php

namespace WingletDB\Helper;

class Date {
  public static function now(): string {
    return date("c");
  }

  public static function normalize($v, $record): string {
    return self::now();
  }

  public static function normalizeCreatedAt($v, $record): string {
    return $v ?? self::now();
  }
}