<?php

add_shortcode('email-list-form', 'show_email_list_form');

function show_email_list_form() 
{
    include MY_PLUGIN_PATH . 'includes/templates/email-list-form.php';

}
