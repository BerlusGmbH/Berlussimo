$(document).ready(function() {
    $('select').material_select();
    $(".button-collapse").sideNav({
        edge: 'right'
    });
    $('.materialboxed').materialbox();
    $('.datepicker').pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15 // Creates a dropdown of 15 years to control year
    });
    $('.modal-trigger').leanModal();
    Materialize.updateTextFields();
});