<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woom_Messaging
 * @subpackage Woom_Messaging/admin
 * @author     Sevengits <sevengits@gmail.com>
 */
class Woom_Messaging_Admin
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles($hook)
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woom_Messaging_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woom_Messaging_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style('chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.min.css', array(), '1.8.7', 'all');
		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wc-messaging-admin.css', array(), $this->version, 'all');
		if (!wp_style_is('sgits-admin-common-css', 'enqueued')) {
			wp_enqueue_style('sgits-admin-common', plugin_dir_url(__FILE__) . 'css/common.css', array(), $this->version, 'all');
		}
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts($hook)
	{
		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woom_Messaging_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woom_Messaging_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script('chosen', 'https://cdnjs.cloudflare.com/ajax/libs/chosen/1.8.7/chosen.jquery.min.js', array('jquery'), '1.8.7', true);
		wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__) . 'js/wc-messaging-admin.js', array('jquery'), $this->version, true);
		wp_localize_script($this->plugin_name . '-admin', 'woom_ajax', array('url' => admin_url('admin-ajax.php'), 'woom_post_nonce' => wp_create_nonce('woom-ajax-post')));
	}

	/**
	 * Helper function for checking checkboxes multiple values
	 * 
	 * @param string $value
	 * @return bool
	 * @since    1.0.0
	 */
	function woom_checkbox_valid($value = 'no')
	{
		$valid_values = array('yes', 1, 'on');
		$status = false;
		if (in_array(strtolower($value), $valid_values)) {
			$status = true;
		}
		return $status;
	}

	/**
	 * Helper function for checking checkboxes valid
	 * 
	 * @param string $error
	 * @return void
	 * @since    1.0.0
	 */
	function woom_report_error($error = '')
	{
		if ($this->woom_checkbox_valid(get_option('woom_enable_report_error'))) {
		}
	}

	/**
	 * Custom type settings function
	 * @since 1.0.0
	 *
	 * @param string $section: unique id for settings 
	 * @param string $field
	 * @return array()
	 */
	function get_settings_statuses($section = '', $statuses = array(), $editable = false)
	{
		$result = array();
		$token = get_option('woom_whatsapp_api', '');
		$templates = get_option('woom_wa_templates', array());
		if (!get_option('woom_wa_templates', array())) {
			$templates_result = $this->woom_get_message_templates($token);
			if ($templates_result['success']) {
				$templates = update_option('woom_wa_templates', $templates_result['data']);
			} else {
				$class = 'notice notice-error';
				printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($templates_result['message']));
			}
		}
		$template_name_list = array('' => __('Select a template', 'wc-messaging'));
		$template_param_count_list = array();
		$available_params = array();

		foreach ($this->woom_get_mparams('keys', 'array') as $value) {
			$available_params[$value] = $value;
		}
		if (is_array($templates)) {
			$template_ids = array_keys($templates);
			$template_names = array_column($templates, 'name');

			for ($id = 0; $id < count($template_names); $id++) {
				$template_param_count_list[$template_ids[$id]]['params_count'] = array('header' => 0, 'body' => 0, 'footer' => 0);
				if (isset($templates[$template_ids[$id]]['header_params_count'])) {
					$template_param_count_list[$template_ids[$id]]['params_count']['header'] = $templates[$template_ids[$id]]['header_params_count'];
				}
				if (isset($templates[$template_ids[$id]]['body_params_count'])) {
					$template_param_count_list[$template_ids[$id]]['params_count']['body'] = $templates[$template_ids[$id]]['body_params_count'];
				}
				if (isset($templates[$template_ids[$id]]['footer_params_count'])) {
					$template_param_count_list[$template_ids[$id]]['params_count']['footer'] = $templates[$template_ids[$id]]['footer_params_count'];
				}
				$template_name_list[$template_ids[$id]] = $template_names[$id];
			}
		}


		foreach ($statuses as $status_key => $status_label) {
			$id_prefix = $section . '_' . str_replace('-', '_', $status_key);
			if (!$editable) {
				$label_description = ucfirst(trim(str_replace('-', ' ', str_replace('wc', '', $status_key)) . ' order notification sent to the customer when orders have been marked ' . strtolower($status_label) . '.'));
			} else {
				$label_description = '';
			}

			$status_name = array(
				'id' => $id_prefix . '_label',
				'name' => $status_label,
				'type' => 'label',
				'desc' => $label_description,
				'desc_tip'	=> true
			);
			if ($editable) {
				$status_name = array(
					'id' => $id_prefix . '_label',
					'type' => 'text',
					'default' => $status_label,
					'desc' => $label_description,
					'desc_tip'	=> true
				);
			}
			$template_header_params_count = (isset($template_param_count_list[get_option($id_prefix . '_template', '')]['params_count']['header']) ? $template_param_count_list[get_option($id_prefix . '_template', '')]['params_count']['header'] : 0);
			$template_body_params_count = (isset($template_param_count_list[get_option($id_prefix . '_template', '')]['params_count']['body']) ? $template_param_count_list[get_option($id_prefix . '_template', '')]['params_count']['body'] : 0);
			$result[] = array(
				$status_name,
				array(
					'id' => $id_prefix . '_enabled',
					'type' => 'switch',
					'custom_attributes' => array(
						'onchange' => 'woom_handle_templates()',
					)

				),
				array(
					'id' => $id_prefix . '_template',
					'type' => "select",
					'placeholder' => __('template name', 'wc-messaging'),
					'options' => $template_name_list,
					'desc_tip'	=> true,
					'custom_attributes' => array(
						'onchange' => 'woom_update_template_preview(this,' . json_encode($template_param_count_list) . ')',
					)
				),
				array(
					'id' => $id_prefix . '_sent_admin',
					'type' => "checkbox",
					'default' => false
				),
				array(
					'id' => $id_prefix . '_header_params',
					'type' => "select",
					'default' => '',
					'options' => array_merge(array('' => ($template_header_params_count === 0) ? __('No variables..', 'wc-messaging') :  __('Add variables..', 'wc-messaging')), $available_params),
					'desc' => __('This is text that you specify in the API that will be personalized to the customer, such as their name or order number.', 'wc-messaging'),
					'desc_tip'	=> true,
					'disabled' => ($template_header_params_count === 0),
					'custom_attributes' => array(
						'data-params_count' => $template_header_params_count,
						'onChange' => 'woom_handle_templates()',
					)
				),
				array(
					'id' => $id_prefix . '_body_params',
					'type' => "chosen-select",
					'placeholder' => __('Select variables..', 'wc-messaging'),
					'default' => '',
					'options' => $available_params,
					'desc' => __('This is text that you specify in the API that will be personalized to the customer, such as their name or order number.', 'wc-messaging'),
					'desc_tip'	=> true,
					'disabled' => ($template_body_params_count === 0),
					'custom_attributes' => array(
						'multiple' => 'true',
						'data-params_label_empty' => __('No variables..', 'wc-messaging'),
						'data-params_label' => __('Add variables..', 'wc-messaging'),
						'data-params_count' => $template_body_params_count,
						'data-error_message' => __("{{count}} variable missing", 'wc-messaging'),
						'data-chosen_value' => implode(',', get_option($id_prefix . '_body_params', array())),
					)
				),
				array(

					'id' => $id_prefix . '_actions',
					'type' => "actions",
					'options' => array(
						array(
							'id' => $id_prefix . '_remove',
							'type' => "button",
							'show_only_if_editable' => $editable,
							'name' => __('Remove', 'wc-messaging'),
							'data' => array('prefix' => $id_prefix)
						),
						array(
							'id' => $id_prefix . '_preview',
							'type' => "link",
							'name' => __('Preview', 'wc-messaging'),
							'data' => array('prefix' => $id_prefix)
						)
					)

				),
			);
		}
		return $result;
	}



	/**
	 * get selected order status from array of statuses
	 * @since 1.0.0
	 *
	 * @param array $statuses
	 * @param string $status
	 * @return mixed
	 */
	function get_filtered_status($statuses = array(), $status = '')
	{
		foreach ($statuses as $status_key => $status_value) {
			if (str_replace('wc-', '', $status_key) === $status) {
				return array($status_key => $status_value);
			}
		}
		return array();
	}

	/**
	 * Woocommerce advanced tab custom sub tab
	 * @since 1.0.0
	 *
	 * @param [type] $settings_tab
	 * @return void
	 */
	public function woom_tab($settings_tab)
	{

		$settings_tab['woom_settings'] = __('WC Messaging', 'wc-messaging');
		return $settings_tab;
	}

	/**
	 * Function for adding WC Messaging sub sections
	 * 
	 * @param mixed $sections
	 * @return mixed
	 * @since 1.0.0
	 */
	function woom_add_tab_subsections($sections)
	{
		$new_sections = array(
			'woom_settings' => array(
				''              => __('General', 'wc-messaging'),
				'templates'  => __('Templates', 'wc-messaging'),
			)
		);
		return array_merge($sections, $new_sections);
	}

	/**
	 * Function for adding WC Messaging settings
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_action_woocommerce_sections_woom_settings_tab()
	{
		global $current_section;
		$tab_id = 'woom_settings';
		$subsections = apply_filters('woom_tab_subsections', array());
		$links_html = '';
		$subsections['woom_settings']['support'] = __('Support', 'wc-messaging');
		foreach ($subsections as $tab_id => $sections) {
			$array_keys = array_keys($sections);
			foreach ($sections as $id => $label) {
				$link_url = esc_url(admin_url('admin.php?page=wc-settings&tab=' . $tab_id . '&section=' . sanitize_title($id)));
				$class_list = ($current_section == $id ? 'current' : '');
				$seperator = (end($array_keys) == $id) ? '' : '|';
				$links_html .= sprintf('<li><a href="%1$s" class="%2$s">%3$s</a>%4$s</li>', $link_url, $class_list, ucfirst($label), $seperator);
			}
			printf('<ul class="subsubsub">%1$s</ul><br class="clear"/>', wp_kses_post($links_html));
		}
		echo '<p id="woom-ajax-result"></p>';


		return;
	}


	function woom_trigger_opt_settings($link)
	{
?>
		<table class="wc_status_table wc_status_table--tools widefat">

			<tr class="clear_transients">
				<th>
					<?php
					printf('<strong class="name">%1$s</strong>', esc_html($link['name']));
					printf('<p class="description">%1$s</p>', esc_html($link['desc'])); ?>
				</th>
				<td class="run-tool">
					<?php
					$action = $link['option'];
					$custom_attributes = '';
					if (isset($action['custom_attributes'])) {
						foreach ($action['custom_attributes'] as $attr_key => $attr_val) {
							if (!empty($custom_attributes)) {
								$custom_attributes .= ' ';
							}
							$custom_attributes .= sprintf('%1$s=%2$s', $attr_key, $attr_val);
						}
					}
					$classes = '';
					if (isset($action['classname'])) {
						if (!empty($action['classname'])) {
							$classes .= ' ' . $action['classname'];
						} else {
							$classes = $action['classname'];
						}
					}
					switch ($action['type']) {
						case 'button':
							if (!empty($action['classname'])) {
								$classes .= ' button button-large';
							} else {
								$classes = 'button button-large';
							}
							printf('<button  %2$s class="%3$s">%1$s</button>', esc_html(ucfirst($action['name'])), esc_attr($custom_attributes), esc_attr($classes));
							break;

						default:
							# code...
							break;
					}
					?>
				</td>
			</tr>

		</table>
	<?php
	}


	function woom_update_wa_templates()
	{
		$result = array("success" => false, "templates" => array(), "message" => __("Failed to update", 'wc-messaging'));
		if ($_POST !== null) :
			if (!empty(sanitize_text_field(wp_unslash($_POST['data']['woom_nonce']))) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['data']['woom_nonce'])), 'woom-ajax-post') && !empty(sanitize_text_field(wp_unslash($_POST['data']['woom_access_token'])))) {
				$token = sanitize_text_field(wp_unslash($_POST['data']['woom_access_token']));
				$template_results = $this->woom_get_message_templates($token);
				$result = array("success" => $template_results['success'], "templates" => $template_results['data'], "message" => $template_results['message']);
			}
		endif;
		if ($result['success']) {
			update_option('woom_wa_templates', $result['templates']);
			$class = 'notice notice-success';
		} else {
			$class = 'notice notice-error';
		}
		$result = array("success" => $result['success'], "message" => $result['message']);
		$result['data'] = sprintf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($result['message']));
		return wp_send_json($result);
	}

	/**
	 * Function for  WC Messaging custom settings
	 * 
	 * @return mixed
	 * @since 1.0.0
	 */
	function get_custom_settings()
	{
		global $current_section;
		$settings = array();
		$subsections = apply_filters('woom_tab_subsections', array());
		$subsections['woom_settings']['support'] = __('Support', 'wc-messaging');

		foreach ($subsections as $sections) {
			foreach (array_keys($sections) as $section) {
				if ($current_section == $section) {
					if ($section !== '') {
						$settings = apply_filters('woom_subsection_settings_' . $section, array());
					} else {
						$settings = apply_filters('woom_subsection_settings', array());
					}
				}
			}
		}
		return $settings;
	}

	/**
	 * Function for  WC Messaging general settings
	 * 
	 * @return mixed $settings
	 * @return mixed
	 * @since 1.0.0
	 */
	function woom_general_settings($settings)
	{
		$new_settings = array();
		if (file_exists(plugin_dir_path(__FILE__) . 'partials/settings/general.php')) {
			include(plugin_dir_path(__FILE__) . 'partials/settings/general.php');
		}
		return array_merge($settings, $new_settings);
	}

	/**
	 * Function for  WC Messaging template settings
	 * 
	 * @return mixed $settings
	 * @return mixed
	 * @since 1.0.0
	 */
	function woom_template_settings($settings)
	{
		$new_settings = array();
		if (file_exists(plugin_dir_path(__FILE__) . 'partials/settings/template.php')) {
			include(plugin_dir_path(__FILE__) . 'partials/settings/template.php');
		}
		$additional_settings = apply_filters('woom_additional_settings', $new_settings);

		return array_merge($settings, $additional_settings);
	}


	/**
	 * Function for custom actions settings
	 * 
	 * @param mixed $settings
	 * @return mixed
	 * @since 1.0.0
	 */
	function woom_custom_actions_settings($settings)
	{
		$new_settings = array(

			array(
				'id'	=> 'woom_template_woomactions_tab',
				'type' => 'title',
				'name' => __('Custom trigger buttons', 'wc-messaging'),
				// 'desc'	=>	__('WC messaging configuration', 'wc-messaging'),
			),
			array(
				'id' => 'woom_trigger_actions',
				'type' => 'woom_config_per_status',
				'name' => __('Custom trigger buttons', 'wc-messaging'),
				'template_titles' => array(
					'title' => __('Button name', 'wc-messaging')
				),
				'label_editable' => true,
				'add_new_row' => true,
				'fields' => $this->get_settings_statuses('woom_trigger', $this->woom_custom_options('trigger_actions'), $editable = true),

			),
			array(
				'id'	=> 'woom_wcb_general_settings',
				'type'	=> 'sectionend',
				'name'	=> 'end_section',
			),
		);
		$settings = array_merge($settings, $new_settings);
		return $settings;
	}





	/**
	 * Function for custom options
	 * 
	 * @param string $section
	 * @return array
	 * @since 1.0.0
	 */
	function woom_custom_options($section = 'wc_bookings')
	{
		$result = array();
		switch ($section) {
			case 'wc_bookings':
				if (function_exists('get_wc_booking_statuses') && count(get_wc_booking_statuses()) > 0) {
					$result = array(
						'pending-confirmation' => __('Pending confirmation', 'wc-messaging'),
						'confirmed' => __('Confirmed', 'wc-messaging'),

					);
				}
				break;
			case 'trigger_actions':
				$result = array();
				$options = get_option('woom_trigger_actions', array('action_1'));
				if (count($options) === 0) {
					delete_option('woom_trigger_actions');
				}
				foreach (get_option('woom_trigger_actions', array('action_1')) as $action) {
					$result[$action] = get_option('woom_trigger_' . $action . '_label', 'Action 1');
				}
				break;
			case 'trigger_hooks':
				$result = array();
				foreach (get_option('woom_trigger_hooks', array('hook_1')) as $hook) {
					if (!empty(get_option('woom_trigger_' . $hook . '_label', ''))) {
						$result[$hook] = get_option('woom_trigger_' . $hook . '_label', '');
					}
				}
				break;

			default:
				$result = array();
				break;
		}
		return $result;
	}

	function woom_tools_settings($settings)
	{
		$new_settings = array();
		if (file_exists(plugin_dir_path(__FILE__) . 'partials/settings/tools.php')) {
			include(plugin_dir_path(__FILE__) . 'partials/settings/tools.php');
		}
		return array_merge($new_settings, $settings);
	}
	function woom_support_settings($settings)
	{
		$new_settings = array();
		if (file_exists(plugin_dir_path(__FILE__) . 'partials/settings/support.php')) {
			include(plugin_dir_path(__FILE__) . 'partials/settings/support.php');
		}
		return array_merge($settings, $new_settings);
	}
	function woom_get_site_diagnostic_info()
	{

		// Site URLs
		$site_url = get_site_url();
		$home_url = get_home_url();

		// Server Info
		$web_server = (!empty(sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])))) ? sanitize_text_field(wp_unslash($_SERVER['SERVER_SOFTWARE'])) : 'Not available';

		// WordPress Info
		$wp_version = get_bloginfo('version');
		$multisite = is_multisite();
		$multisite_site_count = function_exists('get_blog_count') ? get_blog_count() : 'N/A';
		$wp_locale = get_locale();

		// PHP Info
		$php_version = phpversion();
		$php_memory_limit = ini_get('memory_limit');
		$wp_memory_limit = WP_MEMORY_LIMIT;

		// Construct the diagnostic info
		$diagnostic_info = array(
			'site url' => $site_url,
			'home url' => $home_url,
			'Web Server' => $web_server,
			'WordPress' => $wp_version . ($multisite ? "Multisite (subdirectory)" : ""),
			'Multisite Site Count' => $multisite_site_count,
		);
		$site_info = array(
			'WP Locale' => $wp_locale,
			'PHP' => $php_version,
			'PHP Memory Limit' => $php_memory_limit,
			'WP Memory Limit' => $wp_memory_limit
		);
		$woom_content = '';
		foreach ($diagnostic_info as $info_key => $info_val) {
			$woom_content .= sprintf('%1$s: %2$s <br>', ucfirst($info_key), $info_val);
		}
		$woom_content .= '<br>';
		foreach ($site_info as $info_key => $info_val) {
			$woom_content .= sprintf('%1$s: %2$s <br>', ucfirst($info_key), $info_val);
		}
		$woom_content .= '<br>Active Theme details<br>';
		$woom_active_theme_data = array(get_template() => array(
			'name' => wp_get_theme()->name,
		));
		if (!empty(wp_get_theme()->parent_theme)) {
			$woom_active_theme_data[get_template()]['Parent'] = wp_get_theme()->parent_theme;
		}
		$woom_active_theme_data[get_template()]['version'] = wp_get_theme()->version;
		foreach ($woom_active_theme_data[get_template()] as $theme_data => $theme_data_val) {
			$woom_content .= sprintf('%1$s: %2$s </br>', ucfirst($theme_data), $theme_data_val);
		}
		$woom_content .= '<br>Active plugins<br>';
		foreach ($this->woom_get_active_plugins_with_versions() as $text_domain => $plugin) {
			$woom_content .= sprintf('%1$s: %2$s </br>', ucfirst($plugin['name']), $plugin['version']);
		}
		return nl2br($woom_content);
	}
	function woom_get_active_plugins_with_versions()
	{
		$active_plugins = get_option('active_plugins');
		$plugins_info = array();

		foreach ($active_plugins as $plugin) {
			$plugin_path = WP_PLUGIN_DIR . '/' . $plugin;
			$plugin_data = get_plugin_data($plugin_path);

			$plugin_info = array(
				'name' => $plugin_data['Name'],
				'version' => $plugin_data['Version']
			);

			$plugins_info[$plugin_data['TextDomain']] = $plugin_info;
		}

		return $plugins_info;
	}

	function woom_html_to_plaintext($html)
	{
		$text = wp_strip_all_tags($html);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = preg_replace('/\s+/', ' ', $text);
		$text = preg_replace('/\s*<br\s*\/?>\s*/i', "\n", $text);
		return $text;
	}
	function woom_support_descriptions($field)
	{
		$content = '';
		if (isset($field['name']) && !empty($field['name'])) {
			$content .= printf('<h3>%s</h3>', esc_html($field['name']));
		}
		if (isset($field['desc']) && !empty($field['desc'])) {
			$content .= printf('<p>%s</p>', wp_kses($field['desc'], array(
				'a' => array(
					'href'  => array(),
					'target' => array(),
				),
				'p' => array()
			)));
		}
		if (isset($field['woom_desc']) && is_array($field['woom_desc'])) {
			foreach ($field['woom_desc'] as $desc) {
				$content .= printf('<p>%s</p>', wp_kses(
					$desc,
					array(
						'a' => array(
							'href'  => array(),
							'target' => array(),
						)
					)
				));
			}
		}
		return $content;
	}

	function woom_info_downlaoder($field)
	{
		echo '<style>.submit {display: none;}</style>';

		$content = '';
		$disabled = (isset($field['disabled']) && $field['disabled'] === true) ? 'readonly' : '';

		if (isset($field['content']) && !empty($field['content'])) {
			if (is_array($field['content'])) {
				foreach ($field['content'] as $data) {
					$content .= sprintf('<p>%1$s</p>', $data);
				}
			} else {
				$content = sprintf($field['content']);
			}
			$content = sprintf('<div data-html="' . $this->woom_html_to_plaintext($content) . '" class="woom-fullwidth woom_support_diagnostic_info" %2$s>%1$s</div>', $content, $disabled);
		}
		$buttons = '';
		if (isset($field['download_actions']) && !empty($field['download_actions'])) {
			foreach ($field['download_actions'] as $action) {
				$button_text = (isset($action['button_text']) && !empty($action['button_text'])) ? $action['button_text'] : '';
				$button_class = (isset($action['button_class']) && !empty($action['button_class'])) ? $action['button_class'] : '';
				$onclick_func = '';
				if (isset($action['button_action']) && !empty($action['button_action'])) {
					switch ($action['button_action']) {
						case 'download':
							$onclick_func = 'woom_doc_download(event)';
							break;
						case 'copy':
							$onclick_func = 'woom_doc_copy(event)';
							break;
					}
				}
				if ($onclick_func !== '') {
					$buttons .= sprintf('<button class="%2$s" onclick="' . $onclick_func . '">%1$s</button>', $button_text, $button_class, $onclick_func);
				} else {
					$buttons .= sprintf('<button class="%2$s">%1$s</button>', $button_text, $button_class);
				}
			}

			$content = printf('<div class="woom-info-container">%1$s <p class="woom-buttons-group">%2$s</p></div>', wp_kses($content, array(
				'div' => array('class' => array()),
				'p' => array('class' => array()),
				'br' => array(),
			)), wp_kses(
				$buttons,
				array('button' => array(
					'class' => array()
				))
			));
		}
		return wp_kses($content, array(
			'div' => array('class' => array()),
			'p' => array('class' => array()),
			'br' => array(),
			'button' => array('class' => array())
		));
	}




	/**
	 * Function for display settings in the WooCommerce settings page.
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_action_woocommerce_settings_woom_settings_tab()
	{
		// Call settings function
		$settings = $this->get_custom_settings();
		WC_Admin_Settings::output_fields($settings);
	}
	/**
	 * Function for processing and saving custom settings 
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_action_woocommerce_settings_save_woom_settings_tab()
	{
		global $current_section;
		$tab_id = 'woom_settings';

		// Call settings function
		$settings = array();
		foreach ($this->get_custom_settings() as $fields) {
			if (array_key_exists('fields', $fields)) {
				foreach ($fields['fields'] as $custom_fields) {
					if (array_key_exists('id', $custom_fields)) {
						array_push($settings, $custom_fields);
					} else {
						if (is_array($custom_fields)) {
							foreach ($custom_fields as $custom_fields2) {
								array_push($settings, $custom_fields2);
							}
						}
					}
				}
			} else {
				array_push($settings, $fields);
			}
		}
		foreach ($settings as $option) {
			delete_option($option['id']);
			if ($option['type'] === 'switch') {
				$option['type'] = 'checkbox';
			}
			if ($option['type'] === 'woom_copy_url') {
				$option['type'] = 'text';
			}
		}
		WC_Admin_Settings::save_fields($settings);
		if ($current_section) {
			if ($current_section !== '') {
				do_action('woocommerce_update_options_' . $tab_id . '_' . $current_section, $settings);
			} else {
				do_action('woocommerce_update_options_' . $tab_id, $settings);
			}
		}
	}

	function woom_get_message_templates($token)
	{
		$result = array("data" => array(), "success" => false, "message" => __('Failed to retrieve data', 'wc-messaging'));
		if (empty($token)) {
			$err_message = __('Please configure general settings', 'wc-messaging');
			$result = array("data" => array(), "success" => false, "message" => $err_message);
			return $result;
		}

		$number_id = get_option('woom_wb_account_ID', '');
		// $api_version = get_option('woom_wa_api_version', 'v19.0');
		$api_version = 'v18.0';
		$url = array('https://graph.facebook.com', $api_version, $number_id, 'message_templates');
		$url = esc_url(implode('/', $url));
		$options = array(
			'headers'     => array(
				'Authorization' => "Bearer $token",
			)
		);
		$templates = [];
		$request = wp_remote_get($url, $options);
		if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
			$error_data = json_decode(wp_remote_retrieve_body($request));
			$this->woom_report_error($error_data);
			$err_message = esc_html__("Something went wrong...", 'wc-messaging');
			if (isset($error_data->error->message)) {
				$err_message = $error_data->error->message;
			}
			$result = array("data" => $error_data, "success" => false, "message" => esc_html($err_message));
		} else {
			$response = wp_remote_retrieve_body($request);
			$response = json_decode($response);
			foreach ($response->data as $key => $res) {
				$templates[$res->id] = array(
					'name' => $res->name,
					'language' => $res->language,
					'category' => $res->category,
					'status' => $res->status,
				);
				if (isset($res->components)) :
					foreach ($res->components as $comp_key => $comp_val) {
						if (strtolower($comp_val->type) === 'body') {
							$templates[$res->id]['Body'] = $comp_val->text;
							if (isset($comp_val->example->body_text)) {

								$templates[$res->id]['body_params_count'] = count($comp_val->example->body_text[0]);
							} else {
								$templates[$res->id]['body_params_count'] = 0;
							}
						}
						if (strtolower($comp_val->type) === 'header') {
							$templates[$res->id]['Header'] = $comp_val->text;

							if (isset($comp_val->example->header_text)) {
								if (is_array($comp_val->example->header_text)) {
									$templates[$res->id]['header_params_count'] = count($comp_val->example->header_text);
								} else {
									$this->woom_report_error($comp_val->example->header_text);
								}
							} else {
								$templates[$res->id]['header_params_count'] = 0;
							}
						}
						if (strtolower($comp_val->type) === 'footer') {
							$templates[$res->id]['Footer'] = $comp_val->text;
							if (isset($comp_val->example->footer_text)) {
								$templates[$res->id]['footer_params_count'] = count($comp_val->example->footer_text);
							} else {
								$templates[$res->id]['footer_params_count'] = 0;
							}
						}
					}
				endif;
			}
			$result = array("data" => $templates, "success" => true, "message" => __('Data update successful.','wc-messaging'));
		}
		return $result;
	}

	/**
	 * Custom display for settings type of "woom_config_per_status"
	 * 
	 * @param [type] $links
	 * @return void
	 * @since 1.0.0
	 */
	public function woom_config_settings($links)
	{
		$headings = array(
			'title' => __('Order status', 'wc-messaging'),
			'enable_switch' => __('Enable / disable', 'wc-messaging'),
			'template' => __('Template name', 'wc-messaging'),
			'sent_admin' => __('Sent to admin', 'wc-messaging'),
			'header_params' => __('Header parameters', 'wc-messaging'),
			'body_params' => __('Body parameters', 'wc-messaging'),
			'actions' => __('Actions', 'wc-messaging')
		);
		if (isset($links['template_titles'])) {
			foreach ($links['template_titles'] as $field => $title) {
				if (array_key_exists($field, $headings)) {
					$headings[$field] = $title;
				}
			}
		}
	?>
		<table class="wc_emails widefat" cellspacing="0">
			<thead>
				<tr>
					<?php
					foreach ($headings as $head_id => $heading) {
						echo '<th class="wc-email-settings-table-' . esc_attr($head_id) . '">' . esc_html($heading) . '</th>';
					}
					?>
				</tr>
			</thead>
			<tbody class="woom-table-body">
				<?php
				foreach ($links['fields'] as $key => $fields) {
				?>
					<tr class="woom-field-row-<?php echo esc_attr($key); ?>">
						<?php
						foreach ($fields as $field) {
							if ($field['type'] !== 'label') {
								$field['value'] = '';
								if (array_key_exists('default', $field) && $field['value'] === '') {
									$field['value'] = $field['default'];
								}
								$field['value'] = get_option($field['id'], '');
								if (!array_key_exists('placeholder', $field)) {
									$field['placeholder'] = '';
								}
							}
							switch ($field['type']) {
								case 'label':
						?>
									<td class="wc-email-settings-table-name-with-tooltip">
										<?php
										printf('<span>%s</span>', wp_kses_post($field['name']));
										if (isset($field['desc']) && !empty($field['desc'])) {
											if (isset($field['desc_tip'])) {
												if ($field['desc_tip'] === true) {
													printf(wp_kses_post(wc_help_tip($field['desc'])));
												} else {
													printf('<span>%s</span>', wp_kses_post($field['desc']));
												}
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										}
										?>
									</td>
								<?php
									break;
								case 'switch':
									$custom_attributes = '';
									if (isset($field['custom_attributes'])) {
										foreach ($field['custom_attributes'] as $attr_key => $attr_val) {
											if (!empty($custom_attributes)) {
												$custom_attributes .= ' ';
											}
											$custom_attributes .= sprintf('%1$s=%2$s', $attr_key, $attr_val);
										}
									}
								?>
									<td class="wc-email-settings-table-switch woom-switch-checkbox">
										<?php
										if ($this->woom_checkbox_valid($field['value'])) {
											printf('<input type="checkbox" id="%1$s" value="yes" name="%1$s" checked %2$s>', esc_attr($field['id']), esc_attr($custom_attributes));
										} else {
											printf('<input type="checkbox" id="%1$s" value="yes" name="%1$s" %2$s>', esc_attr($field['id']), esc_attr($custom_attributes));
										}
										printf('<label for="%s" class="switch"></label>', esc_attr($field['id']));
										if (isset($field['desc']) && !empty($field['desc'])) {
											if (isset($field['desc_tip'])) {
												if ($field['desc_tip'] === true) {
													printf(wp_kses_post(wc_help_tip($field['desc'])));
												} else {
													printf('<span>%s</span>', wp_kses_post($field['desc']));
												}
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										}
										?>
									</td>
								<?php
									break;
								case 'checkbox':
									if ($this->woom_checkbox_valid($field['value'])) {
										printf('<td class="woom-field-checkbox"><input type="checkbox" id="%1$s" name="%1$s" value="yes" checked></td>', esc_attr($field['id']));
									} else {
										printf('<td class="woom-field-checkbox"><input type="checkbox" id="%1$s" value="yes" name="%1$s"></td>', esc_attr($field['id']));
									}

									break;
								case 'text':

								?>
									<td class="woom-field-text">
										<?php
										printf('<input type="text" id="%1$s" name="%1$s" placeholder="%2$s" value="%3$s">', esc_attr($field['id']), esc_attr($field['placeholder']), esc_attr($field['value']));
										if (isset($field['desc']) && !empty($field['desc'])) {
											if (isset($field['desc_tip'])) {
												if ($field['desc_tip'] === true) {
													printf(wp_kses_post(wc_help_tip($field['desc'])));
												} else {
													printf('<span>%s</span>', wp_kses_post($field['desc']));
												}
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										}
										?>
									</td>
								<?php
									break;
								case 'textarea':

								?>
									<td class=" with-tooltip">
										<?php
										printf('<textarea id="%1$s" name="%1$s" placeholder="%2$s" value="%3$s"></textarea>', esc_attr($field['id']), esc_attr($field['placeholder']), esc_attr(($field['value'])));
										if (isset($field['desc']) && !empty($field['desc'])) {
											if (isset($field['desc_tip'])) {
												if ($field['desc_tip'] === true) {
													printf(wp_kses_post(wc_help_tip($field['desc'])));
												} else {
													printf('<span>%s</span>', wp_kses_post($field['desc']));
												}
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										}
										?>
									</td>
								<?php

									break;
								case 'select':
									$field_value = isset($field['default']) ? $field['default'] : '';
									$field_value = get_option($field['id'], $field_value);
									$custom_attributes = '';
									if (isset($field['custom_attributes'])) {
										foreach ($field['custom_attributes'] as $attr_key => $attr_val) {
											if ($custom_attributes !== '') {
												$custom_attributes .= ' ';
											}
											$custom_attributes .= sprintf('%1$s=%2$s', $attr_key, $attr_val);
										}
									}
									if (isset($field['disabled'])) {
										if ($custom_attributes !== '') {
											$custom_attributes .= ' ';
										}
										if ($field['disabled']) {
											$custom_attributes .= 'disabled="true"';
										}
									}
								?>
									<td class="woom-field-select">
										<?php
										printf('<select name="%1$s" id="%1$s" %2$s>', esc_attr($field['id']), esc_attr($custom_attributes));
										foreach ($field['options'] as $option_value => $option_text) {
											if (trim($option_value) === trim($field_value) && !empty(trim($field_value))) {
												printf('<option value="%1$s" selected>%2$s</option>', esc_attr($option_value), esc_html($option_text));
											} else {
												printf('<option value="%1$s">%2$s</option>', esc_attr($option_value), esc_html($option_text));
											}
										}
										printf('</select>');
										if (isset($field['desc']) && !empty($field['desc'])) {
											if (isset($field['desc_tip'])) {
												if ($field['desc_tip'] === true) {
													printf(wp_kses_post(wc_help_tip($field['desc'])));
												} else {
													printf('<span>%s</span>', wp_kses_post($field['desc']));
												}
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										}
										?>
									</td>
					<?php
									break;
								case 'actions':
									echo '<td class="woom-field-actions">';
									foreach ($field['options'] as $field_opt) {
										switch ($field_opt['type']) {
											case 'button':
												if (isset($field_opt['show_only_if_editable']) && $field_opt['show_only_if_editable']) {
													$attr = "";
													if (str_contains($field_opt['id'], '_1_')) {
														$attr = 'disabled="true"';
													}
													if (isset($field_opt['data'])) {
														foreach ($field_opt['data'] as $key => $value) {
															$attr .= 'data-' . $key . '="' . $value . '"';
														}
													}
													echo '<button ' . esc_attr($attr) . ' id="' . esc_html($field_opt['id']) . '" class="button remove-opt button-small btn button-secondary" onClick="woom_remove_field_trigger(event, this)">' . esc_html($field_opt['name']) . '</button>';
												}
												break;
											case 'link':

												$template_id = get_option(str_replace('actions', 'template', $field['id']), '');
												$attr = sprintf('data-id=%s', esc_attr('woom_template_table_' . $template_id));
												printf('<a href="#" class="link" %1$s onclick="woom_toggle_template_popup(event, this)">%2$s</a>', esc_attr($attr), esc_html($field_opt['name']));

												break;

											default:
												break;
										}
									}
									echo "</td>";
									break;
								case 'chosen-select':
									$custom_attributes = '';
									if (isset($field['custom_attributes'])) {
										foreach ($field['custom_attributes'] as $attr_key => $attr_val) {
											if ($custom_attributes !== '') {
												$custom_attributes .= ' ';
											}
											if (strpos($attr_val, '{{') !== false) {
												$custom_attributes .= sprintf('%1$s="%2$s"', esc_attr($attr_key), esc_js($attr_val));
											} else {
												$custom_attributes .= sprintf('%1$s="%2$s"', esc_attr($attr_key), esc_html($attr_val));
											}
										}
									}
									if (isset($field['disabled'])) {
										if ($custom_attributes !== '') {
											$custom_attributes .= ' ';
										}
										if ($field['disabled']) {
											$custom_attributes .= sprintf('disabled="%s"', esc_attr("true"));
										}
									}
									printf('<td class="woom-field-%s">', esc_attr($field['type']));
									printf('<select data-placeholder="%1$s" class="chosen chosen-select" name="%2$s[]" id="%2$s" %3$s >', esc_attr($field['placeholder']), esc_attr($field['id']), wp_kses($custom_attributes, array()));
									if (!is_array(get_option($field['id'], array()))) {
										delete_option($field['id']);
									}
									if (is_array(get_option($field['id'], array())) && !empty(get_option($field['id'], array()))) {
										foreach (get_option($field['id'], array()) as $option) {

											printf('<option value="%1$s" selected>%1$s</option>', esc_attr($option));
										}
									}
									foreach ($field['options'] as $option) {
										if (is_array(get_option($field['id'], array()))) {
											if (!in_array($option, get_option($field['id'], array()))) {
												printf('<option value="%1$s">%1$s</option>', esc_attr($option));
											}
										}
									}

									echo '</select>';

									if (isset($field['desc']) && !empty($field['desc'])) {
										if (isset($field['desc_tip'])) {
											if ($field['desc_tip'] === true) {
												printf(wp_kses_post(wc_help_tip($field['desc'])));
											} else {
												printf('<span>%s</span>', wp_kses_post($field['desc']));
											}
										} else {
											printf('<span>%s</span>', wp_kses_post($field['desc']));
										}
									}
									echo '<p class="woom-warning-message" style="display:none;"></p></td>';

									break;
								default:
									break;
							}
						}
						echo "</tr>";
					}
					?>
			</tbody>
			<?php
			if (array_key_exists("add_new_row", $links) && $links['add_new_row']) {
			?>
				<tfoot>
					<tr>
						<td colspan="10">
							<a data-row="<?php echo esc_html(substr($links['id'], 0, -1)); ?>" onclick="woom_addnew_row(this)" class="button">Add new</a>
						</td>
					</tr>
				</tfoot>
			<?php
			}

			?>
		</table>
		<!-- popup template starts here -->

		<div class="woom-popup-window" id="woom-wa-templates" onclick="woom_toggle_template_popup(event, this, false)" style="display: none;">
			<div class="woom-popup-panel" onclick="woom_prevent_element(event, false)">
				<div class="woom-popup-header">
					<?php
					$classes = 'woom-popup-dismiss dashicons dashicons-dismiss';
					printf('<span class="%1$s" %2$s onclick="woom_toggle_template_popup(event, this, false)"></span>', esc_attr($classes), esc_attr($attr));
					?>

					<?php printf('<h3>%s</h3>', esc_html(__('Selected template details', 'wc-messaging'))); ?>
				</div>
				<div class="woom-popup-content">
					<?php
					$templates = get_option('woom_wa_templates', array());
					foreach ($templates as $template_id => $template) :
						printf('<table class="popup-content wc_emails" id="%s" style="display: none;">', esc_attr('woom_template_table_' . $template_id));
						foreach ($template as $field_name => $field_val) {
							if (!str_contains($field_name, 'params_count')) {
								printf('<tr><td><b>%1$s</b></td><td>%2$s</td></tr>', esc_html(ucfirst($field_name)), wp_kses($field_val, array()));
							}
						}
						echo "</table>";
					endforeach;
					?>
				</div>
			</div>
		</div>
		<!-- popup template ends here -->

	<?php
	}

	function woom_hidden_settings($link)
	{
		if (isset($link['default']) && !isset($link['value'])) {
			$link['value'] = get_option($link['id'], $link['default']);
		}
		if (!isset($link['value'])) {
			$link['value'] = '';
		}
		$link['value'] = get_option($link['id'], $link['value']);
		printf('<input style="display: none;" type="text" name="%1$s" id="%1$s" value="%2$s">', esc_attr($link['id']), esc_attr($link['value']));
	}

	/**
	 * Function for custom template options
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_save_custom_template_options()
	{
		if (!isset($_POST['data']['woom_nonce'])) {
			return;
		}
		if (!isset($_POST['data']['key'])) {
			return;
		}
		$result = array("status" => 'failed', "message" => __('Something went wrong', 'wc-messaging'));
		if (isset($_POST)) :
			if (wp_verify_nonce(sanitize_key($_POST['data']['woom_nonce']), 'woom-ajax-post')) {
				$key = sanitize_text_field(sanitize_key($_POST['data']['key']));
				$key_arr = get_option('woom_trigger_actions', array('action_1'));
				array_push($key_arr, $key);
				update_option('woom_trigger_actions', $key_arr);
				$result = array("status" => 'success', "message" => __('New row added successfully', 'wc-messaging'));
			}
		endif;
		return wp_send_json($result);
	}

	/**
	 * Function for remove custom template options
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_remove_custom_template_options()
	{
		if ((empty(sanitize_key(wp_unslash($_POST['data']['woom_nonce']))))) {
			return;
		}
		if ((empty(sanitize_text_field(wp_unslash($_POST['data']['prefix']))))) {
			return;
		}

		if (isset($_POST)) :
			if (wp_verify_nonce(sanitize_key(wp_unslash($_POST['data']['woom_nonce'])), 'woom-ajax-post')) {
				$woom_trigger_actions = get_option('woom_trigger_actions', array('action_1'));
				$removed_actions = trim(str_replace("woom_trigger_", '', sanitize_text_field(wp_unslash($_POST['data']['prefix']))), '"');
				
				$opts = array('_label', '_enabled', '_template', '_sent_admin', '_header_params', '_body_params', '_remove');
				foreach ($opts as $opt) {
					delete_option('woom_trigger_' . $removed_actions . $opt);
				}
				
				update_option('woom_trigger_actions', array_diff($woom_trigger_actions, array($removed_actions)));
				$result = array('status' => 'success', 'message' => "trigger action $removed_actions removed");
				return wp_send_json($result);
			}
		endif;
	}

	/**
	 * get order status options and id prefix
	 * 
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_order_status_settings($key = '')
	{
		$result = array();
		if (is_plugin_active('woocommerce/woocommerce.php') && ($key === '' || $key === 'woocommerce')) {
			$result[] = array('woom_woocommerce_config_per_status', wc_get_order_statuses());
		}
		return $result;
	}

	/**
	 * facilitates the retrieval of specific parameters  for WooCommerce orders.
	 * 
	 * @param string $type
	 * @param string $method
	 * @since 1.0.0
	 */
	function woom_get_mparams($type = "keys", $method = "string", $order = null, $options = '')
	{
		$param_keys = array();
		$param_values = array();
		$additional_params = apply_filters("woom_additional_template_params", array(), $order);
		$params_list = array(
			"site_title" => get_bloginfo('name'),
			"site_address" => get_bloginfo('wpurl'),
			"site_url" => get_bloginfo('url'),
		);
		if ($order === null) {


			$order_data =
				array(
					'order_id' => '',
					'order_status' => '',
					'order_prices_include_tax' => '',
					'order_discount_total' => '',
					'order_discount_tax' => '',
					'order_shipping_total' => '',
					'order_shipping_tax' => '',
					'order_cart_tax' => '',
					'order_total' => '',
					'order_total_tax' => '',
					'order_customer_id' => '',
					'order_order_key' => '',
					'order_billing_full_name' => '',
					'order_shipping_full_name' => '',
					'order_billing_first_name' => '',
					'order_billing_last_name' => '',
					'order_billing_company' => '',
					'order_billing_address_1' => '',
					'order_billing_address_2' => '',
					'order_billing_city' => '',
					'order_billing_state' => '',
					'order_billing_postcode' => '',
					'order_billing_country' => '',
					'order_billing_email' => '',
					'order_billing_phone' => '',
					'order_shipping_first_name' => '',
					'order_shipping_last_name' => '',
					'order_shipping_company' => '',
					'order_shipping_address_1' => '',
					'order_shipping_address_2' => '',
					'order_shipping_city' => '',
					'order_shipping_state' => '',
					'order_shipping_postcode' => '',
					'order_shipping_country' => '',
					'order_shipping_phone' => '',
					'order_payment_method' => '',
					'order_payment_method_title' => '',
					'order_transaction_id' => '',
					'order_created_via' => '',
					'order_number' => '',
					'order_date_created' => '',
					'order_date_modified' => '',
					'order_date_completed' => '',
					'order_date_paid' => ''
				);
		} else {
			$order_data = array();
			$order_data_prefix = "order_";

			foreach ($order->get_data() as $order_key => $order_val) {
				if (in_array(gettype($order_val), ['object', 'array'])) {
					$order_data = $this->get_params_from_object($order_val, $order_data_prefix . $order_key . "_", $order_data);
				} else {
					$order_data[$order_data_prefix . $order_key] = $order_val;
				}
			}
			$order_data['order_billing_full_name'] = $order->get_formatted_billing_full_name();
			$order_data['order_shipping_full_name'] = $order->get_formatted_shipping_full_name();
			if (!empty($order->get_date_created())) {
				$order_data['order_date_created'] = $order->get_date_created()->date("F j, Y");
			}
			if (!empty($order->get_date_modified())) {
				$order_data['order_date_modified'] = $order->get_date_modified()->date("F j, Y");
			}
			if (!empty($order->get_date_completed())) {
				$order_data['order_date_completed'] = $order->get_date_completed()->date("F j, Y");
			}
			if (!empty($order->get_date_paid())) {
				$order_data['order_date_paid'] = $order->get_date_paid()->date("F j, Y");
			}
		}
		$params_list = array_merge($params_list, $order_data, $additional_params);
		if ($options !== '') {
			$avail_param_list = array();
			if (!is_array($options)) {
				$options = explode(',', $options);
			}
			foreach ($options as $param) {
				$param = str_replace(' ', '', $param);
				$avail_param_list[$param] = $params_list[$param];
			}
			$params_list = $avail_param_list;
		}
		switch ($type) {
			case 'keys':
				$param_keys = array_keys($params_list);
				if ($method === 'string') {
					return implode(", ", $param_keys);
				}
				return $param_keys;
				break;
			case 'values':
				$param_values = array_values($params_list);
				if ($method === 'string') {
					return implode(", ", $param_values);
				}
				return $param_values;
				break;

			default:

				if ($method === 'string') {
					$param = "";
					foreach ($params_list as $p_key => $p_value) {
						if ($param !== '') {
							$param .= ",";
						}
						$param .= $p_key . '=' . $p_value;
					}
					return $param;
				}
				return $params_list;
				break;
		}
		if ($method === 'string') {
			return implode(", ", $param_values);
		}
		return $param_values;
	}

	/**
	 * Send whatsapp message
	 * 
	 *
	 * @param [type] $message_id
	 * @param string $order_id
	 * @param array $order_status = array(old => OLD-STATUS, new => NEW-STATUS)
	 * @return void
	 * @since 1.0.0
	 */
	public function woom_trigger_msg($order_id = '', $template_prefix = '', $contact = array())
	{
		if ($order_id !== '') {
			$order = wc_get_order($order_id);
			if (get_option('woom_whatsapp_api', '') !== '') {

				if ($this->woom_checkbox_valid(get_option($template_prefix . '_enabled', 'no'))) {
					$token = get_option('woom_whatsapp_api', '');
					if (count(get_option($template_prefix . '_params', array()))) {
						delete_option($template_prefix . '_params');
					}
					$header_param_options = get_option($template_prefix . '_header_params', array());
					$body_param_options = get_option($template_prefix . '_body_params', array());

					if (is_array($header_param_options) && (in_array('order_number', $header_param_options) || in_array('order_date', $header_param_options))) {
						array_walk($header_param_options, function (&$value) {
							if ($value === 'order_number') {
								$value = 'order_id';
							}
							if ($value === 'order_date') {
								$value = 'order_date_created';
							}
						});
						update_option($template_prefix . '_header_params', $header_param_options);
					}
					if (is_array($body_param_options) && (in_array('order_number', $body_param_options) || in_array('order_date', $body_param_options))) {
						array_walk($body_param_options, function (&$value) {
							if ($value === 'order_number') {
								$value = 'order_id';
							}
							if ($value === 'order_date') {
								$value = 'order_date_created';
							}
						});
						update_option($template_prefix . '_body_params', $body_param_options);
					}

					$templates = get_option('woom_wa_templates', array());
					$sel_template = $templates[get_option($template_prefix . '_template', '')];
					$template = array(
						"name" => $sel_template['name'],
						"language" => array('code' => $sel_template['language'])
					);
					$body_params_list = array();
					$header_param_list = array();
					if (!empty($body_param_options)) :
						$body_params = array();
						$body_params_list = array_merge($body_params_list, $this->woom_get_mparams($type = "both", $method = "array", $order, $body_param_options));
						if (count($body_params_list) > 0) {
							$param_missing = "";
							$param_error_msg = " is missing";
							foreach ($body_params_list as $param_key => $param_value) {
								if (empty($param_value)  || $param_value === NULL || !$param_value) {
									if (!empty($param_missing)) {
										$param_error_msg = " are missing";
										$param_missing .= ", ";
									}
									$param_missing .= $param_key;
								}
								$body_params[] = array("type" => "text", "text" => $param_value);
							}
							if (!empty($param_missing)) {
								$param_error_msg = 'WC Messaging: ' . $param_missing . $param_error_msg;
								$order->add_order_note($param_error_msg, $is_customer_note = 0, $added_by_user = false);
							}
							if (!isset($template["components"])) {
								$template["components"] = array();
							}
							$template["components"][] = [
								"type" => "body",
								"parameters" => $body_params
							];
						}

					endif;

					if (!empty($header_param_options)) :
						$header_params = array();
						$header_param_list = array_merge($header_param_list, $this->woom_get_mparams($type = "both", $method = "array", $order, $header_param_options));

						if (count($header_param_list) > 0) {
							$param_missing = "";
							$param_error_msg = " is missing";
							foreach ($header_param_list as $param_key => $param_value) {
								if (empty($param_value)  || $param_value === NULL || !$param_value) {
									if (!empty($param_missing)) {
										$param_error_msg = " are missing";
										$param_missing .= ", ";
									}
									$param_missing .= $param_key;
								}
								$header_params[] = array("type" => "text", "text" => $param_value);
							}
							if (!empty($param_missing)) {
								$param_error_msg = 'WC Messaging: ' . $param_missing . $param_error_msg;
								$order->add_order_note($param_error_msg, $is_customer_note = 0, $added_by_user = false);
							}
							if (!isset($template["components"])) {
								$template["components"] = array();
							}
							$template["components"][] = [
								"type" => "header",
								"parameters" => $header_params
							];
						}
					endif;
					$order_status = $order->get_status();

					if ($this->woom_checkbox_valid(get_option($template_prefix . '_sent_admin', 'no'))) {
						$admin_numbers = get_option('woom_sent_admin_numbers', '');
						$admin_numbers = explode(",", $admin_numbers);
						$contact = array_merge($contact, $admin_numbers);
					}
					if (count($contact) > 0) {
						foreach ($contact as $num) {
							$num = str_replace('+', '', str_replace(' ', '', $num));
							$number_id = get_option('woom_whatsapp_number_id', '');
							if (!empty($number_id)) {
								$api_version = 'v17.0';
								$url = array('https://graph.facebook.com', $api_version, $number_id, "messages?access_token=$token");
								$url = esc_url(implode("/", $url));
								$options = array(
									'headers'     => array(
										'Authorization' => $token,
									),
									'body'        => array(
										'messaging_product' => "whatsapp",
										'to' => floatval($num),
										'type' => "template",
										'template' => $template
									)
								);
								$request = wp_remote_post($url, $options);
								if (is_wp_error($request) || wp_remote_retrieve_response_code($request) != 200) {
									$error_data = json_decode(wp_remote_retrieve_body($request));
									if (isset($error_data)) {
										do_action('woom_whatsapp_msg_sent_fail', array('result' => $error_data));

										if (isset($error_data->error->error_data->details) && isset($error_data->error->message)) {
											$err_details = $error_data->error->error_data->details;
											$msg = "Whatsapp notification failed. " . $error_data->error->message . ": " . $err_details;
											$this->woom_report_error("Whatsapp notification failed. " . $error_data->error->message . ": " . $err_details);
										} else if ($error_data->error->message) {
											$msg = "Whatsapp notification failed. " . $error_data->error->message;
											$this->woom_report_error("Whatsapp notification failed. " . $error_data->error->message);
										} else {
											$this->woom_report_error($error_data);
										}
									}
								} else {
									$htmlspecifies = htmlspecialchars_decode($this->woom_get_whatsapp_template_by_name($template['name'], array('body' => $body_params_list, 'header' => $header_param_list)));
									$response = wp_remote_retrieve_body($request);
									$response = json_decode($response);
									$mid = $response->messages[0]->id;
									$msg = "Whatsapp message sent for " . $order_status . "\n---------------------------------- \n" . $htmlspecifies;
									$message_container = array(
										'comment_content' => $sel_template['name'],
										'post_id' => $order->get_id(),
										'parent_id' => 0,
										'comment_agent' => $response->contacts[0]->wa_id,
										'parameters' => array('body' => $body_params_list, 'header' => $header_param_list)
									);
									if (floatVal($num) === floatVal($order->get_shipping_phone()) || floatVal($num) === floatVal($order->get_billing_phone())) {
										do_action('woom_whatsapp_msg_sent_success', array('wam_data' => $response->messages[0], 'comment' => $message_container));
									} else {
										do_action('woom_whatsapp_msg_sent_admin_success', array('wam_data' => $response->messages[0], 'comment' => $message_container));
									}
									$order->add_order_note('WC Messaging: ' . $msg, $is_customer_note = 0, $added_by_user = false);
								}
							} else {
								$this->woom_report_error('number id is empty. Please update in settings');
							}
						}
					} else {

						$this->woom_report_error("Sentable numbers array is empty");
					}
				}
			} else {
				$this->woom_report_error("Whatsapp token is empty. Please update in settings");
			}
		}
	}


	/**
	 * Function for action buttons of meta boxes
	 * 
	 * @return void 
	 * @since 1.0.0
	 */
	function woom_action_buttons_meta_box()
	{
		$screen_id = "shop_order";
		if (get_current_screen()->post_type === $screen_id && get_current_screen()->id !== $screen_id) {
			$screen_id = get_current_screen()->id;
		}
		if (!empty(get_option('woom_whatsapp_api', ''))) {
			add_meta_box(
				'woom-manual-trigger-actions',
				__('Whatsapp notifications', 'wc-messaging'),
				array($this, 'woom_action_buttons_display'),
				$screen_id,
				'side'
			);
		}
	}

	/**
	 * Function for action button display
	 * 
	 * @param mixed $order
	 * @return void
	 * @since 1.0.0
	 */
	function woom_action_buttons_display($order)
	{

		if ($order instanceof WP_Post) {
			$order = new WC_Order($order->ID);
		}
		$woom_order_id = $order->get_id();
	?>
		<section>
			<div class="wcw-buttons">
				<?php
				foreach (get_option('woom_trigger_actions', array('action_1')) as $button) {
					if ($this->woom_checkbox_valid(get_option('woom_trigger_' . $button . '_enabled', 'no'))) {
						$button_prefix = "woom_trigger_$button";
						$button_label = get_option('woom_trigger_' . $button . '_label', '');
						printf('<button data-order-id="%1$s" data-prefix="%2$s" class="button btn button-secondary" onClick="woom_trigger_button(event, this)">%3$s</button>', esc_attr($woom_order_id), esc_attr($button_prefix), esc_html($button_label));
					}
				}
				?>
			</div>
		</section>
	<?php
	}

	/**
	 * Function for send manual messages
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	function woom_send_manual_msg()
	{
		if (!isset($_POST['data']['woom_nonce'])) {
			return;
		}
		if (!isset($_POST['data']['order_id'])) {
			return;
		}
		if (!isset($_POST['data']['slug_prefix'])) {
			return;
		}
		$result = array();
		if ($_POST !== null) :
			if (wp_verify_nonce(sanitize_key($_POST['data']['woom_nonce']), 'woom-ajax-post')) {
				$order_id = sanitize_text_field(wp_unslash($_POST['data']['order_id']));
				$slug_prefix = sanitize_text_field(wp_unslash($_POST['data']['slug_prefix']));
				$result = array('status' => 'success', 'msg' => __("Message sent successfully", 'wc-messaging'));
				$woom_wa_notification_status = get_option('woom_send_order_notification', 'all');
				$is_user_wa_msg_billing_accepted = in_array($woom_wa_notification_status, array('all', 'billing'));
				$is_user_wa_msg_shipping_accepted = in_array($woom_wa_notification_status, array('all', 'shipping'));
				$order = wc_get_order($order_id);

				$contact_numbers = array();
				if ($is_user_wa_msg_billing_accepted && !empty($order->get_billing_phone())) {
					$contact_numbers[] = $order->get_billing_phone();
				}
				if ($is_user_wa_msg_shipping_accepted && !empty($order->get_shipping_phone())) {
					$contact_numbers[] = $order->get_shipping_phone();
				}
				do_action('woom_trigger_wa_msg', $order_id, $slug_prefix, $contact_numbers);

				return wp_send_json($result);
			}
			$result = array('status' => 'error', 'msg' => __("Failed to verify nonce", 'wc-messaging'));
		endif;
		$result = array('status' => 'error', 'msg' => __("Failed to fetch post data", 'wc-messaging'));
		return wp_send_json($result);
	}


	/**
	 * get array of data if array has multiple arrays
	 *
	 * @param [type] $list
	 * @return mixed
	 */
	function get_params_from_object($list, $prefix, $result = array())
	{
		foreach ($list as $key => $value) {
			if (in_array(gettype($value), ['object', 'array'])) {
				$this->get_params_from_object($value, $prefix . $key . '_', $result);
			} else {
				$result[$prefix . $key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Send whatsapp message trigger if order status changed
	 *
	 * @param [type] $order_id
	 * @param [type] $old_status
	 * @param [type] $new_status
	 * @return void
	 * @since 1.0.0
	 */

	public function woom_send($order_id, $old_status, $new_status)
	{
		$order = wc_get_order($order_id);

		foreach (array_merge(array(), $this->get_order_status_settings()) as $get_order_status_settings) {
			$get_filtered_status = array_merge(array(), $this->get_filtered_status($get_order_status_settings[1], $new_status));
			if (count($get_filtered_status) > 0) {
				$get_filtered_status = str_replace('-', '_', array_keys($get_filtered_status)[0]);
				$slug_prefix = $get_order_status_settings[0] . '_' . $get_filtered_status;
				if (!empty(get_option($slug_prefix . '_template', '')) && $this->woom_checkbox_valid(get_option($slug_prefix . '_enabled', 'no'))) {

					$woom_wa_notification_status = get_option('woom_send_order_notification', 'all');
					$is_trigger_msg_opt_disabled = $woom_wa_notification_status === 'disable';
					$is_userconsent_disabled = get_option('woom_order_notification_permission', 'enable') === 'disable';
					$is_user_wa_msg_billing_accepted = in_array($woom_wa_notification_status, array('all', 'billing'));
					$is_user_wa_msg_shipping_accepted = in_array($woom_wa_notification_status, array('all', 'shipping'));
					if (!$is_userconsent_disabled) {
						if (in_array($woom_wa_notification_status, array('all', 'billing'))) {

							if ($order->get_meta('_wc_billing/namespace/woom_notification', true)) {
								if (empty($order->get_meta('_wc_billing/namespace/woom_notification'))) {
									$is_user_wa_msg_billing_accepted =  false;
								}
							} else if (empty($order->get_meta('_billing_woom_notification'))) {
								$is_user_wa_msg_billing_accepted = false;
							}
						}
						if (in_array($woom_wa_notification_status, array('all', 'shipping'))) {

							if ($order->get_meta('_wc_shipping/namespace/woom_notification', true)) {
								if (empty($order->get_meta('_wc_shipping/namespace/woom_notification'))) {
									$is_user_wa_msg_shipping_accepted =  false;
								}
							} else if (empty($order->get_meta('_shipping_woom_notification'))) {
								$is_user_wa_msg_shipping_accepted = false;
							}
						}
					}
					$contact_numbers = array();
					if ($is_user_wa_msg_billing_accepted && $is_user_wa_msg_shipping_accepted) {
						if (!empty($order->get_billing_phone()) && !empty($order->get_shipping_phone()) && ($order->get_billing_phone() !== $order->get_shipping_phone())) {
							$contact_numbers[] = $order->get_billing_phone();
							$contact_numbers[] = $order->get_shipping_phone();
						} else if (!empty($order->get_billing_phone())) {
							$contact_numbers[] = $order->get_billing_phone();
						} else if (!empty($order->get_shipping_phone())) {
							$contact_numbers[] = $order->get_shipping_phone();
						}
					} else if ($is_user_wa_msg_billing_accepted && !empty($order->get_billing_phone())) {
						$contact_numbers[] = $order->get_billing_phone();
					} else if ($is_user_wa_msg_shipping_accepted && !empty($order->get_shipping_phone())) {
						$contact_numbers[] = $order->get_shipping_phone();
					}
					if (!$is_trigger_msg_opt_disabled && ($is_user_wa_msg_billing_accepted || $is_user_wa_msg_shipping_accepted || $is_userconsent_disabled)) {
						do_action('woom_trigger_wa_msg', $order_id, $slug_prefix, $contact_numbers);
					}
				} else {

					if (!$this->woom_checkbox_valid(get_option($slug_prefix . '_enabled', 'no'))) {
						$this->woom_report_error(__("notification disabled in template configuration", 'wc-messaging'));
					} else if (empty(get_option($slug_prefix . '_template', ''))) {
						$this->woom_report_error(__("Template name not specified", 'wc-messaging'));
						$this->woom_report_error('Order status: ' . $new_status);
					}
				}
			}
		}
	}

	/**
	 * Addition of new links to an existing list of links
	 * 
	 * @param mixed $old_list
	 * @param mixed $new_list
	 * @param string $position
	 * @return mixed
	 * @since 1.0.0
	 */
	public function woom_merge_links($old_list, $new_list, $position = "end")
	{
		$settings = array();
		foreach ($new_list as $name => $item) {
			$target = (array_key_exists("target", $item)) ? $item['target'] : '';
			$classList = (array_key_exists("classList", $item)) ? $item['classList'] : '';
			$settings[$name] = '<a href="' . esc_url($item['link']) . '" target="' . $target . '" class="' . $classList . '">' . esc_html($item['name']) . '</a>';
		}
		if ($position !== "start") {
			// push into $links array at the end
			return array_merge($old_list, $settings);
		} else {
			return array_merge($settings, $old_list);
		}
	}

	/**
	 *  modify the list of links displayed in the WordPress admin area
	 * 
	 * @param mixed $links
	 * @return mixed
	 * @since 1.0.0
	 */
	public function woom_links_below_title_begin($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the begin of list

		$link_list = array(
			'settings' => array(
				"name" => __('Settings', 'wc-messaging'),
				"classList" => "",
				"link" => esc_url(admin_url('admin.php?page=wc-settings&tab=woom_settings'))
			)
		);
		return $this->woom_merge_links($links, $link_list, "start");
	}

	/**
	 * Designed to modify the list of links displayed below the title of the plugin on the plugins page in the WordPress admin area.
	 * 
	 * @param mixed $links
	 * @return mixed
	 * @since 1.0.0
	 */
	public function woom_links_below_title_end($links)
	{
		// if plugin is installed $links listed below the plugin title in plugins page. add custom links at the end of list
		$link_list = array(
			'docs' => array(
				"name" => __('Docs', 'wc-messaging'),
				"target" => '_blank',
				"link" => esc_url('https://sevengits.com/docs/wc-messaging/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin')
			),
			'buy-pro' => array(
				"name" => 'Buy Premium',
				"classList" => "pro-purchase get-pro-link",
				"target" => '_blank',
				"link" => 'https://sevengits.com/plugin/wc-messaging-pro/?utm_source=Wordpress&utm_medium=plugins-link&utm_campaign=Free-plugin'
			)
		);
		return $this->woom_merge_links($links, $link_list, "end");
	}

	/**
	 * Function used to provide additional links related to the wc messaging
	 * 
	 * @param mixed $links
	 * @param mixed $file
	 * @return mixed
	 * @since 1.0.0
	 */
	function woom_plugin_description_below_end($links, $file)
	{
		if (strpos($file, 'wc-messaging.php') !== false) {
			$new_links = array(
				'docs' => array(
					"name" => __('Docs', 'wc-messaging'),
					"target" => '_blank',
					"link" => esc_url('https://sevengits.com/docs/wc-messaging-pro/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin')
				),
				'support' => array(
					"name" => __('Support', 'wc-messaging'),
					"target" => '_blank',
					"link" => esc_url('https://sevengits.com/contact/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin')
				),

				'pro' => array(
					"name" => 'Buy Premium',
					"classList" => "pro-purchase get-pro-link",
					"target" => '_blank',
					"link" => 'https://sevengits.com/plugin/wc-messaging-pro/?utm_source=dashboard&utm_medium=plugins-link&utm_campaign=Free-plugin'
				),

			);
			$links = $this->woom_merge_links($links, $new_links, "end");
		}
		return $links;
	}


	function woom_get_whatsapp_template_by_name($template_name = '', $args = array())
	{
		$templates = get_option('woom_wa_templates', array());
		$template = array();
		$templateHTML = '';
		foreach ($templates as $template_key => $template_value) {
			if ($template_value['name'] === $template_name) {
				if (array_key_exists('header_params_count', $template_value) && $template_value['header_params_count'] > 0) {
					$header_parameters = array();

					for ($i = 0; $i < $template_value['header_params_count']; $i++) {
						$header_parameters['{{' . ($i + 1) . '}}'] = array_values($args['header'])[$i];
					}
					foreach ($header_parameters as $param_key => $param_val) {
						$template_value['Header'] = str_replace($param_key, $param_val, $template_value['Header']);
					}
				}
				if (array_key_exists('body_params_count', $template_value) && $template_value['body_params_count'] > 0) {
					$body_parameters = array();

					for ($i = 0; $i < $template_value['body_params_count']; $i++) {
						$body_parameters['{{' . ($i + 1) . '}}'] = array_values($args['body'])[$i];
					}
					foreach ($body_parameters as $param_key => $param_val) {
						$template_value['Body'] = str_replace($param_key, $param_val, $template_value['Body']);
					}
				}
				if (array_key_exists('footer_params_count', $template_value) && $template_value['footer_params_count'] > 0) {
					$footer_parameters = array();

					for ($i = 0; $i < $template_value['footer_params_count']; $i++) {
						$footer_parameters['{{' . ($i + 1) . '}}'] = array_values($args['footer'])[$i];
					}
					foreach ($footer_parameters as $param_key => $param_val) {
						$template_value['Footer'] = str_replace($param_key, $param_val, $template_value['Footer']);
					}
				}
				$template = $template_value;
			}
		}
		if (count($template) > 0) {
			if (array_key_exists('Header', $template)) {
				$templateHTML .= sprintf('<h3 class="woom-template-header">%s</h3>', $template['Header']);
			}
			if (array_key_exists('Body', $template)) {
				$templateHTML .= sprintf('<div class="woom-template-body">%s</div>', $template['Body']);
			}
			if (array_key_exists('Footer', $template)) {
				$templateHTML .= sprintf('<small class="woom-template-footer">%s</small>', $template['Footer']);
			}
		}
		return $templateHTML;
	}
	/**
	 * 	Callback function to fetch health status and display widget content
	 * 
	 * @return void
	 * @since 1.0.0
	 */
	public function woom_actions_buttons_display()
	{
		// Transient keys
		$transient_key = 'wc_messaging_health_status';
		$update_time_transient = $transient_key . '_update_time';

		// Retrieve the transient data
		$woom_response_data = get_transient($transient_key);

		// Retrieve the last updated time
		$update_time = get_transient($update_time_transient);
		$formatted_update_time = $update_time ? gmdate('Y-m-d H:i:s', $update_time) : 'Unknown';

		if ($woom_response_data === false) {
			// Fetch the health status from Facebook API
			$woom_whatsapp_number_id = get_option('woom_whatsapp_number_id');
			$woom_whatsapp_api = get_option('woom_whatsapp_api');
			$api_url = 'https://graph.facebook.com/v20.0/' . $woom_whatsapp_number_id . '?fields=health_status';
			$bearer_token = $woom_whatsapp_api;

			// Set up the request arguments
			$args = array(
				'timeout'     => 45,
				'redirection' => 5,
				'headers'     => array(
					'Content-Type'  => 'application/json; charset=utf-8',
					'Authorization' => 'Bearer ' . $bearer_token,
				),
				'cookies'     => array(),
			);

			// Perform the request
			$response = wp_remote_get($api_url, $args);

			// Check for errors
			if (is_wp_error($response)) {
				echo '<p>Error fetching health status.</p>';
				return;
			}

			// Get the response body
			$woom_body = wp_remote_retrieve_body($response);

			// Decode the JSON response
			$woom_response_data = json_decode($woom_body, true);

			// Store the response in a transient, set to expire in 1 hour
			set_transient($transient_key, $woom_response_data, HOUR_IN_SECONDS);

			// Store the current timestamp as the update time
			$update_time = time();
			set_transient($update_time_transient, $update_time, HOUR_IN_SECONDS);

			// Update the formatted update time
			$formatted_update_time = gmdate('Y-m-d H:i:s', $update_time);
		}

		// Extract the relevant status for each entity
		$statuses = array();
		if (isset($woom_response_data['health_status']['entities'])) {
			foreach ($woom_response_data['health_status']['entities'] as $entity) {
				if (isset($entity['entity_type']) && isset($entity['can_send_message'])) {
					$statuses[$entity['entity_type']] = $entity['can_send_message'];
				}
			}
		}

		// Display widget content
		$default_status = 'Unavailable';
		$phone_status = isset($statuses['PHONE_NUMBER']) ? $statuses['PHONE_NUMBER'] : $default_status;
		$waba_status = isset($statuses['WABA']) ? $statuses['WABA'] : $default_status;
		$business_status = isset($statuses['BUSINESS']) ? $statuses['BUSINESS'] : $default_status;
		$app_status = isset($statuses['APP']) ? $statuses['APP'] : $default_status;
	?>
		<div class="wrap">
			<h2>Health Status</h2>
			<div class="wc-messaging-overview">
				<div class="status">
					<table>
						<tr>
							<td><b>Phone Number</b></td>
							<td><?php echo esc_html($phone_status); ?></td>
						</tr>
						<tr>
							<td><b>WABA</b></td>
							<td><?php echo esc_html($waba_status); ?></td>
						</tr>
						<tr>
							<td><b>Business</b></td>
							<td><?php echo esc_html($business_status); ?></td>
						</tr>
						<tr>
							<td><b>APP</b></td>
							<td><?php echo esc_html($app_status); ?></td>
						</tr>
					</table>
				</div>
				<div class="links">
					<a href="https://wordpress.org/support/plugin/wc-messaging/">Support</a> |
					<a href="https://sevengits.com/docs/wc-messaging-pro/">Documentation</a> |
					<a href="https://sevengits.com/blog/">Blog</a> |
					<a href="https://wordpress.org/support/plugin/wc-messaging/reviews/">Write Review</a>
				</div>
				<div class="wc-updated-last">
					<?php echo " * Last updated " . esc_html($formatted_update_time); ?>
				</div>
			</div>
		</div>
		<style>
			.wc-messaging-overview {
				border: 1px solid #e5e5e5;
				padding: 10px;
				background: #fff;
			}

			.wc-messaging-overview .status table {
				width: 100%;
			}

			.wc-messaging-overview .status table td {
				padding: 5px;
				border-bottom: 1px solid #e5e5e5;
			}

			.wc-messaging-overview .links {
				margin-top: 10px;
			}

			.wc-messaging-overview .links a {
				margin-right: 10px;
			}

			.wc-updated-last {
				text-align: end;
				font-size: 10px;
			}
		</style>
<?php
	}

	// Hook to add the widget to the dashboard
	public function woom_add_wc_messaging_overview_widget()
	{
		wp_add_dashboard_widget(
			'wc_messaging_overview_widget', // Widget slug
			'WC Messaging Overview', // Title
			array($this, 'woom_actions_buttons_display') // Display function
		);
	}
}
?>