<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woom_Messaging
 * @subpackage Woom_Messaging/includes
 * @author     Sevengits <sevengits@gmail.com>
 */
class Woom_Messaging
{

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Woom_Messaging_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{
		if (defined('woom_version')) {
			$this->version = woom_version;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wc-messaging';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Woom_Messaging_Loader. Orchestrates the hooks of the plugin.
	 * - Woom_Messaging_i18n. Defines internationalization functionality.
	 * - Woom_Messaging_Admin. Defines all hooks for the admin area.
	 * - Woom_Messaging_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wc-messaging-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wc-messaging-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wc-messaging-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/class-wc-messaging-public.php';

		/**
		 * Whatspp Class 
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-whatsapp.php';

		$this->loader = new Woom_Messaging_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Woom_Messaging_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale()
	{
		$plugin_i18n = new Woom_Messaging_i18n();
		$this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks()
	{
		$plugin_admin = new Woom_Messaging_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		$this->loader->add_filter('woocommerce_settings_tabs_array', $plugin_admin, 'woom_tab', 50);
		$this->loader->add_filter('woom_tab_subsections', $plugin_admin, 'woom_add_tab_subsections');
		$this->loader->add_action('woocommerce_sections_woom_settings', $plugin_admin, 'woom_action_woocommerce_sections_woom_settings_tab');
		$this->loader->add_filter('woom_subsection_settings', $plugin_admin, 'woom_general_settings');
		$this->loader->add_filter('woom_subsection_settings_templates', $plugin_admin, 'woom_template_settings');
		$this->loader->add_filter('woom_additional_settings', $plugin_admin, 'woom_tools_settings');
		$this->loader->add_filter('woom_subsection_settings_support', $plugin_admin, 'woom_support_settings');
		$this->loader->add_action('woom_trigger_wa_msg', $plugin_admin, 'woom_trigger_msg', 100, 3);

		$this->loader->add_filter('woom_additional_settings', $plugin_admin, 'woom_custom_actions_settings');
		$this->loader->add_action('wp_ajax_woom_autosave_manual_trigger_actions', $plugin_admin, 'woom_save_custom_template_options');
		$this->loader->add_action('wp_ajax_nopriv_woom_autosave_manual_trigger_actions', $plugin_admin, 'woom_save_custom_template_options');


		$this->loader->add_action('wp_ajax_woom_clear_option', $plugin_admin, 'woom_remove_custom_template_options');
		$this->loader->add_action('wp_ajax_nopriv_woom_clear_option', $plugin_admin, 'woom_remove_custom_template_options');

		$this->loader->add_action('wp_ajax_woom_manual_trigger_action', $plugin_admin, 'woom_send_manual_msg');
		$this->loader->add_action('wp_ajax_nopriv_woom_manual_trigger_action', $plugin_admin, 'woom_send_manual_msg');

		$this->loader->add_action('wp_dashboard_setup', $plugin_admin, 'woom_add_wc_messaging_overview_widget');

		$this->loader->add_action('wp_ajax_woom_regenerate_wa_templates', $plugin_admin, 'woom_update_wa_templates');
		$this->loader->add_action('wp_ajax_nopriv_woom_regenerate_wa_templates', $plugin_admin, 'woom_update_wa_templates');

		$this->loader->add_action('woocommerce_settings_woom_settings', $plugin_admin, 'woom_action_woocommerce_settings_woom_settings_tab', 10);
		$this->loader->add_action('woocommerce_settings_save_woom_settings', $plugin_admin, 'woom_action_woocommerce_settings_save_woom_settings_tab', 10);
		$this->loader->add_action('woocommerce_admin_field_woom_config_per_status', $plugin_admin, 'woom_config_settings', 500);
		$this->loader->add_action('woocommerce_admin_field_woom_hidden', $plugin_admin, 'woom_hidden_settings', 500);
		$this->loader->add_action('woocommerce_admin_field_woom_trigger_button', $plugin_admin, 'woom_trigger_opt_settings', 100);
		$this->loader->add_action('woocommerce_admin_field_woom_descriptions', $plugin_admin, 'woom_support_descriptions', 100);
		$this->loader->add_action('woocommerce_admin_field_woom_info_downloader', $plugin_admin, 'woom_info_downlaoder', 100);

		$this->loader->add_action('add_meta_boxes', $plugin_admin, 'woom_action_buttons_meta_box');

		$this->loader->add_action('woocommerce_order_status_changed', $plugin_admin, 'woom_send', 10, 3);

		$this->loader->add_filter('plugin_action_links_' . woom_basename, $plugin_admin, 'woom_links_below_title_begin');
		// # below the plugin title in plugins page. add custom links at the end of list
		$this->loader->add_filter('plugin_action_links_' . woom_basename, $plugin_admin, 'woom_links_below_title_end');
		# below the plugin description in plugins page. add custom links at the end of list
		$this->loader->add_filter('plugin_row_meta', $plugin_admin, 'woom_plugin_description_below_end', 10, 2);
	}


	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks()
	{

		$plugin_public = new Woom_Messaging_Public($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
		$this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
		$this->loader->add_filter('woocommerce_checkout_fields', $plugin_public, 'woom_enable_notification_wc_classic_checkouts');
		$this->loader->add_action('woocommerce_init', $plugin_public, 'woom_enable_notification_wc_block_checkouts');
		$this->loader->add_action('wp_ajax_nopriv_append_countrycode', $plugin_public, 'woom_countrycode');
		$this->loader->add_action('wp_ajax_append_countrycode', $plugin_public, 'woom_countrycode');
		$this->loader->add_action('wp_footer', $plugin_public, 'woom_adding_countrycode_scripts');
		$this->loader->add_action('woocommerce_after_checkout_validation', $plugin_public, 'woom_verify_phone_number', 50, 2);

		$this->loader->add_action('woocommerce_checkout_order_processed', $plugin_public, 'woom_wa_noitfy_action', 50, 2);
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name()
	{
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Woom_Messaging_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader()
	{
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version()
	{
		return $this->version;
	}
}
