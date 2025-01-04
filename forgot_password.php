<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'config.php'; // Include your database connection file

$response = [
    "status" => "error",
    "message" => "An error occurred"
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve JSON input
    $input = json_decode(file_get_contents("php://input"), true);

    $email = isset($input['email']) ? trim($input['email']) : null;
    $new_password = isset($input['new_password']) ? trim($input['new_password']) : null;

    // Check if all required fields are provided
    if (empty($email) || empty($new_password)) {
        $response['message'] = "Email and New Password are required";
        echo json_encode($response);
        exit;
    }

    try {
        // Check if the email exists in the users table
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Update the password for the user
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = :password, updated_at = NOW() WHERE email = :email");
            $update_stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
            $update_stmt->bindParam(':email', $email, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                $response['status'] = "success";
                $response['message'] = "Password reset successfully";
            } else {
                $response['message'] = "Failed to reset password";
            }
        } else {
            $response['message'] = "Email not found";
        }
    } catch (PDOException $e) {
        $response['message'] = "Database error: " . $e->getMessage();
    }
} else {
    $response['message'] = "Invalid request method";
}

echo json_encode($response);
?>
