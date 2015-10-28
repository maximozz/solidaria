<?php
/**
* Solidaria get generator(s) action
*
* @package Solidaria Back Office
*/

$title = htmlspecialchars(get_input('title', '', false), ENT_QUOTES, 'UTF-8');
$description = get_input('description');
$guid = get_input('guid');
$share = get_input('share');
$container_guid = get_input('container_guid', elgg_get_logged_in_user_guid());

elgg_make_sticky_form('getgen');
