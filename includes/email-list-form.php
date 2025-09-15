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

add_filter('views_edit-mailing_list', function($views) {
    // Change "All" to "Registerations"
    if (isset($views['all'])) {
        $views['all'] = str_replace('All', 'Registrations', $views['all']);
    }
    // Remove the "Published" view
    if (isset($views['publish'])) {
        unset($views['publish']);
    }
    // Change "Draft" to "Restored from Trash"
    if (isset($views['draft'])) {
        $views['draft'] = str_replace('Draft', 'Restored from Trash', $views['draft']);
    }
    return $views;
});

// AJAX handler for updating "read" status
add_action('wp_ajax_update_mailing_list_read', function() {
    if (
        !current_user_can('edit_posts') ||
        !isset($_POST['post_id'], $_POST['read'], $_POST['_wpnonce']) ||
        !wp_verify_nonce($_POST['_wpnonce'], 'mailing_list_read')
    ) {
        wp_send_json_error();
    }

    $post_id = intval($_POST['post_id']);
    $read = $_POST['read'] === '1' ? '1' : '0';

    update_post_meta($post_id, 'read', $read);

    wp_send_json_success();
});

// Remove "View" action from mailing list entries in admin
add_filter('post_row_actions', function($actions, $post) {
    if ($post->post_type === 'mailing_list') {
        // Keep only Edit and Trash
        $allowed = ['trash', 'edit'];
        foreach ($actions as $key => $value) {
            if (!in_array($key, $allowed)) {
                unset($actions[$key]);
            }
        }
    }
    return $actions;
}, 10, 2);

// Remove bulk actions except Trash
add_filter('bulk_actions-edit-mailing_list', function($actions) {
    // Only keep 'trash' in the bulk actions
    if (isset($actions['edit'])) {
        unset($actions['edit']);
    }
    return $actions;
});

// Enqueue admin scripts and styles for mailing list post type
add_action('admin_enqueue_scripts', function($hook) {
    if ($hook === 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'mailing_list') {
        wp_enqueue_script('mailing-list-admin', MY_PLUGIN_URL . 'assets/js/mailing-list-admin.js', [], null, true);
        wp_enqueue_style('mailing-list-admin', MY_PLUGIN_URL . 'assets/css/mailing-list-admin.css');
    }
});

// Enqueue script for handling "read" checkbox in admin list view
add_action('admin_enqueue_scripts', function() {
    wp_enqueue_script('mailing-list-read', MY_PLUGIN_URL . 'assets/js/mailing-list-read.js', [], null, true);
    wp_localize_script('mailing-list-read', 'mailingListReadNonce', wp_create_nonce('mailing_list_read'));
});

// Enqueue CSS and JS files
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

// Setup search to include custom fields
function setup_search() {

    global $typenow;

    if($typenow === 'mailing_list') {
        add_filter('posts_search', 'mailing_list_search_override', 10, 2);
    }
}

// Modify search to include custom fields
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
// Fill custom columns in admin list view (Sähköpostilista)
function fill_custom_mailing_list_columns($column, $post_id) {
    switch($column) {
        case 'etunimi':
            echo esc_html(get_post_meta($post_id, 'etunimi', true));
            break;
        case 'sukunimi':
            echo esc_html(get_post_meta($post_id, 'sukunimi', true));
            break;
        case 'organisaatio':
            echo esc_html(get_post_meta($post_id, 'organisaatio', true));
            break;
        case 'paivamaara':
            $date = get_the_date('d.m.Y H:i', $post_id);
             echo esc_html($date);
            break;
        case 'email':
            echo esc_html(get_post_meta($post_id, 'email', true));
            break;
        case 'read':
            $is_read = get_post_meta($post_id, 'read', true);
            echo '<input type="checkbox" class="mailing-list-read" data-id="' . esc_attr($post_id) . '" data-read="' . esc_attr($is_read) . '" ' . checked($is_read, '1', false) . ' />';
            break;
    }
}

// Customize columns in admin list view (sähköpostilista)
function custom_mailing_list_columns($columns){
    $columns = array(

        'cb' => $columns['cb'],
        'etunimi' => __('First Name', 'email-list-plugin'),
        'sukunimi' => __('Last Name', 'email-list-plugin'),
        'organisaatio' => __('Organization', 'email-list-plugin'),
        'paivamaara' => __('Date', 'email-list-plugin'),
        'email' => __('Email', 'email-list-plugin'),
        'read' => __('Added to Mailing List', 'email-list-plugin')
    );

    return $columns;
}

// Create meta box for viewing submission details
function create_meta_box(){

    add_meta_box('custom_email_list_form', 'Submission', 'display_submission', 'mailing_list');
}

// Format submission details for admin list view (sähköpostilista)
function display_submission(){

    $post_metas = get_post_meta( get_the_ID());

    unset($post_metas['_edit_lock']);

    echo '<ul>';

    foreach ($post_metas as $key => $value) {

        echo '<li><strong>' . ucfirst($key) . '</strong>: <br>' . esc_html($value[0]) . '</li>';
    }

    echo '</ul>';


    }

// Create custom post type for mailing list entries
function create_mailing_list_page(){
    $args = [

        'public' => true,
        'has_archive' => true,
        'menu_position' => 30,
        'publicly_queryable' => false,
        'labels'  => [
            'name' => 'Email List', 
            'singular_name' => 'Email List',
            'edit_item' => 'Registeration Info'
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

// Display the email list form via shortcode
function show_email_list_form() {
    ob_start();
    include MY_PLUGIN_PATH . 'includes/templates/email-list-form.php';
    return ob_get_clean();
}

// Create REST API endpoint for form submission
function create_rest_endpoint(){
    register_rest_route('v1/email-form', 'submit', array(

        'methods' => 'POST',
        'callback' => 'handle_enquiry',
    ));
}


// Handle form submission and send email
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

    // if(!$recipient_email){
    //     $recipient_email = $admin_email;
    // }

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

    if (isset($recipient_email)) {
        wp_mail($recipient_email, $subject, $message, $headers);
    }

    $confirmation_message = 'Kiitos ilmoittautumisesta postituslistalle!';

    if(get_plugin_options('email_list_plugin_message')) {
        $confirmation_message = get_plugin_options('email_list_plugin_message');
        $confirmation_message = str_replace('{etunimi}', $field_etunimi, $confirmation_message);
    }

    return new WP_Rest_Response($confirmation_message, 200);

}
