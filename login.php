<?php
header("Content-Type: application/json");
require_once 'config.php';

$response = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    // Check for missing fields
    if (!isset($input['email']) || !isset($input['password'])) {
        $response['status'] = 'error';
        $response['message'] = 'Email and Password are required';
        echo json_encode($response);
        exit;
    }

    $email = trim($input['email']);
    $password = trim($input['password']);

    try {
        // Query to find the user
        $stmt = $pdo->prepare("SELECT id, first_name, last_name, password FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verify password
            if (password_verify($password, $user['password'])) {
                $response['status'] = 'success';
                $response['message'] = 'Login successful';
                $response['data'] = array(
                    'id' => $user['id'],
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $email
                );
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Invalid email or password';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid email or password';
        }
    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Invalid request method';
}

echo json_encode($response);
