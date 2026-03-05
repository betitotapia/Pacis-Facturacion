<?php
// dist/facturacion/config/facturama_config.php
// Configura tus credenciales (Sandbox/Producción) y datos del emisor.
// IMPORTANTE: NO subas tus credenciales a GitHub.

return [
  'sandbox' => true, // true = sandbox, false = producción

  // Credenciales Facturama (API Web)
  'user' => 'TU_USUARIO_FACTURAMA',
  'password' => 'TU_PASSWORD_FACTURAMA',

  // Emisor (tu RFC único)
  'issuer' => [
    'Rfc' => 'TU_RFC_EMISOR',
    'Name' => 'TU_RAZON_SOCIAL_SIN_SOCIEDAD', // en mayúsculas y sin “S.A. DE C.V.”
    'FiscalRegime' => '601', // c_RegimenFiscal
  ],

  // Sucursal / lugar de expedición (CP de la sucursal dada de alta en Facturama)
  'expedition_place' => '06100',

  // Serie/Folio internos (opcional)
  'default_serie' => 'A',

  // NameId: catálogo “Nombres del CFDI” en Facturama (1 suele ser “Factura”)
  'name_id_factura' => 1,
  'name_id_pago' => 1,
];
