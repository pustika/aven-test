<?php
try {
    $db = new PDO('sqlite:identifier.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB Connection error: " . $e->getMessage());
}
?>
