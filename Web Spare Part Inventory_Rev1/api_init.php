<?php
include 'db.php';

try {
    $data = [];

    // 1. Get Parts
    $stmt = $conn->prepare("SELECT * FROM parts ORDER BY name ASC");
    $stmt->execute();
    $data['parts'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get Users
    $stmt = $conn->prepare("SELECT * FROM users ORDER BY name ASC");
    $stmt->execute();
    $data['users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Get Requests (Pending only for active list, All for history)
    $stmt = $conn->prepare("SELECT * FROM requests ORDER BY timestamp DESC");
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // แปลง JSON string กลับเป็น Object สำหรับ field details
    foreach ($requests as $key => $req) {
        if ($req['details']) {
            $requests[$key]['details'] = json_decode($req['details']);
        }
    }
    $data['requests'] = $requests;

    // 4. Get PO
    $stmt = $conn->prepare("SELECT * FROM purchase_orders ORDER BY date DESC");
    $stmt->execute();
    $data['po'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Get History Logs
    $stmt = $conn->prepare("SELECT * FROM history_log ORDER BY time DESC LIMIT 200"); // Limit เพื่อไม่ให้โหลดหนักเกินไป
    $stmt->execute();
    $data['historyED'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($data);

} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>