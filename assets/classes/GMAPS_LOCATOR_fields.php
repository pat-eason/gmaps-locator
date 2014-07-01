<?php
if(function_exists("register_field_group"))
{
	register_field_group(array (
		'id' => 'acf_location-fields',
		'title' => 'Location Fields',
		'fields' => array (
			array (
				'key' => 'field_53b2cbdecd40a',
				'label' => 'Coordinates',
				'name' => 'coordinates',
				'type' => 'coordinates-field',
				'instructions' => '<em>Put in the address, Google does the rest.</em>',
				'required' => 1,
				'center' => array (
					'lat' => 55.39979699999999951387508190236985683441162109375,
					'lng' => 10.3962009999999995812913766712881624698638916015625,
				),
				'zoom' => 12,
			),
			array (
				'key' => 'field_53b2cc09cd40b',
				'label' => 'Popup',
				'name' => 'infowindow',
				'type' => 'wysiwyg',
				'instructions' => '<em>What do you want the info window to say when a user clicks on it? Keep it short and simple!</em>',
				'required' => 1,
				'default_value' => '',
				'toolbar' => 'full',
				'media_upload' => 'yes',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'gmaps_locations',
					'order_no' => 0,
					'group_no' => 0,
				),
			),
		),
		'options' => array (
			'position' => 'acf_after_title',
			'layout' => 'default',
			'hide_on_screen' => array (
			),
		),
		'menu_order' => 0,
	));
}
?>
