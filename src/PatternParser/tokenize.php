<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter\PatternParser;

use namespace HH\Lib\{Str, Vec};

function tokenize(string $pattern): vec<Token> {
  $tokens = vec[];
  $buffer = '';
  foreach (Str\split($pattern, '') as $byte) {
    if (TokenType::isValid($byte)) {
      $tokens[] = tuple(TokenType::STRING, $buffer);
      $buffer = '';
      $tokens[] = tuple(TokenType::assert($byte), $byte);
    } else {
      $buffer .= $byte;
    }
  }
  if ($buffer !== '') {
    $tokens[] = tuple(TokenType::STRING, $buffer);
  }
  return Vec\filter($tokens, $t ==> $t !== tuple(TokenType::STRING, ''));
}
