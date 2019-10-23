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

use Drupal\menu_link_content\Entity\MenuLinkContent;


function info($value) {
  \Drupal::logger(MODULE_NAME)->info(var_export($value, true));
}

function notice($value) {
  \Drupal::logger(MODULE_NAME)->notice(var_export($value, true));
}

function get_module_settings() {
  return \Drupal::service('config.factory')->getEditable(MODULE_SETTINGS_VAR);
}

function cfg_set($var, $val) {
  $settings = get_module_settings();
  $config = $settings->get(MODULE_CONFIG_ROOT);
  $config[$var] = $val;
  $settings->set(MODULE_CONFIG_ROOT, $config)->save();
  return $val;
}

function cfg_get($var) {
  $settings = get_module_settings();
  $config = $settings->get(MODULE_CONFIG_ROOT);
  $val = isset($config[$var])?$config[$var]:null;
  return $val;
}

function cfg_del($var) {
  $settings = get_module_settings();
  $config = $settings->get(MODULE_CONFIG_ROOT);
  $val = isset($config[$var])?$config[$var]:null;

  $settings->set(MODULE_CONFIG_ROOT, $config)->save();
  return $val;
}

function set_default_values() {
  $config = array(
    PLAYGROUND_MENU_LINK_TITLE_VAR => 'GraphQL',
    PLAYGROUND_MENU_LINK_ENABLED_VAR => 0,
    PLAYGROUND_ACCESS_VAR => PLAYGROUND_ACCESS_EVERYONE,
    PLAYGROUND_ENDPOINT_VAR => PLAYGROUND_ENDPOINT_DEFAULT,
    PLAYGROUND_THEME_VAR => PLAYGRUND_THEME_LIGHT,
    PLAYGROUND_PATH_VAR => PLAYGROUND_PATH_DEFAULT,
    PLAYGROUND_MENU_ID_VAR => "",
    OAUTH_CLIENT_ID_VAR => 'your_application_client_id',
    OAUTH_CLIENT_SECRET_VAR => 'your_application_client_secret',
    OAUTH_AUTH_URI_VAR => 'https://example.com/oauth2/authorize',
    OAUTH_TOKEN_URI_VAR => 'https://example.com/oauth2/token',
    OAUTH_REDIRECT_URI_VAR => 'http://yoursite.devportal.apigee.io/playground',
    OAUTH_SCOPES_VAR => 'scope1 scope2 etc',
    OAUTH_GRANT_TYPES_VAR => array(
      OAUTH_GRANT_TYPE_CLIENT_CREDENTIALS => 0,
      OAUTH_GRANT_TYPE_AUTHORIZATION_CODE => 0
    )

  );

  get_module_settings()->set(MODULE_CONFIG_ROOT, $config)->save();
}

function save_config_values($values) {
  $settings = get_module_settings();

  $old_config = $settings->get(MODULE_CONFIG_ROOT);

  $new_config = array(
      PLAYGROUND_MENU_LINK_TITLE_VAR => $values[PLAYGROUND_MENU_LINK_TITLE_VAR],
      PLAYGROUND_MENU_LINK_ENABLED_VAR => $values[PLAYGROUND_MENU_LINK_ENABLED_VAR],
      PLAYGROUND_ACCESS_VAR => $values[PLAYGROUND_ACCESS_VAR],
      PLAYGROUND_ENDPOINT_VAR => $values[PLAYGROUND_ENDPOINT_VAR],
      PLAYGROUND_THEME_VAR => $values[PLAYGROUND_THEME_VAR],
      PLAYGROUND_PATH_VAR => $values[PLAYGROUND_PATH_VAR],

      OAUTH_CLIENT_ID_VAR => $values[OAUTH_CLIENT_ID_VAR],
      OAUTH_CLIENT_SECRET_VAR => $values[OAUTH_CLIENT_SECRET_VAR],
      OAUTH_AUTH_URI_VAR => $values[OAUTH_AUTH_URI_VAR],
      OAUTH_TOKEN_URI_VAR => $values[OAUTH_TOKEN_URI_VAR],
      OAUTH_REDIRECT_URI_VAR => $values[OAUTH_REDIRECT_URI_VAR],
      OAUTH_SCOPES_VAR => $values[OAUTH_SCOPES_VAR],
      OAUTH_GRANT_TYPES_VAR => $values[OAUTH_GRANT_TYPES_VAR]
    );

  $merged_config = array_merge($old_config, $new_config);

  $settings->set(MODULE_CONFIG_ROOT, $merged_config)->save();
  return array( 'new' => $merged_config, 'old' => $old_config);
}

function get_main_menu_id() {
  return cfg_get(PLAYGROUND_MENU_ID_VAR);
}

function set_main_menu_id($id) {
  cfg_set(PLAYGROUND_MENU_ID_VAR, $id);
}

function clear_main_menu_id() {
  cfg_del(PLAYGROUND_MENU_ID_VAR);
}

function delete_main_menu() {
  $id = get_main_menu_id();

  if (!$id) {
    return;
  }

  //menu_link_delete($id);
  clear_main_menu_id();
}

function enable_or_update_main_menu($configs) {
  $new_title = $configs['new'][PLAYGROUND_MENU_LINK_TITLE_VAR];
  $new_path = $configs['new'][PLAYGROUND_PATH_VAR];

  $old_title = $configs['old'][PLAYGROUND_MENU_LINK_TITLE_VAR];
  $old_path = $configs['old'][PLAYGROUND_PATH_VAR];

  $uuid = get_main_menu_id() ?: "";

  $uuid = menu_link_save($uuid, $new_title, $new_path);

  set_main_menu_id($uuid);

  if ($new_path != $old_path) {
    //We need to clear the cache so that the menu is rebuilt
    //cache_clear_all('*', 'cache_page', TRUE);
    Drupal::cache('menu')->invalidateAll();
    drupal_flush_all_caches();
    drupal_set_message('Caches cleared');
  }
}


function menu_link_save($uuid, $new_title, $new_path) {
  $menu_links = \Drupal::entityTypeManager()->getStorage('menu_link_content')->loadByProperties(['uuid' => $uuid]);
  $menu_link = reset($menu_links);

  if ($menu_link) {
    $menu_link->title = $new_title;
    $menu_link->link = ['uri' => "internal:/" . $new_path];
  }
  else {
    $menu_link = MenuLinkContent::create([
      'title' => $new_title,
      'link' => ['uri' => "internal:/" . $new_path],
      'menu_name' => 'main',
      'expanded' => TRUE
    ]);
  }

  $menu_link->save();
  return $menu_link->uuid();

}

function is_main_menu_enabled() {
  return !!get_main_menu_id();
}

function dis_module() {
  delete_main_menu();
}

function en_module() {
  set_default_values();
}

function graphql_playground_access() {
  if (\Drupal::state()->get(PLAYGROUND_ACCESS_VAR, PLAYGROUND_ACCESS_EVERYONE) == PLAYGROUND_ACCESS_LOGGED_IN) {
    return user_is_logged_in();
  }

  return TRUE;
}