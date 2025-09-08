<?php

add_shortcode('email-list-form', 'show_email_list_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_mailing_list_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_mailing_list_posts_columns', 'custom_mailing_list_columns');

add_action('manage_mailing_list_posts_custom_column', 'fill_custom_mailing_list_columns', 10, 2);

function fill_custom_mailing_list_columns($column, $post_id) {

    switch($column) {

        case 'etunimi':
            echo get_post_meta($post_id, 'etunimi', true);
            break;
        case 'sukunimi':
            echo get_post_meta($post_id, 'sukunimi', true);
            break;
        case 'email':
            echo get_post_meta($post_id, 'email', true);
            break;
        case 'organisaatio':
            echo get_post_meta($post_id, 'organisaatio', true);
            break;
    }

}

function custom_mailing_list_columns($columns){
    $columns = array(

        'cb' => $columns['cb'],
        'etunimi' => __('Etunimi', 'email-list-plugin'),
        'sukunimi' => __('Sukunimi', 'email-list-plugin'),
        'email' => __('Email', 'email-list-plugin'),
        'organisaatio' => __('Organisaatio', 'email-list-plugin'),
    );

    return $columns;
}

function create_meta_box(){

    add_meta_box('custom_email_list_form', 'Submission', 'display_submission', 'mailing_list');
}

function display_submission(){

    $post_metas = get_post_meta( get_the_ID());

    unset($post_metas['_edit_lock']);

    echo '<ul>';

    foreach ($post_metas as $key => $value) {

        echo '<li><strong>' . ucfirst($key) . '</strong>: <br>' . esc_html($value[0]) . '</li>';
    }

    echo '</ul>';


    }

function create_mailing_list_page(){
    $args = [

        'public' => true,
        'has_archive' => true,
        'labels'  => [
            'name' => 'Submissions', 
            'singular_name' => 'Submission'
        ],
        'supports' => false,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true
        
    ];

    register_post_type('mailing_list', $args);
}
function show_email_list_form() {
    include MY_PLUGIN_PATH . 'includes/templates/email-list-form.php';

}

function create_rest_endpoint(){
    register_rest_route('v1/email-form', 'submit', array(

        'methods' => 'POST',
        'callback' => 'handle_enquiry',
    ));
}

function handle_enquiry($data){
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

    $postarr = [
        
        'post_title' => $params['etunimi'] . ' ' . $params['sukunimi'],
        'post_type' => 'mailing_list',
        'post_status' => 'publish'
    ];

        $post_id = wp_insert_post($postarr);

    foreach($params as $label => $value) 
        {
            $message .=  '<strong>' . ucfirst($label) . ': </strong>: ' . $value . '<br />';

            add_post_meta($post_id, $label, $value);
        }


        wp_mail($admin_email, $subject, $message, $headers);

        return new WP_Rest_Response('The message was sent', 200);   

    }
