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
    $('.modal').modal();
    Materialize.updateTextFields();
    $('.tooltipped').tooltip({
        delay: 50,
        html: true,
        position: 'bottom'
    });
});
$(document).ready(function () {
    var $card_expandable = $('.card-expandable');
    var height = 400;
    $card_expandable.each(function () {
        var $value = $(this);
        if ($value.height() >= height) {
            var $row = $('<div class="row"><div class="col-xs-12 end-xs"><i class="right mdi mdi-chevron-down"></i></div></div>');
            var $chevron = $row.find('.mdi-chevron-down').first();
            $chevron.click(function () {
                var $this = $(this);
                $this.toggleClass('mdi-chevron-down');
                $this.toggleClass('mdi-chevron-up');
                var $ex = $this.parents('.card-content').first();
                $ex.toggleClass('active');
                if ($ex.hasClass('active')) {
                    $ex.css('height', '100%');
                } else {
                    $ex.stop().animate({
                        height: height
                    }, 500);
                }
            });
            var $title = $value.find('.card-title').first();
            $title.append($row);
            $value.find('.card-content').css('height', height);
        }
    });
});