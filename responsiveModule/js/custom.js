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
});

