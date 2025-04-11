<?php
    $filePath = realpath(dirname(__FILE__));
    include_once $filePath.'../lib/database.php';
    include_once $filePath.'../helpers/format.php';

    class user{
        private $db;
        private $fm;

        public function __construct(){
            $this->db = new Database();
            $this->fm = new Format();
        }

        public function getCategories() {
            $query = "SELECT * FROM tbl_loaisanpham WHERE trangthai = 1"; // Only fetch active categories
            $result = $this->db->select($query);
            return $result;
        }
        public function getBrand() {
            $query = "SELECT * FROM tbl_thuonghieu WHERE trangthai = 1"; // Only fetch active categories
            $result = $this->db->select($query);
            return $result;
        }

        public function insert($data, $files) {
            $product_name = mysqli_real_escape_string($this->db->link, $data['productname']);
            $product_category = mysqli_real_escape_string($this->db->link, $data['category']);
            $product_brand = mysqli_real_escape_string($this->db->link, $data['brand']);
            $product_description = mysqli_real_escape_string($this->db->link, $data['description']);
            $product_price = mysqli_real_escape_string($this->db->link, $data['price']);
            $product_type = mysqli_real_escape_string($this->db->link, $data['select']);
        
            $permited = array('jpg', 'jpeg', 'png', 'gif');
            $file_name = $_FILES['image']['name'];
            $file_size = $_FILES['image']['size'];
            $file_temp = $_FILES['image']['tmp_name'];
        
            $div = explode('.', $file_name);
            $file_ext = strtolower(end($div));
            $unique_image = substr(md5(time()), 0, 10) . '.' . $file_ext;
            $uploaded_image = "uploads/" . $unique_image;
        
            if ($product_name == "" || $product_brand == "" || $product_category == "" ||
                $product_description == "" || $product_price == "" || $product_type == "" ||
                $file_name == "") {
                $alert = "<span class='error'>Vui lòng nhập đầy đủ thông tin</span>";
                return $alert;
            } else {
                move_uploaded_file($file_temp, $uploaded_image);
                
                // Correct the query syntax by inserting the actual variables
                $query = "INSERT INTO tbl_sanpham (tenSanPham, id_loai, id_thuonghieu, mota, giaban, hinhanh, type) 
                          VALUES ('$product_name', '$product_category', '$product_brand', '$product_description', '$product_price', '$uploaded_image', '$product_type')";
        
                $result = $this->db->insert($query);
        
                if ($result) {
                    $alert = "<span class='success'>Thêm sản phẩm thành công</span>";
                    return $alert;
                } else {
                    $alert = "<span class='error'>Thêm sản phẩm thất bại</span>";
                    return $alert;
                }
            }
        }
        
        public function show(){
            // $querry = "SELECT * FROM tbl_sanpham";
            $query = "SELECT sp.tenSanPham, sp.hinhAnh, sp.mota, lsp.tenLoai, th.tenThuongHieu, 
                sp.giaBan, sp.type 
                FROM tbl_sanpham AS sp
                INNER JOIN tbl_loaisanpham AS lsp ON sp.id_loai = lsp.id_loai
                INNER JOIN tbl_thuonghieu AS th ON sp.id_thuonghieu = th.id_thuonghieu";
; 
            $result = $this->db->select($query);
            return $result;
        }

        public function getcatbyid($id){
            $id = intval($id);
            $querry = "SELECT * FROM tbl_loaisanpham WHERE id_loai = $id ";
            $result = $this->db->select($querry);
            return $result;
        }

        public function update($id,$name){

            if (empty($name)){
                $alert = "<span class='error'>Vui lòng nhập tên loại sản phẩm</span>";
                return $alert;
            } else{
                $query = "UPDATE tbl_loaisanpham SET tenLoai = '$name' WHERE id_loai = $id";
                $result = $this->db->update($query);

                if ($result){
                    $alert = "<span class='success'>Sửa loại sản phẩm thành công</span>";
                    return $alert;
                }else{
                    $alert = "<span class='error'>Sửa loại sản phẩm thất bại</span>";
                    return $alert;
                }
            }
        }

        public function delete($id, $status) {
            $newStatus = ($status == 1) ? 0 : 1;
            $query = "UPDATE tbl_loaisanpham SET trangthai = '$newStatus' WHERE id_loai = $id";
            
            $result = $this->db->update($query);
            
            if ($result) {
                $alert = "<span class='success'>Thay đổi trạng thái thành công</span>";
                return $alert;
            } else {
                $alert = "<span class='error'>Thay đổi trạng thái thất bại</span>";
                return $alert;
            }
        }
    }
?>