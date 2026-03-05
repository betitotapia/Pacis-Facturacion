<?php
	error_reporting(E_ALL ^ E_NOTICE);
	session_start();
	if (!isset($_SESSION['user_login_status']) AND $_SESSION['user_login_status'] != 1) {
        header("location: ../../login.php");
		exit;
    }
	ini_set('display_errors', 1);
	
	/* Connect To Database*/
	include("../config/db.php");
	include("../config/conexion.php");

	//Archivo de funciones PHP
	include("../pages/funciones.php");
	
	//FIn Variables GET
	
	//Variables por GET
	$numero_factura=intval($_GET['numero_factura']);
	$id_cliente=intval($_GET['id_cliente']);
	$id_vendedor=intval($_GET['id_vendedor']);
	$letra_ventas=($_GET['letra_ventas']);
	$fecha=($_GET['fecha']);
	$compra=($_GET['compra']);
	$cotizacion=($_GET['cotizacion']);
	$doctor=($_GET['doctor']);
	$paciente=($_GET['paciente']);
	$material=($_GET['material']);
	$pago=($_GET['pago']);
	$d_factura=($_GET['d_factura']);
	$observaciones=($_GET['observaciones']);

	$session_id= session_id();
	$sql_count=mysqli_query($con,"SELECT * FROM detalle_factura WHERE numero_factura = '".$numero_factura."' AND id_vendedor = '".$id_vendedor."'");
	$count=mysqli_num_rows($sql_count);
	if ($count==0)
	{
	echo "<script>alert('No hay productos agregados a la factura')</script>";
	echo "<script>window.close();</script>";
	exit;
	}
	
	
	//-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------$condiciones=mysqli_real_escape_string($con,(strip_tags($_REQUEST['condiciones'], ENT_QUOTES)));
	//Fin de variables por GET
	

	$simbolo_moneda="$"; // Simbolo de la moneda, se puede cambiar por el que se necesite
    // GET the HTML
    include(dirname('__FILE__').'/res/remision_html.php');
   ?>
   <input type="hidden" id="letra" value="<?php echo $letra_ventas  ?>" >
   <input type="hidden" id="numero_factura" value="<?php echo $numero_factura  ?>" >
<script>
	letra=document.GETElementById("letra").value;
	factura=document.GETElementById("numero_factura").value;
	toPdf(letra,factura)
</script>


