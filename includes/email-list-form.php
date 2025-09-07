<?php

add_shortcode('email-list-form', 'show_email_list_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_mailing_list_page');

function create_mailing_list_page()
{
    $args = [

        'public' => true,
        'has_archive' => true,
        'labels'  => [
            'name' => 'Submissions', 
            'singular_name' => 'Submission'
        ],
        'capabilities' => [ 'create_posts' => 'do_not_allow']
    ];

    register_post_type('mailing_list', $args);
}

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
        
    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    // Send the email message
    $headers = [];

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');

    $headers[] = "From: {$admin_name} <{$admin_email}>";
    $headers[] = "Reply-To: {$params['etunimi']} <{$params['email']}>";
    $headers[] = "Content-Type: text/html"; 

    $subject = "Uusi viesti henkilöltä {$params['etunimi']} {$params['sukunimi']}";

    $message = '';
    $message .= "<h1>{$params['etunimi']} {$params['sukunimi']} haluaa liittyä sähköpostilistalle</h1>";

    foreach($params as $label => $value) 
        {
            $message .=  '<strong>' . ucfirst($label) . ': </strong>: ' . $value . '<br />';
        }

        wp_mail($admin_email, $subject, $message, $headers);

        return new WP_Rest_Response('The message was sent', 200);   

    }
