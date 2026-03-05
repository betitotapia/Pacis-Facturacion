<?php
// dist/facturacion/pagos.php
session_start();
if (!isset($_SESSION['user_login_status']) || $_SESSION['user_login_status'] != 1) { header("location: ../login.php"); exit; }

include("../config/db.php");
include("../config/conexion.php");

$id = (int)($_GET['id'] ?? 0);
$ff = mysqli_query($con, "SELECT ff.*, c.nombre_cliente FROM fact_facturas ff
  LEFT JOIN clientes c ON c.id_cliente=ff.id_cliente
  WHERE ff.id_fact_facturas=$id LIMIT 1");
$fact = mysqli_fetch_assoc($ff);
if(!$fact){ die("Factura inválida"); }
if((int)$fact['status_factura'] !== 2 || empty($fact['uuid'])){ die("La factura debe estar timbrada para crear complemento de pago."); }

$pagos = mysqli_query($con, "SELECT * FROM fact_pagos WHERE id_fact_facturas=$id ORDER BY id_pago DESC");
?>
<!doctype html><html lang="es"><head>
<meta charset="utf-8"><title>Pagos / Complemento</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head><body class="p-3">
<div class="container">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h3 class="m-0">Pagos (Complemento) - Factura #<?php echo $id; ?></h3>
      <div class="text-muted"><?php echo htmlspecialchars($fact['nombre_cliente']??''); ?> — UUID: <span style="font-size:12px"><?php echo htmlspecialchars($fact['uuid']); ?></span></div>
    </div>
    <a class="btn btn-outline-secondary" href="facturas.php">Volver</a>
  </div>

  <div class="card mb-3">
    <div class="card-header">Registrar pago</div>
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-3">
          <label class="form-label">Fecha pago</label>
          <input class="form-control" id="fecha" value="<?php echo date('Y-m-d\TH:i'); ?>" type="datetime-local">
        </div>
        <div class="col-md-2">
          <label class="form-label">Forma pago</label>
          <input class="form-control" id="forma" value="03" placeholder="03">
        </div>
        <div class="col-md-2">
          <label class="form-label">Monto</label>
          <input class="form-control" id="monto" value="">
        </div>
        <div class="col-md-2">
          <label class="form-label">Parcialidad</label>
          <input class="form-control" id="parcialidad" value="1">
        </div>
        <div class="col-md-3 d-flex align-items-end">
          <button class="btn btn-primary w-100" id="btnGuardarPago">Guardar pago</button>
        </div>
      </div>
      <div class="form-text">Este pago generará un CFDI tipo <b>P</b> (Complemento de Pago) relacionado al UUID de la factura.</div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead class="table-dark">
        <tr><th>ID</th><th>Fecha</th><th>Forma</th><th class="text-end">Monto</th><th>Status</th><th>Acción</th></tr>
      </thead>
      <tbody>
        <?php while($p=mysqli_fetch_assoc($pagos)): ?>
          <tr>
            <td><?php echo (int)$p['id_pago']; ?></td>
            <td><?php echo htmlspecialchars($p['fecha_pago']); ?></td>
            <td><?php echo htmlspecialchars($p['forma_pago']); ?></td>
            <td class="text-end">$ <?php echo number_format((float)$p['monto'],2); ?></td>
            <td><?php echo (int)$p['status_pago']; ?></td>
            <td>
              <button class="btn btn-sm btn-success btnTimbrarPago" data-id="<?php echo (int)$p['id_pago']; ?>">Timbrar complemento</button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
async function post(url, data){
  const r = await fetch(url, {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:new URLSearchParams(data)});
  return await r.json();
}
document.getElementById('btnGuardarPago').addEventListener('click', async ()=>{
  const res = await post('ajax/guardar_pago.php', {
    id_fact: <?php echo $id; ?>,
    fecha: document.getElementById('fecha').value,
    forma: document.getElementById('forma').value,
    monto: document.getElementById('monto').value,
    parcialidad: document.getElementById('parcialidad').value,
  });
  if(res.ok){ alert('Pago guardado'); location.reload(); }
  else alert('Error: '+res.error);
});
document.querySelectorAll('.btnTimbrarPago').forEach(b=>{
  b.addEventListener('click', async ()=>{
    if(!confirm('¿Timbrar complemento de pago?')) return;
    const res = await post('ajax/timbrar_pago.php', { id_pago: b.dataset.id });
    if(res.ok){ alert('Timbrado UUID: '+res.uuid); location.reload(); }
    else alert('Error: '+(res.error||'')); 
  });
});
</script>
</body></html>
