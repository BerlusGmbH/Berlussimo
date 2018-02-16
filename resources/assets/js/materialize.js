window.$ = window.jQuery = require("jquery");
require("materialize-css");

$(document).ready(function () {
    $('select').material_select();
    $('.materialboxed').materialbox();
    $('.datepicker').pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15 // Creates a dropdown of 15 years to control year
    });
    $('.modal').modal();
    window.Materialize.updateTextFields();
    $(".dropdown-button").dropdown({hover: true, belowOrigin: true});
});