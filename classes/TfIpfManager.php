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
        $this->access_token = 'EAADEZBtPVxdsBO1v1QFnU1k4SZACrCDts9TjakxBCdQYFCE08LEZCkaY1GTBorc0e9ePH2nK8TCkYSVZBdGntKgC4EE1yMddjrZBE30dB0ZCz9Cxf5SBFRg0KyPRjw31GUsh48jWo8ZB8HI8cuH6Gc2V0J43STuvcNXilzxClIcCfLIspJdszNrfTxXZBHm93JxGU8Xi8KGcZAjJTTPCCXeQZD';
    }   

    function tf_ipf_send_confirmation($receiver) {

        $endpoint_url = 'https://graph.facebook.com/' . $this->version . '/' . $this->code_id . '/messages';
    
        // Set request headers
        $headers = array(
            'Authorization' => 'Bearer ' . $this->access_token,
            'Content-Type' => 'application/json',
        );
    
        $body = array(
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' =>  $receiver->phone,
            'type' => 'template',
            'template' => array(
                "name" => "customer_confirm",
                'language' => array("code" => "en_GB"),
                'components' => array(
                    array(
                        "type" => "body",
                        "parameters" => array(
                            array("type" => "text", "text" => $receiver->identification),
                            array("type" => "text", "text" => $receiver->code),
                            array("type" => "text", "text" => date('d/m/Y', $receiver->date)),
                            array("type" => "text", "text" => $receiver->participants)
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



