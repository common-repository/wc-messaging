<?php
if (!defined('ABSPATH')) {
    exit;
}
$pages = array();
foreach (get_pages() as $page) {
    $pages[$page->post_name] = $page->post_title;
}
$new_settings = array(
    array(
        'id'    => 'woom_widget_tab',
        'type' => 'title',
        'name' => __('Support', 'wc-messaging'),
        'desc'    => '',
    ),
    array(
        'id'    => 'woom_support_description',
        'type'  => 'woom_descriptions',
        'name'  => '',
        'woom_desc'  => array(
            __("This is a free plugin. You can request support in the official WordPress plugin directory <a href='https://wordpress.org/support/plugin/wc-messaging/' target='_blank'>here</a>. We are always looking for suggestions and feature requests. If you've found a bug, report it immediately so we can fix it as soon as possible.", 'wc-messaging'),
            __('For a timely response via email from the developers who work on this plugin, <a href="https://sevengits.com/plugin/wc-messaging-pro/" target="_blank">upgrade to premium version</a>.', 'wc-messaging')
        ),
    ),
    array(
        'id'    => 'woom_support_diagnostic_info',
        'type'  => 'woom_info_downloader',
        'name'  => __('Diagnostic info', 'wc-messaging'),
        'content'  => $this->woom_get_site_diagnostic_info(),
        'disabled' => true,
        'download_actions' => array(
            'option-1' => array(
                'button_text' => __('Download', 'wc-messaging'),
                'button_class' => 'woom-button button primary',
                'button_action' => 'download'
            ),
            'option-2' => array(
                'button_text' => __('Copy', 'wc-messaging'),
                'button_class' => 'woom-button button-link',
                'button_action' => 'copy'
            )

        )
    ),
    array(
        'id'    => 'woom_widget_config_end',
        'type'    => 'sectionend',
        'name'    => 'end_section',
    ),
);
