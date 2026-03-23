<?php
// generar.php

// 1. Configuración de la API y datos fijos
$apiKey = 'AQUI_TU_API_KEY'; // Reemplaza esto con tu API Key real
$endpoint = 'https://invoice-generator.com';

// El cliente siempre es el mismo
$clienteFijo = "Misterweb S.L.\nB42502500\nC/ Garbí, 22\n03530 La Nucía, Alicante (España)";

// 2. Recolectar datos del POST
$from = $_POST['from'] ?? '';
$number = $_POST['number'] ?? '';
$date = $_POST['date'] ?? '';
$dueDate = $_POST['due_date'] ?? '';

$itemsName = $_POST['item_name'] ?? [];
$itemsQty = $_POST['item_qty'] ?? [];
$itemsRate = $_POST['item_rate'] ?? [];

// 3. Formatear los ítems
$items = [];
for ($i = 0; $i < count($itemsName); $i++) {
    if (!empty($itemsName[$i])) {
        // Aseguramos que si escribes "41,041" (coma en lugar de punto) se envíe bien a la API
        $qtyFormat = str_replace(',', '.', $itemsQty[$i]);
        
        $items[] = [
            'name' => $itemsName[$i],
            'quantity' => (float) $qtyFormat,
            'unit_cost' => (float) $itemsRate[$i]
        ];
    }
}

// 4. Preparar el JSON para la API
$data = [
    'from' => $from,
    'to' => $clienteFijo,
    'number' => $number,
    'date' => date('M d, Y', strtotime($date)),
    'due_date' => date('M d, Y', strtotime($dueDate)),
    'items' => $items,
    'currency' => 'USD',
    'header' => 'FACTURA',
    'notes' => 'Notas: cualquier información relevante que no esté ya cubierta'
];

// 5. Enviar la petición cURL
$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey,
    'Accept-Language: es-ES' // Para que las cabeceras de la tabla (Quantity, Rate) salgan en español
]);

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

// 6. Entregar el PDF al navegador
if ($httpcode == 200 && $response) {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="factura_'.$number.'.pdf"');
    echo $response;
} else {
    echo "<h1>Error al generar la factura</h1>";
    echo "<p>Código HTTP: " . $httpcode . "</p>";
    echo "<p>Error cURL: " . $error . "</p>";
}
?>