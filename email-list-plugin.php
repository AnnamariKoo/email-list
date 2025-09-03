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

        require_once( MY_PLUGIN_PATH . 'vendor/autoload.php' );
    }

    public function initialize()
    {
        include_once MY_PLUGIN_PATH . 'includes/utilities.php';
        
        include_once MY_PLUGIN_PATH . 'includes/options-page.php';
    }

}

$emailListPlugin = new EmailListPlugin;

$emailListPlugin->initialize();

}
