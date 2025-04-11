<?php
    header("Content-Type: application/json"); 
    include "../../class/product.php"; 

    if (isset($_GET['id'])) { 
        $id = intval($_GET['id']);
        $product = new Product();

        $result = $product->getById($id);
        
        if ($result) {
            $result['anhphu'] = explode(",", $result['anhphu']);
            echo json_encode($result);
        } else {
            echo json_encode(["message" => "Không tìm thấy sản phẩm"]);
        }
    } else {
        echo json_encode(["error" => "Thiếu ID sản phẩm"]);
    }
?>
