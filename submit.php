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

function validatePhoneNumber($phone) {
    $phone = preg_replace('/[^\d+]/', '', $phone);

    if (strpos($phone, '+') === 0) {
        if (strlen($phone) < 8) {
            return false;
        }
    } else {
        if (strlen($phone) < 7) {
            return false;
        }
    }
    if (!preg_match('/^\+?\d+$/', $phone)) {
        return false;
    }
    return true;
}


if (isset($_POST['form_token']) && $_POST['form_token'] === $_SESSION['csrf_token']) {

    $firstName = sanitizeInput($_POST['first_name']);
    $lastName = sanitizeInput($_POST['last_name']);
    $email = sanitizeInput($_POST['email']);
    $phone = sanitizeInput($_POST['phone']);
    $service = sanitizeInput($_POST['select_service']);
    $price = sanitizeInput($_POST['select_price']);
    $comments = sanitizeInput($_POST['comments']);
    $userIp = sanitizeInput($_POST['user_ip']);
    $country = getCountryByIP($userIp);

    if (!validatePhoneNumber($phone)) {
        http_response_code(400);
        exit(json_encode(genResponse(false, null, "Invalid phone number")));
    }
    if (strlen($firstName) < 2) {
        http_response_code(400);
        exit(json_encode(genResponse(false, null, "First name must be at least 2 characters")));
    }
    if (strlen($lastName) < 2) {
        http_response_code(400);
        exit(json_encode(genResponse(false, null, "Last name must be at least 2 characters")));
    }
    if (strlen($service) === 0) {
        http_response_code(400);
        exit(json_encode(genResponse(false, null, "Select service must be not empty")));
    }
    if (strlen($price) === 0) {
        http_response_code(400);
        exit(json_encode(genResponse(false, null, "Price must be not empty")));
    }

    $isSuccess = insertInDb($firstName, $lastName, $email, $phone, $service, $price, $comments, $country, $userIp);

    if ($isSuccess)
        $response = genResponse(true, "https://google.com", "Data successfully processed!");
    else {
        http_response_code(500);
        exit(genResponse(false, null, "Internal server error"));
    }


    logRequest($logFile, $json, $response);

    echo json_encode($response);
    unset($_SESSION['form_token']);

} else {
    http_response_code(403);
    exit(json_encode(genResponse(false, null, "Invalid CSRF token")));
}
?>