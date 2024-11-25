<?php
require 'db.config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');
$json = file_get_contents('php://input');
$data = json_decode($json, true);
$logFile = __DIR__ . '/request_log.txt';
$response = [];
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

function getCountryByIP($ip)
{
    $url = "http://ip-api.com/json/{$ip}";
    $response = file_get_contents($url);
    if ($response === FALSE) {
        error_log("Failed to retrieve country");
        return "Failed to retrieve country";
    }
    $data = json_decode($response, true);
    if ($data['status'] === 'fail') {
        error_log("Failed to retrieve country" . $data['message']);
        return "Failed to retrieve country";
    }
    return $data['country'];
}

function sanitizeInput($data)
{
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function insertInDb($firstName, $lastName, $email, $phone, $service, $price, $comments, $country, $ip)
{
    try {
        global $db;
        $query = "INSERT INTO leads (first_name, last_name, phone, email, selected_service, select_price, comments, user_ip, сountry) 
                  VALUES (:first_name, :last_name, :phone, :email, :selected_service, :select_price, :comments, :user_ip, :country)";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':first_name', $firstName);
        $stmt->bindParam(':last_name', $lastName);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':selected_service', $service);
        $stmt->bindParam(':select_price', $price);
        $stmt->bindParam(':comments', $comments);
        $stmt->bindParam(':user_ip', $ip);
        $stmt->bindParam(':country', $country);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        error_log("Error DB: " . $e->getMessage());
        return false;
    }
}

function genResponse($isSuccess, $redirect_url, $message)
{
    return [
        'success' => $isSuccess,
        'redirect_url' => $redirect_url,
        'message' => $message
    ];
}

$firstName = sanitizeInput($_POST['first_name']);
$lastName = sanitizeInput($_POST['last_name']);
$email = sanitizeInput($_POST['email']);
$phone = sanitizeInput($_POST['phone']);
$service = sanitizeInput($_POST['select_service']);
$price = sanitizeInput($_POST['select_price']);
$comments = sanitizeInput($_POST['comments']);
$userIp = sanitizeInput($_POST['user_ip']);
$country = getCountryByIP($userIp);


if (isset($_POST['first_name'])) {
    $isSuccess = insertInDb($firstName, $lastName, $email, $phone, $service, $price, $comments, $country, $userIp);

    if ($isSuccess)
        $response = genResponse(true, "google.com", "Data successfully processed!");
     else
        $response = genResponse(false, null, "Internal server error!");
} else
    $response = genResponse(false, null, "Error: The data was not received or is incorrect.");


logRequest($logFile, $json, $response);

echo json_encode($response);
?>