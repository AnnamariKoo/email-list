<?php if(get_plugin_options('email_list_plugin_active')):?>

<div id="form_success" class="form_notification"></div>

<form id="email_list_form">

    <?php wp_nonce_field('wp_rest'); ?>


    <label for="etunimi">Etunimi*</label>
    <input type="text" name="etunimi" required maxlength="50"></input>
    <label for="sukunimi">Sukunimi*</label>
    <input type="text" name="sukunimi" required maxlength="50"></input>

    <label for="email">Sähköposti*</label>
    <input type="text" id="email" name="email" required maxlength="254">

    <label for="organisaatio">Organisaatio*</label>
    <input type="text" name="organisaatio" required maxlength="100">
    <div class="button_wrapper">
        <div id="form_error" class="form_notification"></div>
        <p>* Tähdellä merkityt kentät ovat pakollisia</p>
        <button type="submit">Liity postituslistalle</button>
    </div>
</form>


<?php else:?>

<p>Lomake ei ole käytössä.</p>

<?php endif; ?>