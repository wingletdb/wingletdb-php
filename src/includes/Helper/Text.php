<?php

namespace WingletDB\Helper;

class Text {
  public static function normalize($v, $record): string {
    return $v ?? "";
  }
}
