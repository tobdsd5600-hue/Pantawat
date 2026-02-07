<?php
include 'db.php';

$data = json_decode(file_get_contents("php://input"));

if(isset($data->action)) {
    try {
        if ($data->action == 'create') {
            // เช็คของซ้ำ
            $check = $conn->prepare("SELECT id FROM parts WHERE part_no = ?");
            $check->execute([$data->part_no]);
            if($check->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "Part No already exists"]);
                exit();
            }

            $sql = "INSERT INTO parts (part_no, name, qty, min_level, location, supplier, status, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data->part_no, $data->name, $data->qty, $data->min, 
                $data->location, $data->supplier, $data->status, $data->image
            ]);
            
            // บันทึก Log
            logHistory($conn, $data->part_no, $data->name, "Created new part (Initial: $data->qty)", $data->editor);
            
            echo json_encode(["status" => "success"]);

        } else if ($data->action == 'update') {
            $sql = "UPDATE parts SET name=?, qty=?, min_level=?, location=?, supplier=?, status=?, image=? WHERE part_no=?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                $data->name, $data->qty, $data->min, 
                $data->location, $data->supplier, $data->status, $data->image, $data->part_no
            ]);

            // บันทึก Log ถ้ามี changes ส่งมา
            if(isset($data->changes) && !empty($data->changes)) {
                 logHistory($conn, $data->part_no, $data->name, $data->changes, $data->editor);
            }

            echo json_encode(["status" => "success"]);
        }
    } catch(PDOException $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
}

// ฟังก์ชันบันทึกประวัติ
function logHistory($conn, $part_no, $name, $changes, $editor) {
    $sql = "INSERT INTO history_log (part_no, part_name, changes, editor) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$part_no, $name, $changes, $editor]);
}
?>