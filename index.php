<?php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


use Model\User;
use Controller\UserController;
/*
$objUserCtrl = new UserController();
$objUser = new User();
*/

//Configuratios
$config['displayErrorDetails'] = true;


/** *******************************************************************
 * Step 1: Require the Slim Framework using Composer's autoloader
 *
 * If you are not using Composer, you need to load Slim Framework with your own
 * PSR-4 autoloader.
 **********************************************************************/
require 'vendor/autoload.php';


/** *******************************************************************
 * Step 2: Instantiate a Slim application
 *
 * This example instantiates a Slim application using
 * its default settings. However, you will usually configure
 * your Slim application now by passing an associative array
 * of setting names and values into the application constructor.
 **********************************************************************/
$app = new Slim\App(["settings" => $config]);

//Getting the containers
$container = $app->getContainer();

/** ******************************************************************* 
 * Dependecy Injection Container (DIC) - START
 **********************************************************************/
//Adding Monolog logger to Container
$container['logger'] = function ($c) {
	$logger = new \Monolog\Logger('foodtrip_log');
	$file_handler = new \Monolog\Handler\StreamHandler("logs/app.log");
	$logger->pushHandler($file_handler);
	return $logger;
};
//Adding Database connection to Container
$container['db'] = function ($c) {	
	$dsn = 'mysql:host=localhost;dbname=foodtrip;charset=utf8';
	$usr = 'root';
	$pwd = '';	
	$pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);	
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);	
	return $pdo;
};
/** *******************************************************************
 * Dependecy Injection Container (DIC) - END
 **********************************************************************/

/** *******************************************************************
 * Middleware  - START
 **********************************************************************/
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
	// Use the PSR 7 $request object
	
	//Logging Here
	$this->logger->addInfo("This is an INFO Log");
	//var_dump($request->getQueryParams());die;
	
	return $next($request, $response);
});
/** *******************************************************************
 * Middleware  - END
 **********************************************************************/

/** *******************************************************************
 * Step 3: Define the Slim application routes
 *
 * Here we define several Slim application routes that respond
 * to appropriate HTTP request methods. In this example, the second
 * argument for `Slim::get`, `Slim::post`, `Slim::put`, `Slim::patch`, and `Slim::delete`
 * is an anonymous function.
 ***********************************************************************/
//API - index
$app->get('/', function ($request, $response, $args) {
    $response->write("Welcome to Slim!");    
    return $response;
});


//API - hello
$app->get('/hello[/{name}]', function ($request, $response, $args) {
	$arrResponse = array('lname' => 'Johannes', 'lname' => 'Mabulay');
	
    $response->write("Hello, " . ucfirst(strtolower($args['name'])));
    $response->withHeader(
    			'Content-Type',
    			'application/json'
    	);    
       
    //Sampler logs
    $this->logger->addInfo("This is an INFO Log");
    $this->logger->addError("This is an ERROR Log");
    $this->logger->addWarning("This is a WARNING log!!!");
    
    return $response;
    
})->setArgument('name', 'World!');


//API - users
require 'app/routes/user.php';



/** *******************************************************************
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 ***********************************************************************/
$app->run();
