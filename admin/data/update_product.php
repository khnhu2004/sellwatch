<?php 
    include("../../class/product.php");
    $product = new Product();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if(isset($_POST['product_id'])) {
            $id = $_POST['product_id'];
            $name = $_POST['product_name'];
            $description = $_POST['product_desc'];
            $type = $_POST['product_type'];
            $brand = $_POST['product_brand'];
            $fileDelete = $_POST['sub_img_delete'];

            $body = array(
                'name' => $name,
                'description' => $description,
                'type' => $type,
                'brand' => $brand,
                'delete' => $fileDelete,
            );


            $product->update((int)$id, $body, $_FILES);

            echo json_encode($body);
            exit;
        }

        
        echo json_encode($name);
        // $product->update($id,$body, $_FILES);
    }
?>