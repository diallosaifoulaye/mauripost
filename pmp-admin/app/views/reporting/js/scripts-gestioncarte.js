$(function() {
    window.setTimeout(function() {
        $(".alert").fadeTo(1500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 3000);
});

$(function() {
    $("#email").blur(function () {
        var email = $(this).val();
        if (email == '') {
            $("#availability").html("");
        }
        else{
            $.ajax({
                url: "validation.php?email="+email
            }).done(function( data ) {
                $("#availability").html(data);
            });
        }
    });
});

$(function() {
    $("#piece").blur(function () {
        var piece = $(this).val();
        if (piece == '') {
            $("#availability2").html("");
        }
        else{
            $.ajax({
                url: "validation.php?piece="+piece
            }).done(function( data ) {
                $("#availability2").html(data);
            });
        }
    });
});

$(function() {
    $("#codeben").blur(function () {
        var codeben = $(this).val();
        if (codeben == '') {
            $("#availabilitycode").html("");
        }
        else{
            $.ajax({
                url: "validation.php?codeben="+codeben
            }).done(function( data ) {
                $("#availabilitycode").html(data);
            });
        }
    });
});

$(function() {
    $("#numeroserie").blur(function () {
        var numeroserie = $(this).val();
        if (numeroserie == '') {
            $("#availabilitynumeroserie").html("");
        }
        else{
            $.ajax({
                url: "validation.php?numeroserie="+numeroserie
            }).done(function( data ) {
                $("#availabilitynumeroserie").html(data);
            });
        }
    });
});

$(function() {
    $("#numero").blur(function () {
        var numero = $(this).val();
        if (numero == '') {
            $("#availabilitynumero").html("");
        }
        else{
            $.ajax({
                url: "validation.php?numero="+numero
            }).done(function( data ) {
                $("#availabilitynumero").html(data);
            });
        }
    });
});

$(function() {
    $("#telephone").blur(function () {
        var telephone = $(this).val();
        if (telephone == '') {
            $("#availabilitytelephone").html("");
        }
        else{
            $.ajax({
                url: "validation.php?telephone="+telephone
            }).done(function( data ) {
                $("#availabilitytelephone").html(data);
            });
        }
    });
});
