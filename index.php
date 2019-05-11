<?php
   ini_set("date.timezone", "Asia/Kuala_Lumpur");

   header('Access-Control-Allow-Origin: *');   

   //*
   // Allow from any origin
   if (isset($_SERVER['HTTP_ORIGIN'])) {
      // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
      // you want to allow, and if so:
      header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
      header('Access-Control-Allow-Credentials: true');
      header('Access-Control-Max-Age: 86400');    // cache for 1 day
   }

   // Access-Control headers are received during OPTIONS requests
   if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
         header("Access-Control-Allow-Methods: GET, POST, DELETE, PUT, OPTIONS");         

      if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
         header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

      exit(0);
   }
   //*/

   require_once 'vendor/autoload.php';

   use \Psr\Http\Message\ServerRequestInterface as Request;
   use \Psr\Http\Message\ResponseInterface as Response;

   use Ramsey\Uuid\Uuid;
   use Ramsey\Uuid\Exception\UnsatisfiedDependencyException;

   include_once("salt_pepper.php");
   include_once("database_class.php");  

   //load environment variable - jwt secret key
   $dotenv = new Dotenv\Dotenv(__DIR__);
   $dotenv->load();

   //jwt secret key in case dotenv not working in apache
   //$jwtSecretKey = "jwt_secret_key";

   function getDatabase() {
      $dbhost= getenv('DB_HOST');
      $dbuser= getenv('DB_USERNAME');
      $dbpass= getenv('DB_PASSWORD');
      $dbname= getenv('DB_TABLE');

      $db = new Database($dbhost, $dbuser, $dbpass, $dbname);
      return $db;
   }

   use Slim\App;
   use Slim\Middleware\TokenAuthentication;
   use Firebase\JWT\JWT;

   function getUsernameTokenPayload($request, $response) {
      $token_array = $request->getHeader('HTTP_AUTHORIZATION');
      $token = substr($token_array[0], 7);

      //decode the token
      try
      {
         $tokenDecoded = JWT::decode(
            $token, 
            getenv('JWT_SECRET'), 
            array('HS256')
         );
      }
      catch(Exception $e)
      {
         $data = Array(
            "jwt_status" => "token_invalid"
         ); 

         return $response->withJson($data, 401)
                         ->withHeader('Content-tye', 'application/json');
      }

      return $tokenDecoded->username;
   }

   function getIdTokenPayload($request, $response) {
      $token_array = $request->getHeader('HTTP_AUTHORIZATION');
      $token = substr($token_array[0], 7);

      //decode the token
      try
      {
         $tokenDecoded = JWT::decode(
            $token, 
            getenv('JWT_SECRET'), 
            array('HS256')
         );
      }
      catch(Exception $e)
      {
         $data = Array(
            "jwt_status" => "token_invalid"
         ); 

         return $response->withJson($data, 401)
                         ->withHeader('Content-tye', 'application/json');
      }

      return $tokenDecoded->id;
   }

   function getRoleTokenPayload($request, $response) {
      $token_array = $request->getHeader('HTTP_AUTHORIZATION');
      $token = substr($token_array[0], 7);

      //decode the token
      try
      {
         $tokenDecoded = JWT::decode(
            $token, 
            getenv('JWT_SECRET'), 
            ['HS256']
         );
      }
      catch(Exception $e)
      {
         $data = Array(
            "jwt_status" => "token_invalid"
         ); 

         return $response->withJson($data, 401)
                         ->withHeader('Content-tye', 'application/json');
      }

      return $tokenDecoded->role;
   }

   $config = [
      'settings' => [
         'displayErrorDetails' => true
      ]
   ];

   $app = new App($config);

   $authenticator = function($request, TokenAuthentication $tokenAuth){

      /**
         * Try find authorization token via header, parameters, cookie or attribute
         * If token not found, return response with status 401 (unauthorized)
      */
      $token = $tokenAuth->findToken($request); //from header

      try {
         $tokenDecoded = JWT::decode($token, getenv('JWT_SECRET'), array('HS256'));

         //in case dotenv not working
         //$tokenDecoded = JWT::decode($token, $GLOBALS['jwtSecretKey'], array('HS256'));
      }
      catch(Exception $e) {
         throw new \app\UnauthorizedException('Invalid Token');
      }
   };

   /**
     * Add token authentication middleware
     */
   $app->add(new TokenAuthentication([
        'path' => '/', //secure route - need token
        'passthrough' => [ //public route, no token needed
            '/registration',
            '/checkemail',
            '/auth',
            '/users',
            '/get_all_cars',
         ],
         'secure' => false,
        'authenticator' => $authenticator
   ]));
   /**
     * Public route /registration for member registration
     */
   $app->post('/registration', function($request, $response){
      $json = json_decode($request->getBody());
      $user = new User();
      $user->username = $json->username;
      $user->first_name = $json->first_name;
      $user->last_name = $json->last_name;
      $user->email = $json->email;
      $user->password = $json->password;
      $user->role = 'member';
      $user->ic_number = $json->ic_number;
      $user->mobile_phone = $json->mobile_phone;

      //insert user
      $db = getDatabase();
      $dbs = $db->insertUser($user);
      $db->close();

      $data = array(
         "insertstatus" => $dbs->status,
         "error" => $dbs->error
      ); 

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   });

   $app->get('/get_all_cars', function($request, $response, $args) {
      $db = getDatabase();
      $cars = $db->getAllCars();
      $db->close();

      if (sizeof($cars) == 0) {
         $returnData = [
            message => 'No cars available'
         ];
         return $response->withJson($returnData, 200)
                      ->withHeader('Content-type', 'application/json'); 
      }

      return $response->withJson($cars, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   $app->get('/get_all_manufacturers', function($request, $response) {
      $db = getDatabase();
      $manufacturers = $db->getAllManufacturers();
      $db->close();

      if (sizeof($manufacturers) == 0) {
         $returnData = [
            message => 'No manufacturers available'
         ];
         return $response->withJson($returnData, 200)
                      ->withHeader('Content-type', 'application/json'); 
      }

      return $response->withJson($manufacturers, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   $app->get('/get_all_models', function($request, $response) {
      $db = getDatabase();
      $models = $db->getAllmodels();
      $db->close();

      if (sizeof($models) == 0) {
         $returnData = [
            message => 'No models available'
         ];
         return $response->withJson($returnData, 200)
                      ->withHeader('Content-type', 'application/json'); 
      }

      return $response->withJson($models, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   /**
     * Public route /checkemail/:email for checking email availability
     * in member registration
     */
   $app->get('/checkemail/[{email}]', function($request, $response, $args){

      $email = $args['email'];

      $db = getDatabase();
      $status = $db->checkemail($email);
      $db->close();

      $data = array();

      if ($status) {
         $data = array(
            'exist' => true
         ); 
      } else {
         $data = array(
            "exist" => false
         );          
      }

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   }); 

   /**
     * Public route /auth for creds authentication
     */
   $app->post('/auth', function($request, $response){
      //extract form data - email and password
      $json = json_decode($request->getBody());
      $username = $json->username;
      $clearpassword = $json->password;

      //do db authentication
      $db = getDatabase();
      $data = $db->authenticateUser($username);
      $db->close();

      //status -1 -> user not found
      //status 0 -> wrong password
      //status 1 -> login success

      $returndata = [];

      //user not found
      if ($data === NULL) {
         $returndata = [
            'status' => -1
         ];     
      }
      //user found
      else {
         //now verify password hash - one way mdf hash using salt-peper
         if (pepper($clearpassword, $data->password)) {
            //correct password
      
            //create JWT token
            $date = date_create();
            $jwtIAT = date_timestamp_get($date);
            $jwtExp = $jwtIAT + (60 * 60 * 12); //expire after 12 hours

            $jwtToken = [
               "iss" => "car_rental_system.net", //token issuer
               "iat" => $jwtIAT, //issued at time
               "exp" => $jwtExp, //expire
               "role" => $data->role,
               "id" => $data->id
            ];
            $token = JWT::encode($jwtToken, getenv('JWT_SECRET'));

            $returndata = array(
               'status' => 1,
               'token' => $token,
               'role' => $data->role,
            );                
         } else {
            //wrong password
            $returndata = array(
               'status' => 0
            ); 
         }
      }  

      return $response->withJson($returndata, 200)
                      ->withHeader('Content-type', 'application/json');    
   }); 

   //POST - INSERT CONTACT - secure route - need token
   $app->post('/contacts', function($request, $response){

      $ownerlogin = getUsernameTokenPayload($request, $response);  
      
      //form data
      $json = json_decode($request->getBody());
      $name = $json->name;
      $email = $json->email;
      $mobileno = $json->mobileno;

      $db = getDatabase();
      $dbs = $db->insertContact($name, $email, $mobileno, $ownerlogin);
      $db->close();

      $data = array(
         "insertstatus" => $dbs->status,
         "error" => $dbs->error
      ); 

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   });

   $app->post('/create_car', function ($request, $response) {
      $json = json_decode($request->getBody());
      $model = new Model();
      $model->id = $json->model_id;
      $car = new Car($model);
      $car->plate_number = strtolower(trim($json->plate_number));
      $car->price_per_hour = $json->price_per_hour;
      $car->year = $json->year;
      
      if (!$car->checkNull())
         return $response->withJson('One of the inputs are null', 406)->withHeader('Content-Type', 'application/json');

      $role = getRoleTokenPayload($request, $response);

      if ($role != 'admin') {
         return $response->withJson('Unauthorized', 401)->withHeader('Content-Type', 'application/json');
      }

      $db = getDatabase();
      $insertStatus = $db->createCar($car);
      $db->close();

      return $response->withJson($insertStatus, 200)->withHeader('Content-Type', 'application/json');

   });

   $app->post('/create_manufacturer', function ($request, $response) {
      $json = json_decode($request->getBody());
      $manufacturer = new Manufacturer();
      $manufacturer->manufacturer_name = strtolower(trim($json->manufacturer_name));

      if (!$manufacturer->manufacturer_name)
         return $response->withJson('Manufacturer name cannot be null', 406)->withHeader('Content-Type', 'application/json');

      $role = getRoleTokenPayload($request, $response);

      if ($role != 'admin') {
         return $response->withJson('Unauthorized', 401)->withHeader('Content-Type', 'application/json');
      }

      $db = getDatabase();
      $insertStatus = $db->createManufacturer($manufacturer);
      $db->close();

      return $response->withJson($insertStatus, 200)->withHeader('Content-Type', 'application/json');

   });

   $app->post('/create_model', function ($request, $response) {
      $json = json_decode($request->getBody());
      $manufacturer = new Manufacturer();
      $manufacturer->id = $json->manufacturer_id;
      $model = new Model($manufacturer);
      $model->model_name = $json->model_name;

      if (!$model->model_name)
         return $response->withJson('Model name cannot be null', 406)->withHeader('Content-Type', 'application/json');

      $role = getRoleTokenPayload($request, $response);

      if ($role != 'admin') {
         return $response->withJson('Unauthorized', 401)->withHeader('Content-Type', 'application/json');
      }

      $db = getDatabase();
      $insertStatus = $db->createModel($model);
      $db->close();
      return $response->withJson($insertStatus, 200)->withHeader('Content-Type', 'application/json');

   });

   $app->post('/create_order', function ($request, $response) {
      $db = getDatabase();
      $json = json_decode($request->getBody());
      $car = $db->getCarById($json->car_id);
      $hour = $json->hour;
      return $response->withJson($json, 200)->withHeader('Content-Type', 'application/json');
      
      $total_price = $hour * $car->price_per_hour;
      $user = new User();
      $user->id = getIdTokenPayload($request, $response);
      $order = new Order($car, $user);
      $order->total_price = $total_price;
      $order->date_from = $json->date_from;
      $order->date_to = $json->date_to;
      $order->time_from = $json->time_from;
      $order->time_to = $json->time_to;

      $insertStatus = $db->createOrder($order);
      $db->close();
      return $response->withJson($insertStatus, 200)->withHeader('Content-Type', 'application/json');

   });

   //GET - ALL USERS
   $app->get('/users', function($request, $response){

      $db = getDatabase();
      $data = $db->getAllUsers();
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });      

   //GET - ALL CONTACTS
   $app->get('/contacts', function($request, $response){

      $ownerlogin = getLoginTokenPayload($request, $response);  

      $db = getDatabase();
      $data = $db->getAllContactsViaLogin($ownerlogin);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //GET - SINGLE CONTACT VIA ID
   $app->get('/contacts/[{id}]', function($request, $response, $args){

      //get owner login - to prevent rolling no hacking
      $ownerlogin = getLoginTokenPayload($request, $response);  
      
      $id = $args['id'];

      $db = getDatabase();
      $data = $db->getContactViaId($id, $ownerlogin);
      $db->close();

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json'); 
   }); 

   //PUT - UPDATE SINGLE CONTACT VIA ID
   $app->put('/contacts/[{id}]', function($request, $response, $args){
     
      $id = $args['id'];

      //form data
      $json = json_decode($request->getBody());
      $name = $json->name;
      $email = $json->email;
      $mobileno = $json->mobileno;

      $db = getDatabase();
      $dbs = $db->updateContactViaId($id, $name, $email, $mobileno);
      $db->close();

      $data = Array(
         "updatestatus" => $dbs->status,
         "error" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');
   });

   //DELETE - SINGLE CONTACT VIA ID
   $app->delete('/contacts/[{id}]', function($request, $response, $args){

      $id = $args['id'];

      $db = getDatabase();
      $dbs = $db->deleteContactViaId($id);
      $db->close();

      $data = Array(
         "deletestatus" => $dbs->status,
         "error" => $dbs->error
      );

      return $response->withJson($data, 200)
                      ->withHeader('Content-type', 'application/json');     
   });

   $app->run();