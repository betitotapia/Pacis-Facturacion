$(document).ready(function(){
    load(1);
    
});

function load(page){
    var q= $("#q").val();
    var spinner= document.getElementById("spinner");
    $("#loader").fadeIn('slow');
    $.ajax({
        url:'../../ajax/buscar_remisiones.php?action=ajax&page='+page+'&q='+q,
         beforeSend: function(objeto){
        spinner.display='block';
      },    
        success:function(data){
            $(".outer_div").html(data).fadeIn('slow');
            
            spinner.style.display='none';
            $('[data-toggle="tooltip"]').tooltip({html:true}); 
            
        }
    })
}



    function eliminar (id)
{
    var q= $("#q").val();
if (Swal.fire({
    title: 'CANCELAR REMISION',
    text: "Realmente deseas cancelar esta remision?",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Aceptar',
    cancelButtonText: 'Cancelar'
    }).then((result) => {
    if (result.isConfirmed) {   
$.ajax({
type: "GET",
url: "../../ajax/buscar_remisiones.php",
data: "id="+id,"q":q,
 beforeSend: function(objeto){
    $("#resultados").html("Mensaje: Cargando...");
  },
success: function(datos){
$("#resultados").html(datos);
load(1);
 }
                }); //
            }
    })) ;                        
         }
         
// function descargar(id_factura, numero_factura){
//     VentanaCentrada('./pdf/documentos/ver_factura_excel.php?id_factura='+id_factura+'&numero_factura='+numero_factura);
// }
function imprimir_factura(id_factura, numero_factura, id_vendedor){
    VentanaCentrada('../../pdf/descargar_remision.php?id_factura='+id_factura+'&numero_factura='+numero_factura+'&id_vendedor='+id_vendedor);
}
function ver_factura(id_factura, numero_factura, id_vendedor){
    VentanaCentrada('../../pdf/ver_remision.php?id_factura='+id_factura+'&numero_factura='+numero_factura+'&id_vendedor='+id_vendedor);
}



function notificaciones(tipo){
// Push.create('SUMED', {
// 	body: tipo,
// 	icon: 'icon.png',
// 	timeout: 8000,               // Timeout before notification closes automatically.
// 	vibrate: [100, 100, 100],    // An array of vibration pulses for mobile devices.
// 	onClick: function() {
// 		// Callback for when the notification is clicked. 
// 		console.log(this);
// 	}  
// });
}

function imprimir_directo(id_factura, numero_factura, id_vendedor){
    VentanaCentrada(
        '../../pdf/imprimir_remision.php'
        + '?id_factura=' + id_factura
        + '&numero_factura=' + numero_factura
        + '&id_vendedor=' + id_vendedor
    );
}
function crear_factura(id_factura){
  Swal.fire({
    title: 'Convertir remisión a factura',
    text: 'Se generará una factura CFDI en borrador a partir de esta remisión terminada.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Sí, convertir',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if(!result.isConfirmed) return;

    $.ajax({
      type: "POST",
      url: "../../ajax/convertir_remision_a_cfdi.php",
      data: { id_factura: id_factura },
      success: function(resp){
        try{
          const r = JSON.parse(resp);
          if(!r.ok){
            Swal.fire('No se pudo', r.msg || 'Error', 'error');
            return;
          }
          window.location.href = "../facturacion/cfdi_ver.php?id=" + r.id_cfdi_factura;
        }catch(e){
          console.error(resp);
          Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
        }
      },
      error: function(){
        Swal.fire('Error', 'No se pudo conectar con el servidor', 'error');
      }
    });
  });
}
