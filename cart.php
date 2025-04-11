<?php
include_once "class/brand.php";
$brand = new brand();
include_once(__DIR__ . '/lib/database.php');
include_once(__DIR__ . '/helpers/format.php');
include_once "class/cart.php";

$cart = new cart();
session_start();
$id_taikhoan = $_SESSION['customer_id'] ?? null;

$maGioHang = $cart->get_cart_id_by_account($id_taikhoan);




// Lấy id_khachhang và magiohang
// $id_khachhang = 0;
// $magiohang = 0;
// if ($id_taikhoan) {
//     $id_khachhang = $cart->get_id_customer($id_taikhoan);
//     if ($id_khachhang) {
//         $magiohang = $cart->get_cart_id_by_customer($id_khachhang);
//     }
// }


// Lấy danh sách sản phẩm trong giỏ hàng
$cart_items = [];

if ($maGioHang) {
    $result = $cart->show_cart($maGioHang);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
    
            $cart_items[] = $row;
        }
    }
}

?>


<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watch Store</title>
    <link rel="stylesheet" href="css/cart.css">
    <link rel="stylesheet" href="css/head.css">
    <link rel="stylesheet" href="css/footer.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
    </style>
</head>
<body>
    <?php include 'layout/header.php'; ?>
    <div class="cart-container">
        <h2><i class="fa-solid fa-cart-shopping"></i> Giỏ Hàng Của Bạn</h2>
        <div class="cart-content">
            <table class="cart-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all" checked></th>
                        <th>Sản phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Thành tiền</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody id="cart-items">
                    <?php if (!empty($cart_items)): ?>
                        <?php foreach ($cart_items as $item): ?>
                            <tr data-stock="<?php echo htmlspecialchars($item['soluongTon'] ?? 10); ?>" data-id="<?php echo htmlspecialchars($item['maSanPham']); ?>">

                                <td><input type="checkbox" class="select-item" checked></td>
                                <td class="product-info">
                                    <img src="admin/<?php echo htmlspecialchars($item['hinhAnh'] ?? 'images/default-product.jpg'); ?>" alt="Sản phẩm" class="product-img">
                                    <span class="product-name"><?php echo htmlspecialchars($item['tenSanPham'] ?? 'Tên sản phẩm'); ?></span>
                                </td>
                                <td class="price"><?php echo number_format($item['giaban'] ?? 0, 0, ',', '.') . 'đ'; ?></td>
                                <td>
                                    <div class="conlai">
                                        <span>Còn lại: <?php echo htmlspecialchars($item['soluongTon'] ?? 10); ?></span>
                                    </div>
                                    <div class="quantity-container">
                                        <button class="decrease-btn">-</button>
                                        <input type="number" class="quantity" value="<?php echo htmlspecialchars($item['soLuong'] ?? 1); ?>" min="1">
                                        <button class="increase-btn">+</button>
                                    </div>
                                </td>
                                <td class="total-price"><?php echo number_format(($item['giaban'] ?? 0) * ($item['soLuong'] ?? 1), 0, ',', '.') . 'đ'; ?></td>
                                <td><button class="remove-btn"><i class="fa-solid fa-trash"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Giỏ hàng của bạn đang trống!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="cart-summary">
            <h3>Tổng cộng: <span id="total">0đ</span></h3>
            <button class="checkout-btn">Thanh Toán</button>
        </div>
    </div>
    <?php include 'layout/footer.php'; ?>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const selectAllCheckbox = document.getElementById("select-all");
            const itemCheckboxes = document.querySelectorAll(".select-item");
            const decreaseBtns = document.querySelectorAll(".decrease-btn");
            const increaseBtns = document.querySelectorAll(".increase-btn");
            const totalDisplay = document.getElementById("total");
            const removeBtns = document.querySelectorAll(".remove-btn");

            // Hàm cập nhật tổng tiền của từng hàng
            function updateRowTotal(row) {
                const price = parseFloat(row.querySelector(".price").textContent.replace("đ", "").replace(/,/g, ""));
                const quantity = parseInt(row.querySelector(".quantity").value);
                const totalPriceCell = row.querySelector(".total-price");
                const itemTotal = price * quantity;
                totalPriceCell.textContent = itemTotal.toLocaleString() + "đ";
                return itemTotal;
            }

            // Hàm cập nhật tổng tiền chung
            function updateTotal() {
                let total = 0;
                document.querySelectorAll("#cart-items tr").forEach(row => {
                    const itemTotal = updateRowTotal(row);
                    const checkbox = row.querySelector(".select-item");
                    if (checkbox && checkbox.checked) {
                        total += itemTotal;
                    }
                });
                totalDisplay.textContent = total.toLocaleString() + "đ";
            }

            // Xử lý checkbox "Select All"
            selectAllCheckbox.addEventListener("change", function () {
                itemCheckboxes.forEach(checkbox => {
                    if (checkbox) {
                        checkbox.checked = selectAllCheckbox.checked;
                    }
                });
                updateTotal();
            });

            // Xử lý checkbox từng sản phẩm
            itemCheckboxes.forEach(checkbox => {
                checkbox.addEventListener("change", function () {
                    if (!this.checked) {
                        selectAllCheckbox.checked = false;
                    } else if (document.querySelectorAll(".select-item:checked").length === itemCheckboxes.length) {
                        selectAllCheckbox.checked = true;
                    }
                    updateTotal();
                });
            });

            // Xử lý nút giảm số lượng
            decreaseBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                const row = this.closest("tr");
                const input = this.nextElementSibling;
                const maxStock = parseInt(row.getAttribute("data-stock"));
                const currentValue = parseInt(input.value);
                const idSanPham = row.getAttribute("data-id");
                console.log(idSanPham);
                if (currentValue > 0) {
                    fetch("/watch_store/admin/data/cart_action.php", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=decrement_quantity&id_sanpham=${idSanPham}&id_taikhoan=${<?php echo $id_taikhoan; ?>}`
                    })
                    .then(response =>
                        response.json())
                    .then(data => {
                        if (data.success) {
                            input.value = currentValue - 1;
                            updateTotal();
                        } else {
                            alert("Giảm số lượng thất bại!");
                        }
                    })
                    .catch(error => {
                        console.error("Lỗi:", error);
                        alert("Có lỗi xảy ra khi giảm số lượng!");
                    });
                } else {
                    alert("Số lượng đã đạt tối đa theo số lượng tồn kho!");
                }
            });
        });

            
        increaseBtns.forEach(btn => {
            btn.addEventListener("click", function () {
                const row = this.closest("tr");
                const input = this.previousElementSibling;
                console.log(input);
                const maxStock = parseInt(row.getAttribute("data-stock"));
                const currentValue = parseInt(input.value);
                const idSanPham = row.getAttribute("data-id");
                if (currentValue < maxStock) {
                    fetch("/watch_store/admin/data/cart_action.php", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `action=increment_quantity&id_sanpham=${idSanPham}&id_taikhoan=${<?php echo $id_taikhoan; ?>}`
                    })
                    .then(response =>
                        response.json())
                    .then(data => {
                        if (data.success) {
                            input.value = currentValue + 1;
                            updateTotal();
                        } else {
                            alert("Tăng số lượng thất bại!");
                        }
                    })
                    .catch(error => {
                        console.error("Lỗi:", error);
                        alert("Có lỗi xảy ra khi tăng số lượng!");
                    });
                } else {
                    alert("Số lượng đã đạt tối đa theo số lượng tồn kho!");
                }
            });
        });

        // Xử lý nút xóa sản phẩm
        removeBtns.forEach(btn => {
                btn.addEventListener("click", function () {
                    const row = this.closest("tr");
                    const idSanPham = row.getAttribute("data-id");

                    if (confirm("Bạn có chắc chắn muốn xóa sản phẩm này khỏi giỏ hàng?")) {
                        fetch("/watch_store/admin/data/cart_action.php", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `action=remove_item&id_sanpham=${idSanPham}&id_taikhoan=${<?php echo $id_taikhoan; ?>}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                row.remove();
                                updateTotal();
                            } else {
                                alert("Xóa sản phẩm thất bại!");
                            }
                        })
                        .catch(error => {
                            console.error("Lỗi:", error);
                            alert("Có lỗi xảy ra khi xóa sản phẩm!");
                        });
                    }
                });
            });

            // Khởi tạo tổng tiền ban đầu
            updateTotal();
        });
    </script>
</body>
</html>