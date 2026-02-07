<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->action)) {
    try {
        if ($data->action == 'create') {
             // เช็คซ้ำ
             $check = $conn->prepare("SELECT id FROM users WHERE en = ? OR username = ?");
             $check->execute([$data->en, $data->username]);
             if($check->rowCount() > 0) {
                 echo json_encode(["status" => "error", "message" => "Employee ID or Username already exists"]);
                 exit();
             }

             $sql = "INSERT INTO users (en, name, position, role, username, password, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
             $stmt = $conn->prepare($sql);
             $stmt->execute([$data->en, $data->name, $data->position, $data->role, $data->username, $data->password, $data->status]);
             echo json_encode(["status" => "success"]);

        } else if ($data->action == 'update') {
             $sql = "UPDATE users SET name=?, position=?, role=?, status=?, username=?, password=? WHERE en=?";
             $stmt = $conn->prepare($sql);
             $stmt->execute([$data->name, $data->position, $data->role, $data->status, $data->username, $data->password, $data->en]);
             echo json_encode(["status" => "success"]);

        } else if ($data->action == 'delete') {
             $stmt = $conn->prepare("DELETE FROM users WHERE en = ?");
             $stmt->execute([$data->en]);
             echo json_encode(["status" => "success"]);
        }
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>