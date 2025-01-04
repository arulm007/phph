<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Methods, Authorization, X-Requested-With");

require_once "config.php";

$response = array();

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['id']) && !empty($data['id'])) {
    $id = $data['id'];

    try {
        $stmt = $pdo->prepare("SELECT u.first_name, p.height, p.weight FROM users u LEFT JOIN user_profiles p ON u.id = p.id WHERE u.id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $response["status"] = "success";
            $response["data"] = $user;
        } else {
            $response["status"] = "error";
            $response["message"] = "User not found";
        }
    } catch (PDOException $e) {
        $response["status"] = "error";
        $response["message"] = "Database error: " . $e->getMessage();
    }
} else {
    $response["status"] = "error";
    $response["message"] = "User ID is required";
}

echo json_encode($response);
?>
