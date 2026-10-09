<?php
// Allow calling this script from the same origin or locally
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

// CORS pre-flight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Pull in DB connection ($conn)
require_once 'connect.php';

// ── Read input ──────────────────────────────────────────────────
// Works with both JSON body (fetch) and regular HTML form POST
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
$isJson      = strpos($contentType, 'application/json') !== false; // PHP 7 compatible

if ($isJson) {
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) $data = [];
} else {
    $data = $_POST;
}

$name    = trim(isset($data['name'])    ? $data['name']    : '');
$email   = trim(isset($data['email'])   ? $data['email']   : '');
$phone   = trim(isset($data['phone'])   ? $data['phone']   : '');
$message = trim(isset($data['message']) ? $data['message'] : '');

// ── Validation ──────────────────────────────────────────────────
$errors = [];

if ($name === '') {
    $errors[] = 'Full name is required.';
}
if ($email === '') {
    $errors[] = 'Email address is required.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Please enter a valid email address.';
}
if ($message === '') {
    $errors[] = 'Message is required.';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ── Insert ──────────────────────────────────────────────────────
$stmt = $conn->prepare(
    "INSERT INTO inquiries (name, email, phone, message) VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB prepare error: ' . $conn->error]);
    exit;
}

$stmt->bind_param('ssss', $name, $email, $phone, $message);

if (!$stmt->execute()) {
    $err = $stmt->error;
    $stmt->close();
    $conn->close();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save inquiry: ' . $err]);
    exit;
}

$newId     = (int) $stmt->insert_id;
$createdAt = date('Y-m-d H:i:s');
$stmt->close();
$conn->close();

http_response_code(201);
echo json_encode([
    'success'    => true,
    'message'    => 'Thank you! Your inquiry has been received.',
    'id'         => $newId,
    'created_at' => $createdAt,
]);
