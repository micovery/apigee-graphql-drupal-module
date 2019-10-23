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

namespace Drupal\apigee_drupal8_graphql\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\Component\Utility\UrlHelper;
use \Drupal\Component\Utility\Crypt;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.constants');
module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.functions');


class PlaygroundController extends ControllerBase {


  public static function get(&$var, $default=null) {
    return isset($var) ? $var : $default;
  }

  protected function setTokenCookie() {
    $cookie_name = 'playground-token';
    $token = $_COOKIE[$cookie_name];

    if ($token) {
      //FIXME: need to verify the token is valid before returning it
      return;
    }

    $config = get_module_settings()->get(MODULE_CONFIG_ROOT);

    $oauth_token_uri = $this->get($config[OAUTH_TOKEN_URI_VAR]);

    if (!isset($oauth_token_uri)) {
      //no need to try to get a token, since user has not set a OAuth token endpoint
      return;
    }

    $client_id = $this->get($config[OAUTH_CLIENT_ID_VAR]);
    $client_secret = $this->get($config[OAUTH_CLIENT_SECRET_VAR]);

    if (!(isset($client_id) && isset($client_secret))) {
      //no need to try to get token, since we don't have credentials
      return;
    }


    try {
      $client = \Drupal::httpClient();
      $post_response = $request = $client->post($oauth_token_uri, [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'form_params' => [
          'grant_type' => 'client_credentials',
          'client_id' => $this->get($config[OAUTH_CLIENT_ID_VAR]),
          'client_secret' => $this->get($config[OAUTH_CLIENT_SECRET_VAR]),
          'scope' => $this->get($config[OAUTH_SCOPES_VAR])
        ]
      ]);

      $code = $post_response->getStatusCode();
      $data = $post_response->getBody();


      if ($code != 200) {
        $error_response = [
          'message' => 'Unexpected status code while getting playground token',
          'response' => [
            'status' => $code,
            'data' => $data,
          ]
        ];

        watchdog(MODULE_NAME, json_encode($error_response));
        return false;
      }

      $token_info = json_decode($data);
      setcookie($cookie_name, $token_info->access_token, time() + 900, NULL, NULL, FALSE, FALSE);
      return true;

    }
    catch (\Exception $e) {
      watchdog_exception(MODULE_NAME, $e, "Could not get OAuth access token for GraphQL Playground");
    }

  }

  protected function exchangeCodeForAccessToken($code, $state) {

    $config = get_module_settings()->get(MODULE_CONFIG_ROOT);

    $oauth_token_uri = $this->get($config[OAUTH_TOKEN_URI_VAR]);

    try {
      $client = \Drupal::httpClient();
      $post_response = $request = $client->post($oauth_token_uri, [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'form_params' => [
          'grant_type' => 'authorization_code',
          'state' => $state,
          'code' => $code,
          'client_id' => $this->get($config[OAUTH_CLIENT_ID_VAR]),
          'client_secret' => $this->get($config[OAUTH_CLIENT_SECRET_VAR]),
          'redirect_uri' => $this->get($config[OAUTH_REDIRECT_URI_VAR]),
        ]
      ]);

      $code = $post_response->getStatusCode();
      $data = $post_response->getBody();

      if ($code != 200 ) {
        return [
          token_response => null,
          error_response => [
            'message' => 'Unexpected status code while exchanging OAuth authentication code for access token',
            'response' => [
              'status' => $code,
              'data' => $data,
            ]
          ]
        ];
      }

      return [
        token_response => json_decode($data),
        error_response => null
      ];

    } catch (\Exception $e) {
      return [
        token_response => null,
        error_response => [
          'message' => 'Unexpected exception while exchanging OAuth authorization code for access token',
          'response' => [
            'status' => 500,
            'data' => null,
          ]
        ]
      ];

    }
  }

  protected function loadPlayground() {
    $params = \Drupal::request()->query->all();

    $this->setTokenCookie();

    $oauth_error_response = null;
    $oauth_token_response = null;

    // If state and code exist in url call OAuth function.
    if ($params['code']) {
      if ($this->isValidOAuthState($params['state'])) {
        $this->clearOAuthState();

        $result = $this->exchangeCodeForAccessToken($params['code'], $params['state']);

        if ($result['token_response']) {
          $oauth_token_response = $result['token_response'];
        }

        if ($result['error_response']) {
          $oauth_error_response = $result['error_response'];
        }

      } else {
        //state parameter is not valid
        $oauth_error_response = [
          'message' => 'The OAuth state parameter does not match the session state.',
        ];
      }
    }

    $config = get_module_settings()->get(MODULE_CONFIG_ROOT);

    $output = array(
      'react_app_container' => array(
        '#type' => 'markup',
        '#markup' =>  '<div class="gql-playground" ></div>',
        '#attached' => [
          'library' => [
            MODULE_NAME.'/graphql-playground',
            MODULE_NAME.'/graphql-playground-css'
          ],
          'drupalSettings' => [
            'graphql_playground' => [
              'container_class' => 'gql-playground',
              'endpoint' => $this->get($config[PLAYGROUND_ENDPOINT_VAR], ''),
              'oauth_authorization_uri' => $this->get($config[OAUTH_AUTH_URI_VAR], ''),
              'oauth_token_uri' => $this->get($config[OAUTH_TOKEN_URI_VAR], ''),
              'oauth_redirect_uri' => $this->get($config[OAUTH_REDIRECT_URI_VAR], ''),
              'oauth_grant_types' => $this->get($config[OAUTH_GRANT_TYPES_VAR], ''),
              'oauth_scopes' => $this->get($config[OAUTH_SCOPES_VAR], ''),
              'oauth_client_id' => $this->get($config[OAUTH_CLIENT_ID_VAR], ''),
              'oauth_state' => $this->newOAUthState(),
              'theme' => $this->get($config[PLAYGROUND_THEME_VAR], ''),
              'oauth_token_response' => $oauth_token_response,
              'oauth_error_response' => $oauth_error_response
            ]
          ]
        ],
      ),
    );

    return $output;
  }

  public function content() {

    $build = $this->loadPlayground();
    return $build;
  }



  protected function newOAUthState() {
    $state = Crypt::randomBytesBase64(32);
    \Drupal::service('tempstore.private')->get(MODULE_NAME)->set(OAUTH_STATE_VAR, $state);
    return $state;
  }

  protected function clearOAuthState() {
    \Drupal::service('tempstore.private')->get(MODULE_NAME)->set(OAUTH_STATE_VAR, null);
    unset($_SESSION[OAUTH_STATE_VAR]);
  }

  protected function isValidOAuthState($state) {
    $saved_state = \Drupal::service('tempstore.private')->get(MODULE_NAME)->get(OAUTH_STATE_VAR);
    if (!isset($saved_state)) {
      return FALSE;
    }

    return $state == $saved_state;
  }

  public function access() {
    $config = get_module_settings()->get(MODULE_CONFIG_ROOT);
    $access_requirement = $this->get($config[PLAYGROUND_ACCESS_VAR], PLAYGROUND_ACCESS_EVERYONE);

    if ($access_requirement == PLAYGROUND_ACCESS_EVERYONE) {
      return AccessResult::allowed();
    }

    if ($access_requirement == PLAYGROUND_ACCESS_LOGGED_IN && \Drupal::currentUser()->isAuthenticated()) {
      return AccessResult::allowed();
    }

    return AccessResult::forbidden();
  }



}