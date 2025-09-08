<?php if(get_plugin_options('email_list_plugin_active')):?>

<div id="form_succes"></div>
<div id="form_error"></div>

<form id="enquiry_form">

    <?php wp_nonce_field('wp_rest'); ?>

    <div id="kokonimi">
        <label for="etunimi">Etunimi</label>
        <input type="text" name="etunimi">
        <label for="sukunimi">Sukunimi</label>
        <input type="text" name="sukunimi">
    </div>
    

    <label for="email">Sähköposti</label>
    <input type="text" name="email">

    <label for="organisaatio">Organisaatio</label>
    <input type="text" name="organisaatio">

    <button type="submit">Liity postituslistalle</button>
</form>

<script>

    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('enquiry_form').addEventListener('submit', function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);

        // Convert FormData to URL-encoded string
        const params = new URLSearchParams();
        for (const pair of formData) {
            params.append(pair[0], pair[1]);
        }

        fetch("<?php echo get_rest_url(null, 'v1/email-form/submit'); ?>", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: params.toString()
        })
        .then(response => {
            console.log("response", response);
            if (!response.ok) {
                const errorDiv = document.getElementById('form_error');
                errorDiv.textContent = 'Viestiä ei lähetetty. Yritä uudelleen';
                errorDiv.style.display = 'block';
                return;
            }

            // Hide the form
            form.style.display = 'none';
            // Show the success message
            const successDiv = document.getElementById('form_succes');
            successDiv.textContent = "Lähetys onnistui!";
            successDiv.style.display = 'block';
            successDiv.style.opacity = 0;
            setTimeout(() => {
                successDiv.style.transition = 'opacity 0.5s';
                successDiv.style.opacity = 1;
            }, 10);
        })
        .catch(error => {
            console.log("error", error);
            const errorDiv = document.getElementById('form_error');
            errorDiv.textContent = 'Viestiä ei lähetetty. Yritä uudelleen';
            errorDiv.style.display = 'block';
            console.error('Error:', error);
        });
    });
});

</script>

<?php else:?>

<p>Lomake ei ole käytössä.</p>

<?php endif; ?>