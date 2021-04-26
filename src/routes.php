<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

include 'constConfig.php';

include 'funcoes.php';
include 'funcoesApi.php';

$pdo = getConection();

include 'configRoutes.php';

return function(App $app) { 
	$container = $app->getContainer();

	global $configRoutes;

	foreach ($configRoutes as $key => $value) { 
		foreach ($value as $keyValue => $valueValue) {
			if ($valueValue) { 
				$keyRef = str_replace('is', '', strtolower($keyValue));
				$method = $keyRef == 'get_id' ? 'get' : $keyRef;
				$urlComp = in_array($keyRef, array('get','post')) ? '' : '/{id}';

				$app->$method('/'.$key.$urlComp, function(Request $request, Response $response, array $args) use ($container) { 
					$requestObj = require __DIR__ . '/Request.php';

					$comp = '';
					$keysAgrs = array_keys($args);
					for ($i=0; $i < sizeof($keysAgrs); $i++) { 
						if ($keysAgrs[$i] == 'id') $comp = '_ID';
					}

					$returnResponse = $requestObj->response($request, $response, $args, $container, $request->getMethod().$comp);
					return $returnResponse;
				});
			}
		}
	}

	$app->get('/hello/{name}', function (Request $request, Response $response, array $args) { 
		$name = $args['name'];
		$response->getBody()->write("Hello, $name");
		return $response;
	});

	$app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) { 
		$container->get('logger')->info("Slim-Skeleton '/' route"); // Sample log message
		return $container->get('renderer')->render($response, 'index.phtml', $args); // Render index view
	});
};
