<?php
// Copyright 2019 Google LLC
//
// Licensed under the Apache License, Version 2.0 (the "License");
// you may not use this file except in compliance with the License.
// You may obtain a copy of the License at
//
//      http://www.apache.org/licenses/LICENSE-2.0
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.

namespace Drupal\apigee_drupal8_graphql\Routing;

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.constants');
module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.functions');

class DynamicRouter {


  public function routes() {
    $path = cfg_get(PLAYGROUND_PATH_VAR);
    $route_collection = new RouteCollection();

    $route = new Route(
      '/'.$path,
      [
        '_controller' => '\Drupal\\'.MODULE_NAME.'\Controller\PlaygroundController::content',
        '_title' => 'GraphQL Playground'
      ],
      [
        //'_permission'  => 'access content',
        '_custom_access' => '\Drupal\\'.MODULE_NAME.'\Controller\PlaygroundController::access',
      ]
    );

    $route_collection->add(MODULE_NAME.'.content', $route);
    return $route_collection;
  }

}