<?php
   include_once('interfaces/iManufacturer.php');
   include_once('interfaces/iModel.php');
   include_once('interfaces/iCar.php');
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

      function createManufacturer(Manufacturer $manufacturer) {
         try {
            $dbs = new DbStatus();

            $dbs->status = false;
            $dbs->error = 'none';
            $dbs->lastinsertid = null;

            $manufacuter_count = $this->checkManufacturer($manufacturer);

            if ($manufacuter_count != 0) {
               $dbs->error = 'Manufacturer with the name already created';
               return $dbs;
            }

            $sql = 
            "INSERT INTO manufacturers (manufacturer_name) VALUES (:manufacturer_name)";

            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("manufacturer_name", $manufacturer->manufacturer_name);
            $stmt->execute();

            $dbs->status = true;
            $dbs->error = "none";
            $dbs->lastinsertid = $this->db->lastInsertId();

            return $dbs;
         } catch (PDOException $e) {
            $errorMessage = $e->getMessage();

            $dbs = new DbStatus();
            $dbs->status = false;
            $dbs->error = $errorMessage;

            return $dbs;
         }
      }

      function insertUser(User $user) {
         try {
            $dbs = new DbStatus();

            $dbs->status = false;
            $dbs->error = "none";
            $dbs->lastinsertid = null;

            $isValid = $this->checkUsername($user->username);

            if ($isValid != 0){
               $dbs->error = "Username already exist";
               return $dbs;
            }

            $isValid = $this->checkemail($user->email);
            
            if ($isValid != 0){
               $dbs->error = "Email already exist";
               return $dbs;
            }

            $sql = "INSERT INTO users(
                     first_name, last_name, username, password, email, 
                     role, ic_number, mobile_phone
                  ) 
                  VALUES (:first_name, :last_name, 
                     :username, :password, :email, 
                     :role, :ic_number, :mobile_phone
                  )";

            $stmt = $this->db->prepare($sql);  
            $stmt->bindParam("first_name", $user->first_name);
            $stmt->bindParam("last_name", $user->last_name);
            $stmt->bindParam("username", $user->username);
            $stmt->bindParam("password", salt($user->password));
            $stmt->bindParam("email", $user->email);
            $stmt->bindParam("role", $user->role);
            $stmt->bindParam("ic_number", $user->ic_number);
            $stmt->bindParam("mobile_phone", $user->mobile_phone);
            $stmt->execute();

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

      function checkUsername($username) {
         $sql = "SELECT * FROM users WHERE username = :username";
         
         $stmt = $this->db->prepare($sql);
         $stmt->bindParam("username", $username);
         $stmt->execute();
         $row_count = $stmt->rowCount();
         return $row_count;
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
         $sql = "SELECT username, password, role
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
               $user->role = $row['role'];
            }
         }

         return $user;
      }

      function getAllCars() {

         $sql = "SELECT 
            c.id as car_id, 
            c.plate_number, 
            c.price_per_hour,
            c.year,
            m.model_name, 
            ma.manufacturer_name
            FROM cars c
            join models m on c.model_id = m.id
            join manufacturers ma on m.manufacturer_id = ma.id";

         $stmt = $this->db->prepare($sql);
         $stmt->execute(); 
         $row_count = $stmt->rowCount();

         $data = [];

         if ($row_count)
         {
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
               $manufacturer = new Manufacturer();
               $manufacturer->manufacturer_name = $row['manufacturer_name'];
               $model = new Model($manufacturer);
               $model->model_name = $row['model_name'];
               $car = new Car($model);
               $car->plate_number = $row['plate_number'];
               $car->id = $row['car_id'];
               $car->price_per_hour = $row['price_per_hour'];
               $car->year = $row['year'];

               array_push($data, $car);
            }
         }

         return $data;
      }

      function checkManufacturer(Manufacturer $manufacturer) {
         $sql = "SELECT * FROM manufacturers where manufacturer_name = :manufacturer_name LIMIT 1";

         $statement = $this->db->prepare($sql);
         $statement->bindParam('manufacturer_name', $manufacturer->manufacturer_name);
         $statement->execute();
         return $statement->rowCount();
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