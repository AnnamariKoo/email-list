<?php

add_shortcode('email-list-form', 'show_email_list_form');

add_action('rest_api_init', 'create_rest_endpoint');

function show_email_list_form() 
{
    include MY_PLUGIN_PATH . 'includes/templates/email-list-form.php';

}

function create_rest_endpoint()
{
    register_rest_route('v1/email-form', 'submit', array(

        'methods' => 'POST',
        'callback' => 'handle_enquiry',
    ));
}

function handle_enquiry($data)
{
     $params = $data->get_params(); 

     if ( !isset($params['_wpnonce']) ||!wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
         return new WP_Rest_response('Message not sent', 422);
        }
        
        return new WP_Rest_response('Message SENT!', 200);
}