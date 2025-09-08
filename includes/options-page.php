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
    Container::make( 'theme_options', __( 'Sähköpostilistan asetukset' ) )
        ->set_icon('dashicons-email')
        ->add_fields( array(

            Field::make( 'checkbox', 'email_list_plugin_active', __( 'Aktivoi sähköpostilista' ) ),

            Field::make( 'text', 'email_list_plugin_recipients', __( 'Vastaanottajan sähköposti' ) ) 
            ->set_attribute( 'placeholder', 'vastaanottaja@email.com' )
            ->set_help_text('Sähköpostiosoite, johon lomakkeen tiedot lähetetään'),

            Field::make( 'textarea', 'email_list_plugin_message', __( 'Vahvistusviesti' ) )
            ->set_attribute( 'placeholder', 'Kirjoita vahvistusviesti' )
            ->set_help_text('Vahvistusviesti, joka lähetetään lomakkeen lähettäjälle'),

    ) );
}