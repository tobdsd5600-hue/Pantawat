<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->action)) {
    try {
        $conn->beginTransaction(); // เริ่ม Transaction เพื่อความปลอดภัยของข้อมูล

        if ($data->action == 'approve') {
            // 1. อัปเดตสถานะคำขอ
            $sqlReq = "UPDATE requests SET status='Approved', action_by=?, action_time=NOW() WHERE req_id=?";
            $stmtReq = $conn->prepare($sqlReq);
            $stmtReq->execute([$data->editor, $data->req_id]);

            // 2. ตัดสต็อก
            $sqlPart = "UPDATE parts SET qty = qty - ? WHERE part_no = ?";
            $stmtPart = $conn->prepare($sqlPart);
            $stmtPart->execute([$data->qty, $data->part_no]);

            // 3. บันทึก Log
            $sqlLog = "INSERT INTO history_log (part_no, part_name, changes, editor) VALUES (?, ?, ?, ?)";
            $stmtLog = $conn->prepare($sqlLog);
            $changeMsg = "Withdrawal Approved (REQ: $data->req_id): -$data->qty";
            $stmtLog->execute([$data->part_no, $data->part_name, $changeMsg, $data->editor]);

        } else if ($data->action == 'reject') {
            $sql = "UPDATE requests SET status='Rejected', action_by=?, action_time=NOW(), note=? WHERE req_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$data->editor, $data->note, $data->req_id]);
        }

        $conn->commit(); // ยืนยันการทำงานทั้งหมด
        echo json_encode(["status" => "success"]);

    } catch(PDOException $e) {
        $conn->rollBack(); // ถ้ายกเลิกกลางคัน ให้ย้อนกลับค่าทั้งหมด
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>