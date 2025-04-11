<?php
    $filePath = realpath(dirname(__FILE__));
    include_once __DIR__ . '/../lib/session.php';
    Session::checkLogin();
    include_once __DIR__ . '/../lib/database.php';
    include_once __DIR__ . '/../helpers/format.php';

    class customerlogin {
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function login($data) {
            $username = mysqli_real_escape_string($this->db->link, $data['username']);
            $password = mysqli_real_escape_string($this->db->link, md5($data['password']));
        
            // Truy vấn kiểm tra username
            $query = "SELECT * FROM tbl_taikhoan WHERE username = ? AND trangThai = 1 LIMIT 1";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
        
            if ($result->num_rows == 0) {
                return "Tên đăng nhập hoặc mật khẩu không đúng!";
            }
        
            $user = $result->fetch_assoc();
        
            if ($user['password'] !== $password) {
                return "Tên đăng nhập hoặc mật khẩu không đúng!";
            }
        
            // Lưu vào session khi đăng nhập thành công
            Session::set('customer_login', true);
            Session::set('customer_id', $user['id']);
            Session::set('customer_name', $user['username']);
        
            return true;
        }
        
        public function register($data) {
            $hoten = mysqli_real_escape_string($this->db->link, $data['hoten']);
            $username = mysqli_real_escape_string($this->db->link, $data['username']);
            $password = mysqli_real_escape_string($this->db->link, md5($data['password']));
        
            // Kiểm tra username đã tồn tại chưa
            $checkQuery = "SELECT * FROM tbl_taikhoan WHERE username = ?";
            $stmt = $this->db->link->prepare($checkQuery);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $checkResult = $stmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                return "Tên Đăng Nhập Đã Tồn Tại";
            }
        
            // Thêm tài khoản vào bảng tbl_taikhoan
            $query = "INSERT INTO tbl_taikhoan (username, password) VALUES (?, ?)";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
        
            if ($stmt->affected_rows <= 0) {
                return "Lỗi khi tạo tài khoản: " . $stmt->error;
            }
        
            // Lấy ID của tài khoản vừa tạo
            $account_id = $this->db->link->insert_id;
        
            // Thêm vào bảng tbl_khachhang
            $query_tblkhachhang = "INSERT INTO tbl_khachhang (id_taikhoan, tenKhachHang, diaChi, soDT, email) 
            VALUES (?, ?, '', '', '')";
            $stmt = $this->db->link->prepare($query_tblkhachhang);
            $stmt->bind_param("is", $account_id, $hoten);
            $stmt->execute();
        
            if ($stmt->affected_rows <= 0) {
                return "Lỗi khi thêm khách hàng: " . $stmt->error;
            }
        
            // Thêm ảnh đại diện mặc định
            $hinhanh = 'admin/uploads/avt_user/avatar.png';
            $query_tblanhdaidien = "INSERT INTO tbl_anhdaidien (id_taikhoan, hinhAnh) 
                                    VALUES (?, ?)";
            $stmt = $this->db->link->prepare($query_tblanhdaidien);
            $stmt->bind_param("is", $account_id, $hinhanh);
            $stmt->execute();
        
            if ($stmt->affected_rows <= 0) {
                return "Lỗi khi thêm ảnh đại diện: " . $stmt->error;
            }
        
            // Thêm giỏ hàng cho khách hàng mới với giá trị `tongTien = 0`
            $query_cart = "INSERT INTO tbl_giohang (maTaiKhoan, tongTien) VALUES (?, 0)";
            $stmt = $this->db->link->prepare($query_cart);
            $stmt->bind_param("i", $account_id);
            $stmt->execute();
        
            if ($stmt->affected_rows <= 0) {
                return "Lỗi khi thêm giỏ hàng: " . $stmt->error;
            }
        
            return true;
        }
        
        public function getinforcustomerbyid($id) {
            $query = "SELECT * FROM tbl_khachhang WHERE id_taikhoan = ?";
            $stmt = $this->db->link->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result;
        }
    }
?>
