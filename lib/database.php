<?php
    include_once(__DIR__ . '/../config/config.php');
    Class Database{
        public $host   = DB_HOST;
        public $user   = DB_USER;
        public $pass   = DB_PASS;
        public $dbname = DB_NAME;
        
        
        public $link;
        public $error;
    
        public function __construct(){
            $this->connectDB();
        }
        
        private function connectDB(){
            $this->link = new mysqli($this->host, $this->user, $this->pass, $this->dbname);
            if (!$this->link) {
                $this->error = "Connection failed: " . mysqli_connect_error();
                die($this->error); // In lỗi ra để xem chi tiết
            }
        }
        
        // Select
        public function select($query){
            $result = $this->link->query($query) or 
            die($this->link->error.__LINE__);
            if($result->num_rows > 0){
                return $result;
            } else {
                return false;
            }
        }
        
        // Insert
        public function insert($query){
            $insert_row = $this->link->query($query) or 
                die($this->link->error.__LINE__);
            if($insert_row){
                return $insert_row;
            } else {
                return false;
            }
        }
        
        // Update
        public function update($query){
            $update_row = $this->link->query($query) or die($this->link->error.__LINE__);
            if($update_row){
                return $update_row;
            } else {
                return false;
            }
        }
        
        // Delete
        public function delete($query){
            $delete_row = $this->link->query($query) or
                die($this->link->error.__LINE__);
            if($delete_row){
                return $delete_row;
            } else {
                return false;
            }
        }
    }
?>