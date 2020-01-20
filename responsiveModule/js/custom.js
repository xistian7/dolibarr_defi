/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$(document).ready(function(){
    $('.botoDelete').click(function(e){
        e.preventDefault();
        var clickBtnValue = $(this).val();
        $.ajax({ 
            type: "POST",
            url: './../../basic_lib/pedido_proveedor.php',
            data: {
                action: 'deleteLinePedido',
                idLineaRebut: clickBtnValue
            },
            success: function(output) {
                //alert(output);
                $( "#dispatch_received_products" ).load( window.location.href+" #dispatch_received_products" );
            }
        });
    });
    
    $(document).on("click", ".open-intervencioModal", function () {
        var intervencioID = $(this).data('id');
        var userid = $(this).data('id');

        // AJAX request
        $.ajax({
         url: '../../basic_lib/view/intervencio.php',
         type: 'post',
         data: {intervencioid: intervencioID},
         success: function(response){ 
           // Add response in Modal body
           $('.modal-body').html(response);

           // Display Modal
           $('#myModal1').modal('show'); 
         }
       });
        /*$(".modal-title").val( "hola" );
        // As pointed out in comments, 
        // it is unnecessary to have to manually call the modal.
        $('#myModal1').modal('show');*/
   });
    $(document).on("click", ".btn-outline-danger", function (e) {
        e.preventDefault();
        var lineacontracte = $(this).data('lineacontracte');
        if(lineacontracte > 0){
            var opcion = confirm("Estas segur que vols facturar aques servei?");
            if (opcion == true) {
                $.ajax({
                    url: '../../basic_lib/view/servei.php',
                    type: 'post',
                    data: {lineacontracte: lineacontracte},
                    success: function(){ 
                        // Display Modal
                        location.reload();
                    }
                });
            }
        }
    });
});

