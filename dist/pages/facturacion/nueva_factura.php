<?php
// dist/facturacion/nueva_factura.php
session_start();
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) {
  header("location: ../login.php");
  exit;
}

include("../config/db.php");
include("../config/conexion.php");
include("../pages/funciones.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_remision = isset($_GET['id_remision']) ? (int)$_GET['id_remision'] : 0;

$fact = null;
if($id>0){
  $q = mysqli_query($con, "SELECT * FROM fact_facturas WHERE id_fact_facturas=".$id." LIMIT 1");
  $fact = mysqli_fetch_assoc($q);
} else {
  // crear borrador
  $no = (int)get_row('consecutivos','factura','id_consecutivo',1);
  $id_vendedor = (int)($_SESSION['user_id'] ?? 0);

  $sql = "INSERT INTO fact_facturas (id_remision, no_fact_factura, id_cliente, id_vendedor, total_factura, status_factura, validacion, date_created)
          VALUES (".($id_remision>0?$id_remision:"NULL").", $no, 0, $id_vendedor, 0, 0, 0, NOW())";
  mysqli_query($con, $sql);
  $id = (int)mysqli_insert_id($con);

  // incrementar consecutivo
  mysqli_query($con, "UPDATE consecutivos SET factura=factura+1 WHERE id_consecutivo=1");

  // si viene desde remisión, copiar detalle remisión -> detalle_fact_factura
  if($id_remision>0){
    // tu remisión usa facturas.numero_factura
    $rq = mysqli_query($con, "SELECT * FROM facturas WHERE numero_factura=".$id_remision." LIMIT 1");
    if($rw = mysqli_fetch_assoc($rq)){
      mysqli_query($con, "UPDATE fact_facturas SET id_cliente=".(int)$rw['id_cliente']." WHERE id_fact_facturas=".$id);
      $dq = mysqli_query($con, "SELECT id_producto, cantidad, precio_venta, id_almacen, id_vendedor
        FROM detalle_factura WHERE numero_factura=".$id_remision);
      while($d = mysqli_fetch_assoc($dq)){
        $pid=(int)$d['id_producto'];
        $p = mysqli_query($con, "SELECT referencia FROM products WHERE id_producto=".$pid." LIMIT 1");
        $pr = mysqli_fetch_assoc($p);
        $cve = $pr['referencia'] ?? '';
        $tipo = 'P';
        mysqli_query($con, "INSERT INTO detalle_fact_factura
          (numero_fact_factura, id_producto, cantidad, precio_venta, id_almacen, id_vendedor, cve_producto, tipo_producto, date_created)
          VALUES (".$id.", ".$pid.", ".(float)$d['cantidad'].", ".(float)$d['precio_venta'].", ".(int)$d['id_almacen'].", ".(int)$d['id_vendedor'].",
                  '".mysqli_real_escape_string($con,$cve)."', '".mysqli_real_escape_string($con,$tipo)."', NOW())");
      }
    }
  }

  header("Location: nueva_factura.php?id=".$id);
  exit;
}

// cargar clientes para combo
$clientes = mysqli_query($con, "SELECT id_cliente, nombre_cliente, rfc FROM clientes ORDER BY nombre_cliente ASC LIMIT 2000");

// items
$items = mysqli_query($con, "SELECT d.*, p.referencia, p.descripcion
  FROM detalle_fact_factura d
  INNER JOIN products p ON p.id_producto = d.id_producto
  WHERE d.numero_fact_factura=".$id."
  ORDER BY d.id_detalle_fact ASC
");

?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Factura CFDI - Borrador</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body class="p-3">
<div class="container">

  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="m-0">Factura CFDI (borrador) #<?php echo (int)$id; ?></h3>
      <div class="text-muted">Status: <?php echo (int)($fact['status_factura'] ?? 0); ?></div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="facturas.php">Volver</a>
      <button class="btn btn-success" id="btnTimbrar">Timbrar CFDI</button>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header">Datos del receptor (cliente)</div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-6">
          <label class="form-label">Cliente</label>
          <select class="form-select" id="id_cliente">
            <option value="0">Selecciona…</option>
            <?php while($c = mysqli_fetch_assoc($clientes)): ?>
              <option value="<?php echo (int)$c['id_cliente']; ?>" <?php echo ((int)$c['id_cliente']==(int)($fact['id_cliente']??0))?'selected':''; ?>>
                <?php echo htmlspecialchars($c['nombre_cliente']." (".$c['rfc'].")"); ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Método de pago</label>
          <select class="form-select" id="metodo_pago">
            <option value="PUE">PUE</option>
            <option value="PPD">PPD</option>
          </select>
          <div class="form-text">Si es PPD, luego podrás timbrar Complementos de Pago.</div>
        </div>
        <div class="col-md-3">
          <label class="form-label">Forma de pago (si PUE)</label>
          <input class="form-control" id="forma_pago" placeholder="Ej: 03" value="03">
        </div>

        <div class="col-md-3">
          <label class="form-label">Uso CFDI</label>
          <input class="form-control" id="uso_cfdi" placeholder="Ej: G03" value="G03">
        </div>
        <div class="col-md-3">
          <label class="form-label">Serie</label>
          <input class="form-control" id="serie" value="<?php echo htmlspecialchars($fact['serie'] ?? 'A'); ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label">Folio</label>
          <input class="form-control" id="folio" value="<?php echo htmlspecialchars($fact['folio'] ?? ''); ?>">
        </div>
      </div>

      <div class="mt-3">
        <button class="btn btn-primary" id="btnGuardarHeader">Guardar datos</button>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between">
      <span>Conceptos</span>
      <a class="btn btn-sm btn-outline-primary" href="seleccionar_producto.php?id=<?php echo (int)$id; ?>">Agregar producto</a>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-sm m-0">
          <thead class="table-light">
            <tr>
              <th>Referencia</th>
              <th>Descripción</th>
              <th class="text-end">Cantidad</th>
              <th class="text-end">Precio</th>
              <th class="text-end">Importe</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php $sum=0; while($it = mysqli_fetch_assoc($items)): $imp=(float)$it['cantidad']*(float)$it['precio_venta']; $sum+=$imp; ?>
              <tr>
                <td><?php echo htmlspecialchars($it['referencia']); ?></td>
                <td><?php echo htmlspecialchars($it['descripcion']); ?></td>
                <td class="text-end"><?php echo number_format((float)$it['cantidad'],2); ?></td>
                <td class="text-end"><?php echo number_format((float)$it['precio_venta'],2); ?></td>
                <td class="text-end"><?php echo number_format($imp,2); ?></td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-danger btnDel" data-id="<?php echo (int)$it['id_detalle_fact']; ?>">X</button>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
    <div class="card-footer text-end">
      <strong>Subtotal (sin IVA calc):</strong> $ <?php echo number_format($sum,2); ?>
    </div>
  </div>

</div>

<script>
async function post(url, data){
  const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams(data)});
  return await r.json();
}

document.getElementById('btnGuardarHeader').addEventListener('click', async ()=>{
  const res = await post('ajax/guardar_factura_header.php', {
    id: <?php echo (int)$id; ?>,
    id_cliente: document.getElementById('id_cliente').value,
    metodo_pago: document.getElementById('metodo_pago').value,
    forma_pago: document.getElementById('forma_pago').value,
    uso_cfdi: document.getElementById('uso_cfdi').value,
    serie: document.getElementById('serie').value,
    folio: document.getElementById('folio').value,
  });
  alert(res.ok ? 'Guardado' : ('Error: '+res.error));
});

document.getElementById('btnTimbrar').addEventListener('click', async ()=>{
  if(!confirm('¿Timbrar CFDI ahora?')) return;
  const res = await post('ajax/timbrar_factura.php', { id: <?php echo (int)$id; ?> });
  if(res.ok){
    alert('Timbrada. UUID: '+res.uuid);
    location.href = 'nueva_factura.php?id=<?php echo (int)$id; ?>';
  } else {
    alert('Error timbrado: ' + (res.error || ''));
  }
});

document.querySelectorAll('.btnDel').forEach(btn=>{
  btn.addEventListener('click', async ()=>{
    if(!confirm('¿Eliminar concepto?')) return;
    const res = await post('ajax/eliminar_item.php', { id_detalle: btn.dataset.id });
    if(res.ok) location.reload();
    else alert('Error: '+res.error);
  });
});
</script>
</body>
</html>
