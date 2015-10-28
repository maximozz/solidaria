<?php
/**
 * Elgg Back Office plugin get generator(s) page
 *
 * @package Solidaria Back Office
 */

$page_owner = elgg_get_page_owner_entity();
if (!$page_owner) {
	forward('', '404');
}
/*
elgg_push_breadcrumb($page_owner->name);

elgg_register_title_button();

$content .= elgg_list_entities(array(
	'type' => 'object',
	'subtype' => 'backoffice',
	'container_guid' => $page_owner->guid,
	'full_view' => false,
	'view_toggle_type' => false,
	'no_results' => elgg_echo('backoffice:none'),
	'preload_owners' => true,
	'distinct' => false,
));

$title = elgg_echo('backoffice:owner', array($page_owner->name));

$filter_context = '';
if ($page_owner->getGUID() == elgg_get_logged_in_user_guid()) {
	$filter_context = 'mine';
}

$vars = array(
	'filter_context' => $filter_context,
	'content' => $content,
	'title' => $title,
	'sidebar' => elgg_view('backoffice/sidebar'),
);

// don't show filter if out of filter context
if ($page_owner instanceof ElggGroup) {
	$vars['filter'] = false;
}

$body = elgg_view_layout('content', $vars);

echo elgg_view_page($title, $body);
*/
// make sure only logged in users can see this page
gatekeeper();

// set the title
// for distributed plugins, be sure to use elgg_echo() for internationalization
$title = elgg_echo('backoffice:owner', array($page_owner->name));
$title .= ": " . elgg_echo('generators:get');

$link = elgg_view('output/url', array(
	'href' => "backoffice/{$user->username}",
	'text' => elgg_echo("backoffice:back"),
));

// start building the main column of the page
$content = "<div class='elgg-head clearfix'>";
$content .= elgg_view_title($title);
$content .= $link."::after</div>";

// add the form to this section
$content .= elgg_view_form("backoffice/getgen");

// optionally, add the content for the sidebar
$sidebar = elgg_view('backoffice/sidebar'); // $sidebar = "";

// layout the page
$body = elgg_view_layout('one_sidebar', array(
   'content' => $content,
   'sidebar' => $sidebar
));

// draw the page
echo elgg_view_page($title, $body);
