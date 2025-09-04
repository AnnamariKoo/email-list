<form id="enquiry_form">

    <label for="etunimi">Etunimi</label>
    <input type="text" name="etunimi">
    
    <label for="sukunimi">Sukunimi</label>
    <input type="text" name="sukunimi">

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
        alert('test');
        // event.preventDefault(); // Uncomment if you want to prevent form submission
    });
});

</script>