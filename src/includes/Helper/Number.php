<?php

namespace WingletDB\Helper;

class Integer {
  public static function normalizeInt($v, $record): string {
    return intval($v) ?? 0;
  }
  public static function normalizeFloat($v, $record): string {
    return intval($v) ?? 0.0;
  }
}
