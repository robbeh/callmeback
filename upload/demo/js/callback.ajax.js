jQuery( document ).ready(function( $ ) {

    var form = $('#callback_form');

    $('#submit').removeAttr('disabled');
    $(form).submit(function (e) {

        $(".alert-danger").slideUp(800,function() {
            $('#submit').attr('disabled','disabled');
            e.preventDefault();
            $.ajax({
                type: "POST",
                url: form.attr( 'action' ),
                data: form.serialize(),
                dataType: "json",
                success: function (data) {

                    if (data.status == 'error') {
                        $('.alert-danger').slideDown();
                        $('#error ul').empty();
                        $.each(data.response, function(i, item) {
                            $("#error ul").append('<li>'+item+'</li>');
                        });
                        $('#submit').removeAttr('disabled');
                        return;
                    }

                    $("#callback_form").slideUp(800,function() {
                        $('.alert-success').slideDown();
                        $("#success").append(data.response);


                    });
                },
                error: function () {
                    alert('Error.');
                }
            });


        });

        return false;

    });
});