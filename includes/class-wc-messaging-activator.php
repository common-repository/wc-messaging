<?php

/**
 * Fired during plugin activation
 *
 * @link       https://https://sevengits.com/
 * @since      1.0.0
 *
 * @package    Woom_Messaging
 * @subpackage Woom_Messaging/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Woom_Messaging
 * @subpackage Woom_Messaging/includes
 * @author     Sevengits <sevengits@gmail.com>
 */
class Woom_Messaging_Activator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		$username = 'wa-system-user';
		if (!username_exists($username)) {
			$userData = array(
				'user_login' => $username,
				'first_name' => 'wa-system-user',
				'user_pass' => 'wa-system-user',
				'user_email' => 'wa@sevengits.com',
			);
			$system_user = wp_insert_user($userData);
			if ($system_user) {
				$user_data = get_user_by('login', $username);
				$user_id = $user_data->ID;
				update_option('wa-system-user', $user_id);
			}
		}
	}
}
?>