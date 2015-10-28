<?php
/**
 * get generator(s) form
 *
 * @package Solidaria Back Office
 */

$title = elgg_extract('title', $vars, '');
$desc = elgg_extract('description', $vars, '');
$access_id = elgg_extract('access_id', $vars, ACCESS_DEFAULT);
$container_guid = elgg_extract('container_guid', $vars);
$guid = elgg_extract('guid', $vars, null);
$shares = elgg_extract('shares', $vars, array());

?>
<div>
	<label><?php echo elgg_echo('generators:get:howmany').' '.elgg_echo('generators:get:desc'); ?></label><br />
	<?php echo elgg_view('input/select', array(
		'name' => 'gen_block',
		'id' => 'gen_block',
		'options' => array(elgg_echo('generators:get:block01'),
			elgg_echo('generators:get:block02'),
			elgg_echo('generators:get:block03'),
			elgg_echo('generators:get:block04'),
			elgg_echo('generators:get:block05'),
			elgg_echo('generators:get:block06'),
			elgg_echo('generators:get:block07'),
			elgg_echo('generators:get:block08'))
	)); ?>
</div>
<div>
	<label><?php echo elgg_echo('title'); ?></label><br />
	<?php echo elgg_view('input/text', array('name' => 'title', 'value' => $title)); ?>
</div>
<div>
	<label><?php echo elgg_echo('description'); ?></label>
	<?php echo elgg_view('input/plaintext', array('name' => 'description', 'value' => $desc)); ?>
</div>

<?php

$categories = elgg_view('input/categories', $vars);
if ($categories) {
	echo $categories;
}

?>
<div class="elgg-foot">
<?php

echo elgg_view('input/hidden', array('name' => 'container_guid', 'value' => $container_guid));

if ($guid) {
	echo elgg_view('input/hidden', array('name' => 'guid', 'value' => $guid));
}

echo elgg_view('input/submit', array('value' => elgg_echo("save")));

?>
</div>
