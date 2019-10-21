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

//Menu constants

const MODULE_NAME = "apigee_drupal8_graphql";
const MODULE_SETTINGS_VAR = MODULE_NAME . ".settings";
const MODULE_CONFIG_ROOT = "config";

//Playground constants
const PLAYGROUND_ENDPOINT_VAR = 'endpoint';
const PLAYGROUND_THEME_VAR = 'theme';
const PLAYGROUND_PATH_VAR = 'page_path';
const PLAYGROUND_ACCESS_VAR = 'page_access';
const PLAYGROUND_MENU_ID_VAR = 'main_menu_id';
const PLAYGROUND_MENU_LINK_TITLE_VAR = 'menu_title';
const PLAYGROUND_MENU_LINK_ENABLED_VAR = 'menu_enabled';
const PLAYGROUND_TOKEN_VAR = 'token';


const PLAYGROUND_ACCESS_EVERYONE = 'everyone';
const PLAYGROUND_ACCESS_LOGGED_IN = 'logged_in';
const PLAYGROUND_PATH_DEFAULT = 'playground';
const PLAYGROUND_THEME_DARK = 'dark';
const PLAYGRUND_THEME_LIGHT = 'light';
const PLAYGROUND_ENDPOINT_DEFAULT = 'https://api.graph.cool/simple/v1/swapi/graphql';


//OAuth constants
const OAUTH_CLIENT_ID_VAR = 'app_id';
const OAUTH_CLIENT_SECRET_VAR = 'app_secret';
const OAUTH_AUTH_URI_VAR = 'oauth_authorization_uri';
const OAUTH_TOKEN_URI_VAR = 'oauth_token_uri';
const OAUTH_REDIRECT_URI_VAR = 'oauth_redirect_uri';
const OAUTH_SCOPES_VAR = 'oauth_scopes';
const OAUTH_GRANT_TYPES_VAR = 'oauth_grant_types';
const OAUTH_STATE_VAR = 'oauth_state';

const OAUTH_GRANT_TYPE_CLIENT_CREDENTIALS = 'client_credentials';
const OAUTH_GRANT_TYPE_AUTHORIZATION_CODE = 'authorization_code';


