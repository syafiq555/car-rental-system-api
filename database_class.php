<?php

   include_once('models/Base.php');
   include_once('models/User.php');
   include_once('models/Order.php');
   include_once('models/Car.php');
   include_once('models/Manufacturer.php');
   include_once('models/Model.php');

   class DbStatus {
      var $status;
      var $error;
      var $lastinsertid;
   }

   function time_elapsed_string($datetime, $full = false) {

      if ($datetime == '0000-00-00 00:00:00')
         return "none";

      if ($datetime == '0000-00-00')
         return "none";

      $now = new DateTime;
      $ago = new DateTime($datetime);
      $diff = $now->diff($ago);

      $diff->w = floor($diff->d / 7);
      $diff->d -= $diff->w * 7;

      $string = array(
         'y' => 'year',
         'm' => 'month',
         'w' => 'week',
         'd' => 'day',
         'h' => 'hour',
         'i' => 'minute',
         's' => 'second',
      );
      
      foreach ($string as $k => &$v) {
         if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
         } else {
            unset($string[$k]);
         }
      }

      if (!$full) $string = array_slice($string, 0, 1);
         return $string ? implode(', ', $string) . ' ago' : 'just now';
   }

	class Database {
 		protected $dbhost;
    	protected $dbuser;
    	protected $dbpass;
    	protected $dbname;
    	protected $db;

 		function __construct( $dbhost, $dbuser, $dbpass, $dbname) {
   		$this->dbhost = $dbhost;
   		$this->dbuser = $dbuser;
   		$this->dbpass = $dbpass;
   		$this->dbname = $dbname;

   		$db = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
         $db->setAttribute(PDO::MYSQL_ATTR_FOUND_ROWS, true);
    		$this->db = $db;
   	}

      function beginTransaction() {
         try {
            $this->db->beginTransaction(); 
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function commit() {
         try {
            $this->db->commit();
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function rollback() {
         try {
            $this->db->rollback();
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function close() {
         try {
            $this->db = null;   
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();
            return 0;
         } 
      }

      function insertUser(User $user) {

         //hash the password using one way md5 hashing
         $passwordhash = salt($user->password);
         try {
            
            $sql = "
               INSERT INTO users(
                  first_name, last_name, 
                  username, password, email, role, 
                  ic_number, mobile_phones
               ) 
               VALUES (:first_name, :last_name, 
                  :username, :password, :email, 
                  :role, :ic_number, :c, 
               )";

            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("first_name", $user->first_name);
            $stmt->bindParam("last_name", $user->last_name);
            $stmt->bindParam("username", $user->username);
            $stmt->bindParam("password", $user->password);
            $stmt->bindParam("email", $user->email);
            $stmt->bindParam("role", $user->role);
            $stmt->bindParam("ic_number", $user->ic_number);
            $stmt->bindParam("mobile_phone", $user->mobile_phone);
            $stmt->execute();

            $dbs = new DbStatus();
            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
         }
         catch(PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
         } 
      }

      function checkemail($email) {
         $sql = "SELECT *
                 FROM users
                 WHERE email = :email";

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("email", $email);
         $stmt->execute(); 
         $row_count = $stmt->rowCount();
         return $row_count;
      }

      function authenticateUser($username) {
         $sql = "SELECT username, password as passwordhash
                 FROM users
                 WHERE username = :username";        

         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("username", $username);
         $stmt->execute(); 
         $row_count = $stmt->rowCount(); 

         $user = null;

         if ($row_count) {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
               $user = new User();
               $user->username = $row['username'];
               $user->password = $row['password'];
            }
         }

         return $user;
      }

      function getAllUsers() {

         $sql = "SELECT *
                 FROM users";

         $stmt = $this->db->prepare($sql);
         $stmt->execute(); 
         $row_count = $stmt->rowCount();

         $data = array();

         if ($row_count)
         {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
               $user = new User();
               $user->id = $row['id'];
               $user->username = $row['username'];
               $user->first_name = $row['first_name'];
               $user->last_name = $row['last_name'];
               $user->email = $row['email'];
               $user->mobile_phone = $row['mobile_phone'];
               $user->ic_number = $row['ic_number'];
               $user->role = $row['role'];

               array_push($data, $user);
            }
         }

         return $data;
      }
      
   }