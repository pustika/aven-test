<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$logFile = __DIR__ . '/request_log.txt';;

function logRequest($logFile, $data, $response)
{
    $logEntry = [
        'time' => date('Y-m-d H:i:s'),
        'request_data' => $data,
        'response' => $response
    ];
    $result = file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND);
    if ($result === false) {
        error_log('File write error: ' . $logFile);
    }
}

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

$fields = [
    'first_name' => 'first_name1',
    'last_name' => 'last_name1',
    'email' => 'email1',
    'phone' => 'phone1',
    'select_service' => 'select_service1',
    'select_price' => 'select_price1',
    'comments' => null
];

$inputData = [];

foreach ($fields as $key => $altKey) {
    if (isset($_POST[$key])) {
        $inputData[$key] = sanitizeInput($_POST[$key]);
    } elseif ($altKey && isset($_POST[$altKey])) {
        $inputData[$key] = sanitizeInput($_POST[$altKey]);
    } else {
        $inputData[$key] = '';
    }
}

$firstName = $inputData['first_name'];
$lastName = $inputData['last_name'];
$email = $inputData['email'];
$phone = $inputData['phone'];
$service = $inputData['select_service'];
$price = $inputData['select_price'];
$comments = $inputData['comments'];

if (isset($_POST['first_name'])) {
    $response = [
        'success' => true,
        'redirect_url' => 'https://google.com/',
        'message' => 'Data successfully processed!'
    ];
} else {
    $response = [
        'success' => false,
        'redirect_url' => null,
        'message' => 'Error: The data was not received or is incorrect.'
    ];
}

logRequest($logFile, $json, $response);

echo json_encode($response);
?>