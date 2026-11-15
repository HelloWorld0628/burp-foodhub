<?php
session_start();
session_destroy(); // Destroy the secure session
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>