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
namespace Drupal\apigee_drupal8_graphql\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.constants');
module_load_include('inc', 'apigee_drupal8_graphql', 'src/apigee_drupal8_graphql.functions');

class AdminForm extends ConfigFormBase {


  public function getFormId() {
    return 'apigee_drupal8_graphql_admin_settings';
  }

  protected function getEditableConfigNames() {
    return [
      MODULE_SETTINGS_VAR
    ];
  }

  public static function get(&$var, $default=null) {
    return isset($var) ? $var : $default;
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = get_module_settings()->get(MODULE_CONFIG_ROOT);

    $form['playground'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('GraphQL Playground')
    ];


    $form['playground'][PLAYGROUND_PATH_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#description' => $this->t('Path within Drupal where the GraphQL playground is accessible. (Note that changing the path, will clear the Drupal cache)'),
      '#default_value' => $this->get($config[PLAYGROUND_PATH_VAR], PLAYGROUND_PATH_DEFAULT),
    ];

    $form['playground'][PLAYGROUND_ENDPOINT_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('GraphQL Endpoint'),
      '#description' => $this->t('URL that will show up in the endpoint field of the GraphQL Playground.'),
      '#default_value' => $this->get($config[PLAYGROUND_ENDPOINT_VAR], PLAYGROUND_ENDPOINT_DEFAULT),
    ];

    $form['playground'][PLAYGROUND_ACCESS_VAR] = [
      '#type' => 'radios',
      '#title' => $this->t('Access'),
      '#description' => $this->t('Which users can view GraphQL Playground page'),
      '#options' => ['everyone' => $this->t('Everyone'), 'logged_in' => $this->t('Logged-in Users')],
      '#default_value' => $this->get($config[PLAYGROUND_ACCESS_VAR], 'everyone'),
    ];

    $form['playground'][PLAYGROUND_THEME_VAR] = [
      '#type' => 'radios',
      '#title' => $this->t('Color Theme'),
      '#description' => $this->t('Color theme to be used with the GraphQL Playground'),
      '#options' => ['light' => $this->t('Light'), 'dark' => $this->t('Dark')],
      '#default_value' => $this->get($config[PLAYGROUND_THEME_VAR], 'light'),
    ];


    $form['playground'][PLAYGROUND_MENU_LINK_ENABLED_VAR] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show GraphQL Playground link in the main menu'),
      '#default_value' => $this->get($config[PLAYGROUND_MENU_LINK_ENABLED_VAR], 1),
    ];

    $form['playground'][PLAYGROUND_MENU_LINK_TITLE_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Menu menu link text'),
      '#description' => $this->t('Text to display for link in the main menu'),
      '#default_value' => $this->get($config[PLAYGROUND_MENU_LINK_TITLE_VAR], 'GraphQL'),
    ];


    $form['security'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('OAuth Settings.')
    ];

    $form['security'][OAUTH_CLIENT_ID_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#description' => $this->t('Client ID.'),
      '#default_value' => $this->get($config[OAUTH_CLIENT_ID_VAR], ''),
    ];

    $form['security'][OAUTH_CLIENT_SECRET_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client Secret'),
      '#description' => $this->t('Client Secret.'),
      '#default_value' => $this->get($config[OAUTH_CLIENT_SECRET_VAR], ''),
    ];

    $form['security'][OAUTH_AUTH_URI_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Authorization URI'),
      '#description' => $this->t('OAuth Authorization URI.'),
      '#default_value' => $this->get($config[OAUTH_AUTH_URI_VAR], ''),
    ];

    $form['security'][OAUTH_TOKEN_URI_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token URI'),
      '#description' => $this->t('OAuth Token URI.'),
      '#default_value' => $this->get($config[OAUTH_TOKEN_URI_VAR], ''),
    ];

    $form['security'][OAUTH_REDIRECT_URI_VAR] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect URI'),
      '#description' => $this->t('OAuth Redirect URI.'),
      '#default_value' => $this->get($config[OAUTH_REDIRECT_URI_VAR], ''),
    ];

    $form['security'][OAUTH_SCOPES_VAR] = [
      '#type' => 'textfield',
      '#title' => t('Oauth Scopes'),
      '#description' => t('Oauth service provider scopes. Space separated list.'),
      '#default_value' => $this->get($config[OAUTH_SCOPES_VAR], ''),
    ];

    $form['security'][OAUTH_GRANT_TYPES_VAR] = [
      '#type' => 'checkboxes',
      '#title' => t('Grant Types'),
      '#description' => t('OAuth Grant Types.'),
      '#options' => [ OAUTH_GRANT_TYPE_CLIENT_CREDENTIALS => 'Client Credentials',
        OAUTH_GRANT_TYPE_AUTHORIZATION_CODE => 'Authorization' ],
      '#default_value' => $this->get($config[OAUTH_GRANT_TYPES_VAR], array(OAUTH_GRANT_TYPE_AUTHORIZATION_CODE)),
    ];


    return parent::buildForm($form, $form_state);
  }


  public function submitForm(array &$form, FormStateInterface $form_state) {

    $configs = save_config_values($form_state->getValues());
    $this->updateMenu($configs);


    parent::submitForm($form, $form_state);
  }


  protected function updateMenu($configs) {
    if (!$configs['new'][PLAYGROUND_MENU_LINK_ENABLED_VAR]) {
      delete_main_menu();
      return;
    }

    enable_or_update_main_menu($configs);
  }

}

