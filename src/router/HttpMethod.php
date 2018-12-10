<?hh // strict
/*
 *  Copyright (c) 2015-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\HackRouter;

enum HttpMethod: string {
  HEAD = 'HEAD';
  GET = 'GET';
  POST = 'POST';
  PUT = 'PUT';
  PATCH = 'PATCH';
  DELETE = 'DELETE';
  OPTIONS = 'OPTIONS';
  PURGE = 'PURGE';
  TRACE = 'TRACE';
  CONNECT = 'CONNECT';
  REPORT = 'REPORT';
  LOCK = 'LOCK';
  UNLOCK = 'UNLOCK';
  COPY = 'COPY';
  MOVE = 'MOVE';
  MERGE = 'MERGE';
  NOTIFY = 'NOTIFY';
  SUBSCRIBE = 'SUBSCRIBE';
  UNSUBSCRIBE = 'UNSUBSCRIBE';
}
