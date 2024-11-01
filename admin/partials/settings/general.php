<?php

if (!defined('ABSPATH')) {
	exit;
}
$new_settings = array(
	array(
		'id'	=> 'woom_tab',
		'type' => 'title',
		'name' => __('WC Messaging', 'wc-messaging'),
		'desc'	=>	__('WC messaging configuration', 'wc-messaging'),
	),
	array(
		'id' => 'woom_wb_account_ID',
		'type' => 'text',
		'name' => __('Whatsapp bussiness account ID', 'wc-messaging')
	),
	array(
		'id' => 'woom_whatsapp_api',
		'type' => 'text',
		'name' => __('Whatsapp access token', 'wc-messaging'),
		'desc' => __('Access token will be get from WhatsApp API setup.', 'wc-messaging') . sprintf('<a href="%s" target="_blank">%s</a>', esc_url('https://sevengits.com/docs/wc-messaging/'), __('learn more.', 'wc-messaging')),
		'desc_tip'	=> false
	),
	array(
		'id' => 'woom_whatsapp_number',
		'type' => 'text',
		'name' => __('Whatsapp number', 'wc-messaging'),
		'desc' => __('Whatsapp number in international format without + sign.', 'wc-messaging'),
		'desc_tip'	=> false,
		'placeholder' => __('91xxxxxxxxxx', 'wc-messaging')
	),

	array(
		'id' => 'woom_whatsapp_number_id',
		'type' => 'text',
		'name' => __('Phone number ID', 'wc-messaging'),
		'desc' => __('Unique phone number id given by whatsapp.', 'wc-messaging') . sprintf('<a href="%s" target="_blank">%s</a>', esc_url('https://sevengits.com/docs/wc-messaging/'), __('learn more.', 'wc-messaging')),
		'desc_tip'	=> false,
		'placeholder' => __('123XXXXX', 'wc-messaging')
	),


	array(
		'id' => 'woom_sent_admin_numbers',
		'type' => 'text',
		'name' => __('Admin numbers', 'wc-messaging'),
		'desc' => __('WhatsApp numbers, in international format without the "+" sign, separated by commas.', 'wc-messaging'),
		'desc_tip'	=> false
	),
	array(
		'id' => 'woom_enable_report_error',
		'type' => 'checkbox',
		'name' => __('Enable debugging', 'wc-messaging'),
		'default' => true,
		'desc' => __('Enable debugging to log errors in the debug.log file.', 'wc-messaging'),
		'desc_tip'	=> false,

	),

	array(
		'id'	=> 'woom_general_settings',
		'type'	=> 'sectionend',
		'name'	=> 'end_section',
	),


	array(
		'id'    => 'woom_classic_checkout_settings',
		'type' => 'title',
		'name' => __('Checkout Opt-in', 'wc-messaging'),
	),

	array(
		'id' => 'woom_send_order_notification',
		'type' => 'select',
		'name' => __('Send whatsapp notifications', 'wc-messaging'),
		'default' => 'billing',
		'options' => array(
			'billing' => __('Billing number', 'wc-messaging'),
			'shipping' => __('Shipping number', 'wc-messaging'),
			'all' => __('Billing & shipping number', 'wc-messaging'),
			'disable' => __('Disable', 'wc-messaging'),
		),

	),

	array(
		'id' => 'woom_order_notification_permission',
		'type' => 'select',
		'name' => __('Opt-in method', 'wc-messaging'),
		'default' => 'disable',
		'options' => array(
			'enable' => __('Show checkbox for customer consent', 'wc-messaging'),
			'disable' => __('Send messages without customer consent', 'wc-messaging')
		),

	),

	array(
		'id' => 'woom_opt_in_checkbox_label',
		'type' => 'text',
		'name' => __('WhatsApp opt-in checkbox message', 'wc-messaging'),
		'default' => __('Get order updates on whatsapp', 'wc-messaging'),
        'desc' => __('Customise this label, for showing on the checkout page', 'wc-messaging'),
		'desc_tip'    => true,
	),

	array(
		'id'    => 'woom_validate_phone_number',
		'type'    => 'checkbox',
		'name' => __("Phone number validation", 'wc-messaging'),
		'default'    => 'yes',
		'options' => array('no' => 'False', 'yes' => 'true'),
        'desc' => __('Enable phone number custom validation at checkout', 'wc-messaging'). sprintf('<a href="%s" target="_blank">%s</a>', esc_url('https://sevengits.com/docs/wc-messaging-pro/#settings-page'), __('learn more.', 'wc-messaging')),
		'desc_tip'    => false,
	),

	array(
		'id'    => 'woom_classic_checkout_settings_end',
		'type'    => 'sectionend',
		'name'    => 'end_section',
	),
);
