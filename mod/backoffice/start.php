<?php
/**
 * Plugin for creating Back Office for members
 *
 * @package Solidaria Back Office
 */

elgg_register_event_handler('init', 'system', 'backoffice_init');

// Metadata on users needs to be independent
// outside of init so it happens earlier in boot. See #3316
register_metadata_as_independent('user');



/**
 * Back Office init function
 */
function backoffice_init() {

	// actions
	$action_path = "$root/actions/backoffice";
	elgg_register_action('backoffice/getgen', "$action_path/getgen.php");
	// elgg_register_action("backoffice/getgen", elgg_get_plugins_path() . "backoffice/actions/backoffice/getgen.php");

	// add a site navigation item - see backoffice_owner_block_menu()
	// $item = new ElggMenuItem('backoffice', elgg_echo('backoffice'), 'backoffice/all');
	// elgg_register_menu_item('site', $item);

	// menus
	/* elgg_register_menu_item('site', array(
		'name' => 'backoffice',
		'text' => elgg_echo('backoffice'),
		'href' => 'backoffice/all'
	)) */;

	// elgg_register_plugin_hook_handler('register', 'menu:page', 'backoffice_page_menu');

	// elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'backoffice_owner_block_menu');
	if (elgg_is_logged_in()) elgg_register_plugin_hook_handler('register', 'menu:owner_block', 'backoffice_owner_block_menu');

	// Register a URL handler for users
	elgg_register_plugin_hook_handler('entity:url', 'user', 'backoffice_set_url', 499); // 499 - priority

	elgg_register_plugin_hook_handler('entity:icon:url', 'user', 'backoffice_set_icon_url');
	elgg_unregister_plugin_hook_handler('entity:icon:url', 'user', 'user_avatar_hook');


	elgg_register_simplecache_view('icon/user/default/tiny');
	elgg_register_simplecache_view('icon/user/default/topbar');
	elgg_register_simplecache_view('icon/user/default/small');
	elgg_register_simplecache_view('icon/user/default/medium');
	elgg_register_simplecache_view('icon/user/default/large');
	elgg_register_simplecache_view('icon/user/default/master');

	elgg_register_page_handler('backoffice', 'backoffice_page_handler');

	elgg_extend_view('css/elgg', 'backoffice/css'); // add to the main css
	elgg_extend_view('js/elgg', 'backoffice/js');

	// allow ECML in parts of the Back Office
	elgg_register_plugin_hook_handler('get_views', 'ecml', 'backoffice_ecml_views_hook');

	// allow admins to set default widgets for users on Back Offices
	elgg_register_plugin_hook_handler('get_list', 'default_widgets', 'backoffice_default_widgets_hook');
	
	elgg_register_event_handler('pagesetup', 'system', 'backoffice_pagesetup', 50);
}

/**
 * Back Office page handler
 *
 * @param array $page Array of URL segments passed by the page handling mechanism
 * @return bool
 */
function backoffice_page_handler($page) {

	if (isset($page[0])) {
		$username = $page[0];
		$user = get_user_by_username($username);
		elgg_set_page_owner_guid($user->guid);
	} elseif (elgg_is_logged_in()) {
		forward(elgg_get_logged_in_user_entity()->getURL());
	}

	// short circuit if invalid or banned username
	if (!$user || ($user->isBanned() && !elgg_is_admin_logged_in())) {
		register_error(elgg_echo('backoffice:notfound'));
		forward();
	}

	$action = NULL;
	if (isset($page[1])) {
		$action = $page[1];
	}

	if ($action == 'getgen') {
		// use the custom backoffice getgen page
		/* 
		$base_dir = elgg_get_root_path();
		require "{$base_dir}pages/backoffice/getgen.php"; 
		 */
		include elgg_get_plugins_path() . 'backoffice/pages/backoffice/getgen.php';
		return true;
	}

	$content = elgg_view('backoffice/layout', array('entity' => $user));
	$body = elgg_view_layout('one_column', array(
		'content' => $content
	));
	echo elgg_view_page($user->name, $body);
	return true;
}

/**
 * Back Office URL generator for $user->getUrl();
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string
 */
function backoffice_set_url($hook, $type, $url, $params) {
	$user = $params['entity'];
	return "backoffice/" . $user->username;
}

/**
 * Use a URL for avatars that avoids loading Elgg engine for better performance
 *
 * @param string $hook
 * @param string $type
 * @param string $url
 * @param array  $params
 * @return string
 */
function backoffice_set_icon_url($hook, $type, $url, $params) {

	// if someone already set this, quit
	if ($url) {
		return;
	}

	$user = $params['entity'];
	$size = $params['size'];

	$user_guid = $user->getGUID();
	$icon_time = $user->icontime;

	if (!$icon_time) {
		return "_graphics/icons/user/default{$size}.gif";
	}

	$filehandler = new ElggFile();
	$filehandler->owner_guid = $user_guid;
	$filehandler->setFilename("backoffice/{$user_guid}{$size}.jpg");

	try {
		if ($filehandler->exists()) {
			$join_date = $user->getTimeCreated();
			return "mod/backoffice/icondirect.php?lastcache=$icon_time&joindate=$join_date&guid=$user_guid&size=$size";
		}
	} catch (InvalidParameterException $e) {
		elgg_log("Unable to get Back Office icon for user with GUID $user_guid", 'ERROR');
		return "_graphics/icons/default/$size.png";
	}
}

/**
 * Parse ECML on parts of the Back Office
 *
 * @param string $hook
 * @param string $entity_type
 * @param array  $return_value
 * @return array
 */
function backoffice_ecml_views_hook($hook, $entity_type, $return_value) {
	$return_value['backoffice/backoffice_content'] = elgg_echo('backoffice');

	return $return_value;
}

/**
 * Register Back Office widgets with default widgets
 *
 * @param string $hook
 * @param string $type
 * @param array  $return
 * @return array
 */
function backoffice_default_widgets_hook($hook, $type, $return) {
	$return[] = array(
		'name' => elgg_echo('backoffice'),
		'widget_context' => 'backoffice',
		'widget_columns' => 3,

		'event' => 'create',
		'entity_type' => 'user',
		'entity_subtype' => ELGG_ENTITIES_ANY_VALUE,
	);

	return $return;
}

/**
 * Sets up user-related menu items
 *
 * @return void
 * @access private
 */
function backoffice_pagesetup() {
	$viewer = elgg_get_logged_in_user_entity();
	if (!$viewer) {
		 return;
	}
	/* 
	elgg_register_menu_item('topbar', array(
		'name' => 'backoffice',
		'href' => $viewer->getURL(),
		'text' => elgg_view('output/img', array(
			'src' => $viewer->getIconURL('topbar'),
			'alt' => $viewer->name,
			'title' => elgg_echo('backoffice'),
			'class' => 'elgg-border-plain elgg-transition',
		)),
		'priority' => 100,
		'link_class' => 'elgg-topbar-avatar',
		'item_class' => 'elgg-avatar elgg-avatar-topbar',
	)); */
}

/**
 * Add a menu item to an ownerblock
 * 
 * @param string $hook
 * @param string $type
 * @param array  $return
 * @param array  $params
 */
function backoffice_owner_block_menu($hook, $type, $return, $params) {
	if (elgg_instanceof($params['entity'], 'user')) {
		// $url = "backoffice/owner/{$params['entity']->username}";
		$url = "backoffice/{$params['entity']->username}";
		$item = new ElggMenuItem('backoffice', elgg_echo('backoffice'), $url);
		$return[] = $item;
	} else { /*
		if ($params['entity']->backoffice_enable != 'no') {
			$url = "backoffice/group/{$params['entity']->guid}/all";
			$item = new ElggMenuItem('backoffice', elgg_echo('backoffice:group'), $url);
			$return[] = $item;
		*/
	}

	return $return;
}
