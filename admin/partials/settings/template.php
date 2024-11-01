<?php

if (!defined('ABSPATH')) {
	exit;
}
$new_settings = array(
	array(
		'id'	=> 'woom_template_wc_tab',
		'type' => 'title',
		'name' => __('Woocommerce', 'wc-messaging'),
		'desc'	=>	__('Incase not showing any templates in dropdown list please goto Tools->Update message template,', 'wc-messaging') . sprintf('<a href="%s" target="_blank">%s</a>', esc_url('https://sevengits.com/docs/wc-messaging-pro/'), __('learn more.', 'wc-messaging')),

	),
	array(
		'id' => 'woom_woocommerce',
		'type' => 'woom_config_per_status',
		'name' => __('Woocommerce', 'wc-messaging'),
		'fields' => $this->get_settings_statuses('woom_woocommerce_config_per_status', wc_get_order_statuses()),
		'desc_tip'	=> true
	),
	array(
		'id' => 'woom_nonce',
		'type' => 'woom_hidden',
		'value' => wp_create_nonce('woom-template-settings')
	),
	array(
		'id'	=> 'woom_general_settings',
		'type'	=> 'sectionend',
		'name'	=> 'end_section',
	),
);
?>