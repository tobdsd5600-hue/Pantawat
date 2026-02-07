<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->action) && $data->action == 'request') {
    try {
        $sql = "INSERT INTO requests (req_id, part_no, part_name, qty, requester, en, station, status, details, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending', ?, NOW())";
        $stmt = $conn->prepare($sql);
        $details = json_encode($data->details); // แปลง object details เป็น JSON string ก่อนลง DB
        $stmt->execute([
            $data->id, $data->part_no, $data->part_name, $data->qty,
            $data->requester, $data->en, $data->station, $details
        ]);
        
        echo json_encode(["status" => "success"]);
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>