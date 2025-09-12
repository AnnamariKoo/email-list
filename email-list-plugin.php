<?php

/**
 * Plugin Name: Email List Plugin
 * Description: A simple email list plugin.
 * Version: 1.0.0
 * Author: Annamari Kuittinen
 * 
 */

if( !defined( 'ABSPATH' ) ) {
    die( 'You cannot be here' );
}

if(!class_exists('EmailListPlugin')) {
class EmailListPlugin {

    public function __construct()
    {
        define('MY_PLUGIN_PATH', plugin_dir_path( __FILE__ ));
        define('MY_PLUGIN_URL', plugin_dir_url( __FILE__ ));

        require_once( MY_PLUGIN_PATH . 'vendor/autoload.php' );
    }

    public function initialize() {
        $files = [
            'includes/utilities.php',
            'includes/options-page.php',
            'includes/email-list-form.php'
        ];
        foreach($files as $file) {
            $full_path = MY_PLUGIN_PATH . $file;
            if (file_exists($full_path) && is_readable($full_path)) {
                include_once $full_path;
            } else {
                error_log("EmailListPlugin: Could not load file: " . $file);
            }
        }
    }

}

$emailListPlugin = new EmailListPlugin;

$emailListPlugin->initialize();

}
