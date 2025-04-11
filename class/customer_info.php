<?php
    include_once(__DIR__ . '/../lib/database.php');
    include_once(__DIR__ . '/../helpers/format.php');
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    class Customer {
        private $db;
        private $fm;

        public function __construct() {
            $this->db = new Database();
            $this->fm = new Format();
        }

        // Hàm lấy thông tin khách hàng theo ID
        public function getinforcustomerbyid($id_cus) {
            $id_cus = $this->fm->validation($id_cus);
            $query = "SELECT * FROM tbl_khachhang WHERE id_taikhoan = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id_cus);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }

        // Hàm lấy thông tin tài khoản theo ID
        public function getinfortaikhoanbyid($id_taikhoan) {
            $id_taikhoan = $this->fm->validation($id_taikhoan);
            $query = "SELECT * FROM tbl_taikhoan WHERE id = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id_taikhoan);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            return $result;
        }

        public function updateCustomerInfo($id_taikhoan, $tenkhachhang, $diachi, $sodt, $email) {
            try {
                // Validation dữ liệu đầu vào
                $id_taikhoan = $this->fm->validation($id_taikhoan);
                $tenkhachhang = $this->fm->validation($tenkhachhang);
                $diachi = $this->fm->validation($diachi);
                $sodt = $this->fm->validation($sodt);
                $email = $this->fm->validation($email);
        
                // Kiểm tra định dạng email (nếu có dữ liệu)
                if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return "Email không hợp lệ!";
                }
        
                // Kiểm tra định dạng số điện thoại (nếu có dữ liệu)
                if ($sodt !== '' && !preg_match("/^[0-9]{10,11}$/", $sodt)) {
                    return "Số điện thoại không hợp lệ!";
                }
        
                // Lấy thông tin hiện tại của khách hàng dựa trên id_taikhoan
                $currentInfo = $this->getinforcustomerbyid($id_taikhoan);
                if (!$currentInfo) {
                    return "Không tìm thấy khách hàng với ID: $id_taikhoan!";
                }
                $currentInfo = $currentInfo->fetch_assoc();
        
                // Kiểm tra email trùng lặp (nếu thay đổi và có dữ liệu)
                if ($email !== '' && $email !== $currentInfo['email']) {
                    $checkEmailQuery = "SELECT * FROM tbl_khachhang WHERE email = ? AND id_taikhoan != ?";
                    $stmt = $this->db->link->prepare($checkEmailQuery);
                    $stmt->bind_param("ss", $email, $id_taikhoan);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $stmt->close();
                        return "Email đã tồn tại!";
                    }
                    $stmt->close();
                }
        
                // Kiểm tra số điện thoại trùng lặp (nếu thay đổi và có dữ liệu)
                if ($sodt !== '' && $sodt !== $currentInfo['soDT']) {
                    $checkPhoneQuery = "SELECT * FROM tbl_khachhang WHERE soDT = ? AND id_taikhoan != ?";
                    $stmt = $this->db->link->prepare($checkPhoneQuery);
                    $stmt->bind_param("ss", $sodt, $id_taikhoan);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows > 0) {
                        $stmt->close();
                        return "Số điện thoại đã tồn tại!";
                    }
                    $stmt->close();
                }
        
                // Cập nhật thông tin trong bảng tbl_khachhang
                $query = "UPDATE tbl_khachhang SET tenKhachHang = ?, diaChi = ?, soDT = ?, email = ? WHERE id_taikhoan = ?";
                $stmt = $this->db->link->prepare($query);
                $stmt->bind_param("sssss", $tenkhachhang, $diachi, $sodt, $email, $id_taikhoan);
                $updateResult = $stmt->execute();
                $stmt->close();
        
                if (!$updateResult) {
                    return "Lỗi khi cập nhật thông tin khách hàng!";
                }
        
                return true;
            } catch (Exception $e) {
                return "Lỗi hệ thống: " . $e->getMessage();
            }
        }
        public function changePassword($id_taikhoan, $old_password, $new_password, $confirm_password) {
            try {
                // Validation dữ liệu đầu vào
                $id_taikhoan = $this->fm->validation($id_taikhoan);
                $old_password = $this->fm->validation($old_password);
                $new_password = $this->fm->validation($new_password);
                $confirm_password = $this->fm->validation($confirm_password);
        
                // Kiểm tra các trường không được để trống
                if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
                    return "Vui lòng điền đầy đủ tất cả các trường!";
                }
        
                // Kiểm tra độ dài mật khẩu mới
                if (strlen($new_password) < 8) {
                    return "Mật khẩu mới phải chứa ít nhất 8 ký tự!";
                }
        
                // Kiểm tra xác nhận mật khẩu mới
                if ($new_password !== $confirm_password) {
                    return "Xác nhận mật khẩu mới không khớp!";
                }


        
                // Lấy thông tin hiện tại của khách hàng
                $currentInfo = $this->getinfortaikhoanbyid($id_taikhoan);
                
                if (!$currentInfo || $currentInfo->num_rows == 0) {
                    return "Không tìm thấy tài khoản với ID: $id_taikhoan!";
                }
                $currentInfo = $currentInfo->fetch_assoc();
        
                // Debug: kiểm tra dữ liệu lấy từ database
                if (!isset($currentInfo['password'])) {
                    return "Lỗi: Không tìm thấy cột mật khẩu trong database!";
                }

                $old_password = trim($old_password);

                $md5_old_password = md5($old_password);
                $stored_password = $currentInfo['password'];
                var_dump($md5_old_password, $stored_password);
                // Kiểm tra mật khẩu cũ
                if ($md5_old_password !== $stored_password) {
                    return "Mật khẩu cũ không đúng!";
                }
        
                // Kiểm tra mật khẩu mới không được trùng với mật khẩu cũ
                if ($new_password === $old_password) {
                    return "Mật khẩu mới không được trùng với mật khẩu cũ!";
                }
        
                // Mã hóa mật khẩu mới bằng MD5 trước khi lưu
                $md5_new_password = md5($new_password);
        
                // Cập nhật mật khẩu mới vào database
                $query = "UPDATE tbl_taikhoan SET password = ? WHERE id = ?";
                $stmt = $this->db->link->prepare($query);
                $stmt->bind_param("ss", $md5_new_password, $id_taikhoan);
                $updateResult = $stmt->execute();
        
                if (!$updateResult) {
                    return "Lỗi khi cập nhật mật khẩu: " . $this->db->link->error;
                }
        
                $stmt->close();
                return true;
        
            } catch (Exception $e) {
                return "Lỗi hệ thống: " . $e->getMessage();
            }
        }
        // Lưu hoặc cập nhật ảnh đại diện
        public function updateAvatar($id_taikhoan, $hinh_anh) {
            // Kiểm tra xem đã có ảnh đại diện cho tài khoản này chưa
            $sql_check = "SELECT id_anh FROM tbl_anhdaidien WHERE id_taikhoan = ?";
            $stmt_check = $this->db->link->prepare($sql_check);
            $stmt_check->bind_param("i", $id_taikhoan);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            $stmt_check->close();

            if ($result->num_rows > 0) {
                // Cập nhật ảnh nếu đã tồn tại
                $sql = "UPDATE tbl_anhdaidien SET hinhAnh = ? WHERE id_taikhoan = ?";
                $stmt = $this->db->link->prepare($sql);
                $stmt->bind_param("si", $hinh_anh, $id_taikhoan);
            } else {
                // Thêm mới nếu chưa có
                $sql = "INSERT INTO tbl_anhdaidien (id_taikhoan, hinhAnh) VALUES (?, ?)";
                $stmt = $this->db->link->prepare($sql);
                $stmt->bind_param("is", $id_taikhoan, $hinh_anh);
            }
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

        //Lấy đường dẫn ảnh đại diện
        public function getAvatar($id_taikhoan) {
            $sql = "SELECT hinhAnh FROM tbl_anhdaidien WHERE id_taikhoan = ?";
            $stmt = $this->db->link->prepare($sql);
            $stmt->bind_param("i", $id_taikhoan);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
            if ($row = $result->fetch_assoc()) {
                return $row['hinhAnh'];
            }
            return false; // Trả về false nếu không có ảnh
        }
    }
?>