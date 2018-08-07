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

use namespace HH\Lib\{C, Vec};

abstract final class Parser {
  public static function parse(string $pattern): PatternNode {
    $tokens = tokenize($pattern);

    list($node, $tokens) = self::parseImpl($tokens);
    invariant(
      C\is_empty($tokens),
      'Tokens remaining at end of expression: %s',
      \var_export($tokens, true),
    );
    return $node;
  }

  private static function parseImpl(
    vec<Token> $tokens,
  ): (PatternNode, vec<Token>) {
    $nodes = vec[];

    while (!C\is_empty($tokens)) {
      list($type, $text) = C\firstx($tokens);
      $tokens = Vec\drop($tokens, 1);

      if ($type === TokenType::OPEN_BRACE) {
        list($node, $tokens) = self::parseParameter($tokens);
        $nodes[] = $node;
        list($type, $_) = C\firstx($tokens);
        invariant(
          $type === TokenType::CLOSE_BRACE,
          'Got %s without %s',
          TokenType::OPEN_BRACE,
          TokenType::CLOSE_BRACE,
        );
        $tokens = Vec\drop($tokens, 1);
        continue;
      }

      if ($type === TokenType::OPEN_BRACKET) {
        list($node, $tokens) = self::parseImpl($tokens);
        $nodes[] = new OptionalNode($node);
        list($type, $_) = C\firstx($tokens);
        invariant(
          $type === TokenType::CLOSE_BRACKET,
          'Got %s without %s',
          TokenType::OPEN_BRACKET,
          TokenType::CLOSE_BRACKET,
        );
        $tokens = Vec\drop($tokens, 1);
        continue;
      }

      if ($type === TokenType::CLOSE_BRACKET) {
        $tokens = Vec\concat(vec[tuple($type, $text)], $tokens);
        return tuple(new PatternNode($nodes), $tokens);
      }

      invariant(
        $type === TokenType::STRING,
        'Unexpected token type: %s',
        $type,
      );
      $nodes[] = new LiteralNode($text);
    }

    return tuple(new PatternNode($nodes), $tokens);
  }

  private static function parseParameter(
    vec<Token> $tokens,
  ): (ParameterNode, vec<Token>) {
    list($type, $text) = C\firstx($tokens);
    invariant(
      $type === TokenType::STRING,
      'Expected parameter to start with a name, got "%s" (%s)',
      $text,
      $type,
    );
    $name = $text;
    $tokens = Vec\drop($tokens, 1);

    list($type, $text) = C\firstx($tokens);
    if ($type === TokenType::CLOSE_BRACE) {
      return tuple(new ParameterNode($name, null), $tokens);
    }

    invariant(
      $type === TokenType::COLON,
      'Expected parameter name "%s" to be followed by "%s" or "%s", got "%s"',
      $name,
      TokenType::CLOSE_BRACE,
      TokenType::COLON,
      $text,
    );
    $tokens = Vec\drop($tokens, 1);
    $regexp = '';
    $depth = 0;
    while (!C\is_empty($tokens)) {
      list($type, $text) = C\firstx($tokens);
      if ($type === TokenType::OPEN_BRACE) {
        ++$depth;
      } else if ($type === TokenType::CLOSE_BRACE) {
        if ($depth === 0) {
          break;
        }
        --$depth;
      }
      $tokens = Vec\drop($tokens, 1);
      $regexp .= $text;
    }
    invariant(
      $depth === 0,
      '%s without matching %s in regexp',
      TokenType::OPEN_BRACE,
      TokenType::CLOSE_BRACE,
    );

    return tuple(new ParameterNode($name, $regexp), $tokens);
  }
}
