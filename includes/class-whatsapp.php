<?php
if (!class_exists('WCWhatsapp')) {
    class WCWhatsapp
    {

        private $base_url;
        private $access_token;

        // initialise all variables
        function __construct()
        {

            $this->base_url    = $this->get_wa_baseurl(); // get_option('livo_live_server')."/api";
            $this->access_token = $this->get_wa_token();
        }


        private function get_wa_token()
        {
            if (!defined('DEFAULT_WA_ACCESS_TOKEN')) {
                $token = get_option('woom_whatsapp_api', '');
            } else {

                $token = DEFAULT_WA_ACCESS_TOKEN;
            }

            return $token;
        }
        private function get_wa_baseurl()
        {
            $woom_whatsapp_number_id = get_option('woom_whatsapp_number_id', '');
            $base_url = "";
            if ($woom_whatsapp_number_id !== '') {
                $base_url = sprintf('https://graph.facebook.com/v17.0/%s/messages', $woom_whatsapp_number_id);
            }
            return $base_url;
        }

        /**
         * Send Whatsapp text MEssage
         */

        public  function send_text_message($mobile, $text_message)
        {
            $wam_id = 0;
            $data = array(
                "messaging_product" => "whatsapp",
                "recipient_type" => "individual",
                "to" => $mobile,
                "type" => "text",
                "text" => array(
                    "preview_url" => false,
                    "body" => $text_message,
                ),
            );


            $response = wp_remote_post($this->base_url, array(
                'body'    => wp_json_encode($data),
                'headers' => array(
                    'Authorization' => 'Bearer ' . $this->access_token,
                    'content-type' => 'application/json',
                ),
            ));

            if (is_wp_error($response)) {
                $error_message = $response->get_error_message();
            } else {
                $api_response = json_decode(wp_remote_retrieve_body($response), true);
                if (isset($api_response['messages']['0']['id'])) {
                    $wam_id = $api_response['messages']['0']['id'];
                } else {
                }
            }

            return $wam_id;
        }
    } // End Class
}//