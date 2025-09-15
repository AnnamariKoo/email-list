<?php

if( !defined( 'ABSPATH' ) ) {
    die( 'You cannot be here' );
}

use Carbon_Fields\Field;
use Carbon_Fields\Container;

add_action('after_setup_theme', 'load_carbon_fields');
add_action('carbon_fields_register_fields', 'create_options_page');

function load_carbon_fields(): void
{
    \Carbon_Fields\Carbon_Fields::boot();
}

function create_options_page(): void
{
    Container::make( 'theme_options', __( 'Email List options' ) )
        ->set_page_menu_position( 30 )
        ->set_icon('dashicons-email')
        ->add_fields( array(

            Field::make( 'checkbox', 'email_list_plugin_active', __( 'Activate email list' ) ),

            Field::make( 'text', 'email_list_plugin_recipients', __( 'Recipient email' ) ) 
            ->set_attribute( 'placeholder', 'recipient@email.com' )
            ->set_help_text('Email address to which the form data will be sent'),

            Field::make( 'textarea', 'email_list_plugin_message', __( 'Confirmation Message' ) )
            ->set_attribute( 'placeholder', 'Enter confirmation message' )
            ->set_help_text('Confirmation message displayed to the form sender'),

    ) );
}