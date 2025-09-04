<?php 
include 'connection.php';

$query = $_GET['q'] ?? '';
$query = trim($query);

if ($query === '') {
    echo json_encode([]);
    exit;
}

// 使用 prepared statement 防止 SQL injection
$sql = "
    SELECT p.id, p.name, i.img_path 
    FROM all_products p
    LEFT JOIN (
        SELECT product_id, MIN(id) as min_img_id
        FROM all_products_img
        GROUP BY product_id
    ) first_img ON p.id = first_img.product_id
    LEFT JOIN all_products_img i ON i.id = first_img.min_img_id
    WHERE p.name LIKE ?
    LIMIT 5
";
$stmt = $conn->prepare($sql);
$likeQuery = "%$query%";
$stmt->bind_param("s", $likeQuery);
$stmt->execute();
$result = $stmt->get_result();

$results = [];
while ($row = $result->fetch_assoc()) {
    $results[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'img' => $row['img_path']
    ];
}

echo json_encode($results, JSON_UNESCAPED_UNICODE);
?>
