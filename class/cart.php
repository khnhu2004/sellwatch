<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');

    class cart{
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        // public function get_id_customer($id_taikhoan) {
        //     $id_taikhoan = mysqli_real_escape_string($this->db->link, $id_taikhoan);
        
        //     $query = "SELECT id_khachhang FROM tbl_khachhang WHERE id_taikhoan = '$id_taikhoan' LIMIT 1";
        //     $result = $this->db->select($query);
        
        //     if ($result && $row = $result->fetch_assoc()) {
        //         return (int)$row['id_khachhang'];
        //     }
        //     return 0;
        // }

        public function get_cart_id_by_account($id_taikhoan) {
            $id_taikhoan = mysqli_real_escape_string($this->db->link, $id_taikhoan);
        
            $query = "SELECT magiohang FROM tbl_giohang WHERE maTaiKhoan = '$id_taikhoan' LIMIT 1";
            $result = $this->db->select($query);
        
            if ($result && $row = $result->fetch_assoc()) {
                return (int)$row['magiohang']; // Trả về mã giỏ hàng nếu có
            }
            return 0; // Trả về 0 nếu không tìm thấy
        }
        
        
        public function add_to_cart($id_giohang, $id_sanpham, $soLuong) {
            $quantity = $this->fm->validation($soLuong);
            $quantity = mysqli_real_escape_string($this->db->link, $quantity);
        
            $id_giohang = $this->fm->validation($id_giohang);
            $id_giohang = mysqli_real_escape_strisng($this->db->link, $id_giohang);
            
            $id_sanpham = mysqli_real_escape_string($this->db->link, $id_sanpham);
        
            // Kiểm tra sản phẩm đã có trong giỏ hàng hay chưa
            $check_query = "SELECT * FROM tbl_chitietgiohang WHERE maGioHang = '$id_giohang' AND maSanPham = '$id_sanpham'";
            $result = $this->db->select($check_query);
        
            if ($result) {
                // Nếu sản phẩm đã tồn tại, cập nhật số lượng
                $update_query = "UPDATE tbl_chitietgiohang SET soLuong = soLuong + $quantity WHERE maGioHang = '$id_giohang' AND maSanPham = '$id_sanpham'";
                $update_result = $this->db->update($update_query);
                if ($update_result) {
                    return "Cập nhật số lượng thành công!";
                } else {
                    return "Cập nhật thất bại!";
                }
            } else {
                // Nếu sản phẩm chưa có, thêm mới
                $insert_query = "INSERT INTO tbl_chitietgiohang (maGioHang, maSanPham, soLuong) VALUES ('$id_giohang', '$id_sanpham', '$quantity')";
                $insert_result = $this->db->insert($insert_query);
                if ($insert_result) {
                    return "Thêm vào giỏ hàng thành công!";
                } else {
                    return "Thêm thất bại!";
                }
            }
        }
        
        public function show_cart($maGioHang) {
            $query = "Select sp.maSanPham, sp.tenSanPham, sp.hinhAnh, ct.soLuong, ctsp.soluongTon, ctsp.giaban
                        FROM tbl_sanpham AS sp 
                        JOIN tbl_chitietgiohang AS ct ON sp.maSanPham = ct.maSanPham
                        join tbl_chitietsanpham as ctsp on sp.maSanPham = ctsp.masanpham
                        join tbl_giohang as gh on ct.maGioHang = gh.magiohang where gh.magiohang = '$maGioHang'";
            return $this->db->select($query);
        }

        public function increment_quantity($id_giohang, $id_sanpham) {
            $query = "UPDATE tbl_chitietgiohang SET soLuong = soLuong + 1 WHERE maGioHang = '$id_giohang' AND maSanPham = '$id_sanpham'";
            return $this->db->update($query);
        }
        public function decrement_quantity($id_giohang, $id_sanpham) {
            $query = "UPDATE tbl_chitietgiohang SET soLuong = soLuong - 1 WHERE maGioHang = '$id_giohang' AND maSanPham = '$id_sanpham'";
            return $this->db->update($query);
        }

        public function get_quantity($maKhachHang) {
            // Thực hiện truy vấn
            $query = "SELECT SUM(soLuongSanPham) AS total_quantity FROM tbl_giohang WHERE maKhachHang = $maKhachHang";
            $result = $this->db->select($query);
        
            // Kiểm tra nếu truy vấn thất bại
            if (!$result) {
                die("Lỗi SQL trong get_quantity(): " . $this->db->link->error);
            }
        
            // Kiểm tra nếu có kết quả hợp lệ
            $row = $result->fetch_assoc();
            return isset($row['total_quantity']) ? (int)$row['total_quantity'] : 0;
        }
        

        public function get_price_by_id($id) {
            $query = "SELECT giaBan FROM tbl_sanpham WHERE maSanPham = $id";

            $result = $this->db->select($query)->fetch_assoc();

            if ($result) {
                return (int)$result['giaBan'];
            } else {
                return 0;
            }
        }   
        public function remove_from_cart($id_giohang, $id_sanpham) {
            $id_giohang = mysqli_real_escape_string($this->db->link, $id_giohang);
            $id_sanpham = mysqli_real_escape_string($this->db->link, $id_sanpham);
            $query = "DELETE FROM tbl_chitietgiohang WHERE maGioHang = '$id_giohang' AND maSanPham = '$id_sanpham'";
            return $this->db->delete($query);
        }
    }
?>