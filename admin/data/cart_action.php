<?php
include_once "../../class/cart.php";
$cart = new cart();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $id_taikhoan = $_POST['id_taikhoan'] ?? 0;
        $magiohang = $cart->get_cart_id_by_account($id_taikhoan);

        if ($magiohang) {
            if ($_POST['action'] === 'increment_quantity') {
                $id_sanpham = $_POST['id_sanpham'] ?? 0;
                $result = $cart->increment_quantity($magiohang, $id_sanpham);
                echo json_encode(['success' => $result]);
                exit;
            }

            if ($_POST['action'] === 'decrement_quantity') {
                $id_sanpham = $_POST['id_sanpham'] ?? 0;
                $result = $cart->decrement_quantity($magiohang, $id_sanpham);
                echo json_encode(['success' => $result]);
                exit;
            }

            if ($_POST['action'] === 'remove_item') {
                $id_sanpham = $_POST['id_sanpham'] ?? 0;
                $result = $cart->remove_from_cart($magiohang, $id_sanpham);
                echo json_encode(['success' => $result]);
                exit;
            }
        }
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy giỏ hàng']);
        exit;
    }
}
echo json_encode(['success' => false, 'message' => 'Hành động không hợp lệ']);
?>