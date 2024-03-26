<?php


class Tf_Ipf_Manager
{
    private $version; 
    private $code_id;
    private $access_token;

    function __construct() 
    {
        $this->version = 'v18.0'; 
        $this->code_id = '261127417074544';
        $this->access_token =  get_option( 'tfipf_whatsapp_token' );
    }   

    function tf_ipf_send_confirmation($phone, $code, $identification, $date_long, $participants) {

        $endpoint_url = 'https://graph.facebook.com/' . $this->version . '/' . $this->code_id . '/messages';
    
        // Set request headers
        $headers = array(
            'Authorization' => 'Bearer ' . $this->access_token,
            'Content-Type' => 'application/json',
        );
    
        $body = array(
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' =>  $phone,
            'type' => 'template',
            'template' => array(
                "name" => "customer_confirm",
                'language' => array("code" => "en_GB"),
                'components' => array(
                    array(
                        "type" => "body",
                        "parameters" => array(
                            array("type" => "text", "text" => $identification),
                            array("type" => "text", "text" => $code),
                            array("type" => "text", "text" => date('d/m/Y', $date_long)),
                            array("type" => "text", "text" => $participants)
                        )
                    )
                )
            )
        );


        $response = wp_remote_post(
            $endpoint_url,
            array(
                'headers' => $headers,
                'body' => wp_json_encode($body), 
            )
        );

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            return false;
        } else {
            // Get response body
            $response_body = wp_remote_retrieve_body($response);
            return true;
        }
    }
}



