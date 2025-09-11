<?php

if( !defined( 'ABSPATH' ) ) {
    die( 'You cannot be here' );
}

add_shortcode('email-list-form', 'show_email_list_form');

add_action('rest_api_init', 'create_rest_endpoint');

add_action('init', 'create_mailing_list_page');

add_action('add_meta_boxes', 'create_meta_box');

add_filter('manage_mailing_list_posts_columns', 'custom_mailing_list_columns');

add_action('manage_mailing_list_posts_custom_column', 'fill_custom_mailing_list_columns', 10, 2);

add_action('admin_init', 'setup_search');

add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function enqueue_custom_scripts() {
    wp_enqueue_style('email-list-plugin', MY_PLUGIN_URL . 'assets/css/email-list-plugin.css');
    wp_enqueue_script(
        'email-list-plugin',
        MY_PLUGIN_URL . 'assets/js/email-list-plugin.js',
        array(), // dependencies
        null,
        true
    );
    // Pass REST URL to JS
    wp_localize_script('email-list-plugin', 'emailListPluginRestUrl', array(
        'restUrl' => get_rest_url(null, 'v1/email-form/submit')
    ));
}

function setup_search() {

    global $typenow;

    if($typenow === 'mailing_list') {
        add_filter('posts_search', 'mailing_list_search_override', 10, 2);
    }
}

function mailing_list_search_override($search, $query) {

    global $wpdb;

    if($query->is_main_query() && !empty($query->query['s'])) {
        $sql  = " 
            or exists (
                select * from $wpdb->postmeta
                where post_id = $wpdb->posts.ID
                and meta_key in ('etunimi', 'sukunimi', 'email', 'organisaatio')
                and meta_value like %s 
            )
        ";
        $like = '%' . $wpdb->esc_like($query->query['s']) . '%';
        $search = preg_replace("#\({$wpdb->posts}.post_title LIKE [^)]+\)\K#", $wpdb->prepare($sql, $like), $search);
    }

    return $search;

}

function fill_custom_mailing_list_columns($column, $post_id) {

    switch($column) {

        case 'etunimi':
            echo esc_html(get_post_meta($post_id, 'etunimi', true));
            break;
        case 'sukunimi':
            echo esc_html(get_post_meta($post_id, 'sukunimi', true));
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'organisaatio':
            echo esc_html(get_post_meta($post_id, 'organisaatio', true));
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
        'menu_position' => 30,
        'publicly_queryable' => false,
        'labels'  => [
            'name' => 'Sähköpostilista', 
            'singular_name' => 'Sähköpostilista',
            'edit_item' => 'Henkilön tiedot'
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
    ob_start();
    include MY_PLUGIN_PATH . 'includes/templates/email-list-form.php';
    return ob_get_clean();
}

function create_rest_endpoint(){
    register_rest_route('v1/email-form', 'submit', array(

        'methods' => 'POST',
        'callback' => 'handle_enquiry',
    ));
}

function handle_enquiry($data){

    //Get all parameters from form
     $params = $data->get_params(); 

     //Set fields from form
     $field_etunimi = sanitize_text_field($params['etunimi']);
     $field_sukunimi = sanitize_text_field($params['sukunimi']);
     $field_email = sanitize_email($params['email']);

     if ( !isset($params['_wpnonce']) ||!wp_verify_nonce( $params['_wpnonce'], 'wp_rest' ) ) {
         return new WP_Rest_response('Message not sent', 422);
        }
        
    unset($params['_wpnonce']);
    unset($params['_wp_http_referer']);

    // Send the email message
    $headers = [];

    $admin_email = get_bloginfo('admin_email');
    $admin_name = get_bloginfo('name');


    //Set recipient email
    $recipient_email = get_plugin_options('email_list_plugin_recipients');

    if(!$recipient_email){
        $recipient_email = $admin_email;
    }

    $headers[] = "From: {$admin_name} <{$admin_email}>";
    $headers[] = "Reply-To: {$field_etunimi} <{$field_email}>";
    $headers[] = "Content-Type: text/html"; 

    $subject = "Uusi viesti henkilöltä {$field_etunimi} {$field_sukunimi}";

    $message = '';
    $message .= "<h1>{$field_etunimi} {$field_sukunimi} haluaa liittyä sähköpostilistalle</h1>";

    $postarr = [

        'post_title' => $field_etunimi . ' ' . $field_sukunimi,
        'post_type' => 'mailing_list',
        'post_status' => 'publish'
    ];

        $post_id = wp_insert_post($postarr);

    foreach($params as $label => $value) {

            switch($label) {
                case 'email':
                    $value = sanitize_email($value);
                break;

                case 'organisaatio':
                    $value = sanitize_text_field($value);
                    break;

                default:
                    $value = sanitize_text_field($value);
            }

            add_post_meta($post_id, sanitize_text_field($label), $value);

            $message .=  '<strong>' . sanitize_text_field(ucfirst($label)) . ': </strong>: ' . $value . '<br>';
        }


        wp_mail($recipient_email, $subject, $message, $headers);

        $confirmation_message = 'Kiitos ilmoittautumisesta postituslistalle!';

        if(get_plugin_options('email_list_plugin_message')) {
            $confirmation_message = get_plugin_options('email_list_plugin_message');
            $confirmation_message = str_replace('{etunimi}', $field_etunimi, $confirmation_message);
        }

        return new WP_Rest_Response($confirmation_message, 200);

    }
