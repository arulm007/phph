<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

require_once "config.php";

$response = array();

// Retrieve the data from the request body
$data = json_decode(file_get_contents("php://input"), true);

if (
    isset($data['id']) && !empty($data['id']) &&
    isset($data['gender']) && !empty($data['gender']) &&
    isset($data['dob']) && !empty($data['dob']) &&
    isset($data['weight']) && !empty($data['weight']) &&
    isset($data['height']) && !empty($data['height'])
) {
    $id = $data['id'];
    $gender = $data['gender'];
    $dob = $data['dob'];
    $weight = $data['weight'];
    $height = $data['height'];

    // Check if the id exists in the users table
    $check_user_query = "SELECT id FROM users WHERE id = :id";
    $stmt_check = $pdo->prepare($check_user_query);
    $stmt_check->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        // Insert or update the user's profile
        $query = "INSERT INTO user_profiles (id, gender, dob, weight, height) 
                  VALUES (:id, :gender, :dob, :weight, :height)
                  ON DUPLICATE KEY UPDATE 
                  gender = :gender, dob = :dob, weight = :weight, height = :height";
        $stmt = $pdo->prepare($query);

        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->bindParam(":gender", $gender, PDO::PARAM_STR);
        $stmt->bindParam(":dob", $dob, PDO::PARAM_STR);
        $stmt->bindParam(":weight", $weight, PDO::PARAM_STR);
        $stmt->bindParam(":height", $height, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $response["status"] = "success";
            $response["message"] = "User profile saved successfully";
        } else {
            $response["status"] = "error";
            $response["message"] = "Failed to save user profile";
        }
    } else {
        $response["status"] = "error";
        $response["message"] = "User ID does not exist";
    }
} else {
    $response["status"] = "error";
    $response["message"] = "All fields are required";
}

echo json_encode($response);
?>
