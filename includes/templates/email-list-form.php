<?php if(get_plugin_options('email_list_plugin_active')):?>

<div id="form_success" class="form_notification"></div>
<div id="form_error" class="form_notification"></div>

<form id="email_list_form">

    <?php wp_nonce_field('wp_rest'); ?>


    <label for="etunimi">Etunimi</label>
    <input type="text" name="etunimi"></input>
    <label for="sukunimi">Sukunimi</label>
    <input type="text" name="sukunimi">
    

    <label for="email">Sähköposti</label>
    <input type="text" id="email" name="email" required>

    <label for="organisaatio">Organisaatio</label>
    <input type="text" name="organisaatio">

    <button type="submit">Liity postituslistalle</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('email_list_form').addEventListener('submit', function(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const formObj = Object.fromEntries(formData);
        const errorDiv = document.getElementById('form_error');
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        for (const [key, value] of Object.entries(formObj)) {
            if (key === '_wpnonce' || key === '_wp_http_referer') continue;

            if (!value.trim() || value.trim().length < 2) {
                errorDiv.textContent = `Tarkista ${key}!`;
                errorDiv.style.display = 'block';
                return; // Exits the submit handler!
            }
            // Optionally clear error here if needed

            if (key === "email" && !emailPattern.test(value)) {
                errorDiv.textContent = 'Syötä kelvollinen sähköpostiosoite!';
                errorDiv.style.display = 'block';
                return; // Exits the submit handler!
                
            }
        }
        errorDiv.textContent = '';
        errorDiv.style.display = 'none';

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
            if (!response.ok) {
                const errorDiv = document.getElementById('form_error');
                errorDiv.textContent = 'Viestiä ei lähetetty. Yritä uudelleen';
                errorDiv.style.display = 'block';
                return;
            }
                return response.json(); // <-- get plain text response
        })
        .then(data => {
            if (!data) return; // If previous .then returned nothing (error)
            form.style.display = 'none';
            const successDiv = document.getElementById('form_success');
            successDiv.textContent = data; // <-- show API response
            successDiv.style.display = 'block';
            successDiv.style.opacity = 0;
            setTimeout(() => {
                successDiv.style.transition = 'opacity 0.2s';
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