<?php
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

//use Model\User;
//use Controller\UserController;

/*********Configs - START ***************/
date_default_timezone_set('Asia/Singapore');
setlocale(LC_MONETARY, 'en_PH');
define('ROOT_DIR', dirname(__FILE__));
/*********Configs - END ***************/

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
//Adding Database connection to Container
$container['db'] = function ($c) {

	$dsn = 'mysql:host='.$_ENV['MYSQL_HOST'].';dbname='.$_ENV['MYSQL_DB'].';charset=utf8';
	$usr = $_ENV['MYSQL_USER'];
	$pwd = $_ENV['MYSQL_PWD'];	
	
	$pdo = new \Slim\PDO\Database($dsn, $usr, $pwd);
	$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
	return $pdo;
};

//Adding Monolog logger to Container
$container['logger'] = function ($c) {
	$logger = new \Monolog\Logger('foodtrip_log');
	$file_handler = new \Monolog\Handler\StreamHandler("logs/app.log");
	$logger->pushHandler($file_handler);
	return $logger;
};

//Adding JWT to Container
use \Firebase\JWT\JWT;
$container["jwt"] = function ($container) {
	return new JWT;
};

/* Paypal Setup - START*/
//Injecting Paypal Payment
use \PayPal\Api\Payment;
$container["PaypalPayment"] = function ($container) {
	return new Payment;
};

//Injecting Paypal ApiContext
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
$container["PaypalApiContext"] = function ($container) {
	//TODO: put it in an environment variables
	$clientId = 'AcN1vThV_mxNQ2H2PQnQqugHTtup_wmS9nO6CYrO0OT1zkM18RxvLHcgUE39thiq8ugQqdqu7faR20GN';	 
	$clientSecret = 'EI4mfCDgr8uTr5Nbme0W5mu9mmBXRo_p52SCs9a7WzSDPp44-NI_b_k9lIPU8hMdMOYiidFS8URCLMWO';

	$apiContext = new ApiContext(
			new OAuthTokenCredential(
					$clientId,
					$clientSecret
			)
	);
	
	$apiContext->setConfig(
		array(
				'mode' => 'sandbox',
				'log.LogEnabled' => true,
				'log.FileName' => 'logs/PayPal.log',
				'log.LogLevel' => 'DEBUG', // PLEASE USE `INFO` LEVEL FOR LOGGING IN LIVE ENVIRONMENTS
				//'cache.enabled' => true,
				// 'http.CURLOPT_CONNECTTIMEOUT' => 30
				// 'http.headers.PayPal-Partner-Attribution-Id' => '123123123'
				//'log.AdapterFactory' => '\PayPal\Log\DefaultLogFactory' // Factory class implementing \PayPal\Log\PayPalLogFactory
		)
	);	
	
	return $apiContext;
};
/* Paypal Setup - END */

//Inject manifest.xml config
$container['manifest'] = function ($c) {
	$xml = simplexml_load_file("app/manifest.xml") or die("Error: Cannot create object");
	return $xml;
};

//Inject User Utility Class
use Utilities\UserUtil;
$container['UserUtil'] = function ($c) {
	$utilities = new UserUtil( $c->db, $c->jwt );
	return $utilities;
};

//Inject User Upload Class
use Utilities\UploadUtil;
$container['UploadUtil'] = function ($c) {
	$objUtil = new UploadUtil($c->db );
	return $objUtil;
};

//Inject MenuUtil Class
use Utilities\MenuUtil;
$container['MenuUtil'] = function ($c) {
	$objUtil = new MenuUtil($c->db);
	return $objUtil;
};

//Inject TransacUtil Class
use Utilities\TransacUtil;
$container['TransacUtil'] = function ($c) {
	$objUtil = new TransacUtil($c->db);
	return $objUtil;
};

//Inject NotificationUtil Class
use Utilities\NotificationUtil;
$container['NotificationUtil'] = function ($c) {
	if (empty($c->jwtToken)) $c->jwtToken = null;
	$objUtil = new NotificationUtil($c->db, $c->jwtToken, $c->manifest);
	return $objUtil;
};

/** *******************************************************************
 * Dependecy Injection Container (DIC) - END
 **********************************************************************/

/** *******************************************************************
 * Middleware  - START
 **********************************************************************/

$app->add(new \Slim\Middleware\JwtAuthentication([
    "secret" => "supersecretkeyyoushouldnotcommittogithub", //TODO: Use https://github.com/vlucas/phpdotenv
    "secure" => true,
    "relaxed" => ["localhost", "foodtriph-api.herokuapp.com"],
	//"logger" => $logger,
		
	"rules" => [
		new \Slim\Middleware\JwtAuthentication\RequestPathRule([
			"path" => "/",
			"passthrough" => ["/user/login","/user/add","/user/verify/","/user/password/reset"],
		]),
		/*
		new \Slim\Middleware\JwtAuthentication\RequestMethodRule([
					"passthrough" => ["OPTIONS"]
		])
		*/
	],		
		
    "callback" => function ($request, $response, $arguments) use ($container) {
        $container["jwtToken"] = $arguments["decoded"];
    },
    
    "error" => function ($request, $response, $arguments) {
    	$data["status"] = false;
    	$data["message"] = $arguments["message"];
    	return $response->withJson($data,200);
    }    
    
]));


$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
	// Use the PSR 7 $request object
	
// 	var_dump($request->getMethod());
// 	var_dump('POST: ' . $request->isPost());
// 	var_dump('GET: ' . $request->isGet());
// 	die;
	//Logging Here
    //Sampler logs
    
    $this->logger->addInfo("This is an INFO Log");
    $this->logger->addError("This is an ERROR Log");
    $this->logger->addWarning("This is a WARNING log!!!");
    
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
       
    return $response;
    
})->setArgument('name', 'World!');


//API - user
require 'app/routes/user.php';

//API - address
require 'app/routes/address.php';

//API - menu
require 'app/routes/menu.php';

//API - transac
require 'app/routes/transac.php';

//API - vendor
require 'app/routes/provider.php';

//API - vendor address
require 'app/routes/provider_address.php';

//API - uploads
$app->post('/uploads[/{type}]', function ($request, $response, $args) {

	$file = $this->UploadUtil->upload($args, $request);

	if ( $file === false) {
		$response->withJson( array("status"=>false, "message"=>"Upload Error!"),500);
		return $response;		
	}
	
	$data = array(
			'name'       => $file->getNameWithExtension(),
			'extension'  => $file->getExtension(),
			//'mime'       => $file->getMimetype(),
			'size'       => $file->getSize(),
			//'md5'        => $file->getMd5(),
			//'dimensions' => json_encode($file->getDimensions())
	);	

	$response->withJson(array('status'=>true, 'data'=>$data),200);

});


/** *******************************************************************
 * Step 4: Run the Slim application
 *
 * This method should be called last. This executes the Slim application
 * and returns the HTTP response to the HTTP client.
 ***********************************************************************/
$app->run();
