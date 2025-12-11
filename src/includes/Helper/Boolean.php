<?php

namespace WingletDB\Helper;

class Boolean {
  public static function normalize($v, $record): string {
    return boolval($v);
  }
}
