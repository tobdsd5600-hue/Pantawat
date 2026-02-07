<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->action)) {
    try {
        $conn->beginTransaction();

        if ($data->action == 'create') {
            $sql = "INSERT INTO purchase_orders (po_id, date, part_no, part_name, qty, requester, reason, supplier, status, remark) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data->id, $data->part_no, $data->part_name, $data->qty,
                $data->requester, $data->reason, $data->supplier, $data->status, $data->remark
            ]);
            
            echo json_encode(["status" => "success"]);

        } else if ($data->action == 'receive') {
            // 1. อัปเดตสถานะ PO
            $sqlPO = "UPDATE purchase_orders SET status = ? WHERE po_id = ?";
            $stmtPO = $conn->prepare($sqlPO);
            $stmtPO->execute([$data->status, $data->id]);

            // 2. เพิ่มสต็อกสินค้า
            $sqlPart = "UPDATE parts SET qty = qty + ? WHERE part_no = ?";
            $stmtPart = $conn->prepare($sqlPart);
            $stmtPart->execute([$data->receive_qty, $data->part_no]);

            // 3. บันทึก Log
            $sqlLog = "INSERT INTO history_log (part_no, part_name, changes, editor) VALUES (?, ?, ?, ?)";
            $stmtLog = $conn->prepare($sqlLog);
            $changeMsg = "Received PO ({$data->id}): +{$data->receive_qty} (Ordered: {$data->order_qty})";
            $stmtLog->execute([$data->part_no, $data->part_name, $changeMsg, $data->editor]);
            
            echo json_encode(["status" => "success"]);
        }

        $conn->commit();

    } catch(PDOException $e) {
        $conn->rollBack();
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}
?>