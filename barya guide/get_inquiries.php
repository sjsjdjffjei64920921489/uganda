<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'GET')     { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit; }

require_once 'connect.php'; // provides $conn

$result = $conn->query(
    "SELECT id, name, email, phone, message, status, created_at
       FROM inquiries
      ORDER BY created_at DESC"
);

if (!$result) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>'Query failed: '.$conn->error]);
    exit;
}

$rows    = [];
$total   = 0;
$new     = 0;
$pending = 0;
$replied = 0;

while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
    $total++;
    if ($row['status'] === 'new')     $new++;
    if ($row['status'] === 'pending') $pending++;
    if ($row['status'] === 'replied') $replied++;
}

$conn->close();

echo json_encode([
    'success' => true,
    'stats'   => compact('total','new','pending','replied'),
    'data'    => $rows,
]);
