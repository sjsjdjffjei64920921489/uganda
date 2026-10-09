<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed.']); exit; }

require_once 'connect.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!is_array($data)) $data = [];

$id     = isset($data['id'])     ? (int)$data['id']       : 0;
$status = isset($data['status']) ? trim($data['status'])   : '';

$allowed = ['new', 'pending', 'replied'];
if ($id <= 0 || !in_array($status, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Valid id and status (new|pending|replied) are required.']);
    exit;
}

$stmt = $conn->prepare("UPDATE inquiries SET status = ? WHERE id = ?");
if (!$stmt) { http_response_code(500); echo json_encode(['success'=>false,'message'=>$conn->error]); exit; }

$stmt->bind_param('si', $status, $id);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    $stmt->close(); $conn->close();
    http_response_code(404);
    echo json_encode(['success'=>false,'message'=>'Inquiry not found.']);
    exit;
}

$stmt->close();
$conn->close();
echo json_encode(['success'=>true,'message'=>'Status updated.','status'=>$status]);
