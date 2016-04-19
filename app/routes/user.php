<?php
$app->get('/users[/{varName}]', function (ServerRequestInterface $request, $response, $args) {

	//Calling UserController and passing db connections
	$objUserController = new UserController($this->db);
	$arrUser = $objUserController->users($args['varName']);
	$response->getBody()->write(var_export($arrUser, true));
	//var_dump($request);
	return $response;
});