<form id="enquiry_form">

    <?php wp_nonce_field('wp_rest'); ?>

    <div class="kokonimi">
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

    // jQuery(document).ready(function($){

    //     $("#enquiry_form").submit( function(event){

    //         alert('test')

    //     });
    // });
    document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('enquiry_form').addEventListener('submit', function(event) {
        event.preventDefault(); // Uncomment if you want to prevent form submission
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
        .then(response => response.json())
        .then(data => {
            // Handle response
            console.log(data);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});

</script>