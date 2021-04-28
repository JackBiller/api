<?php

class Request extends PadraoObjeto { 
	public function response($request, $response, $args, $container, $method='GET') { 
		global $pdo;

		if (IS_CHAVE_API) { 
			include __DIR__ . '/config/chave_api.php';
			$chaveApi = new ChaveApi($pdo, $request->getHeaders());
			if (!$chaveApi->isValid()) { 
				// $response->getBody()->write(json_encode(new FalseDebug($chaveApi->getDebug())));
				return $response->withStatus(400)->withJson(new FalseDebug($chaveApi->getDebug()));
			}
		}
		$paramUrl = $this->getParamUrl($request);
		$key = $this->returnKeyPath($paramUrl);
		if ($key == '') { 
			// $response->getBody()->write(json_encode(new FalseDebug('PATH not found')));
			return $response->withStatus(400)->withJson(new FalseDebug('PATH not found'));
		}
		$key = explode('?', $key)[0];
		$this->setLog($container, $method." '/$key' route", $key);
		$obj = require __DIR__ . '/config/' . $key . '.php';

		/*
			TYPES methodFunc:
				get
				get_id
				post
				put
				delete
		*/
		$methodFunc = strtolower($method);
		if (IS_CHAVE_API) { 
			$response = $obj->$methodFunc($request, $response, $args, $container, $chaveApi);
			// $response->getBody()->write($obj->$methodFunc($request, $response, $args, $container, $chaveApi));
		} else { 
			$response = $obj->$methodFunc($request, $response, $args, $container);
			// $response->getBody()->write($obj->$methodFunc($request, $response, $args, $container));
		}
		return $response;
	}

	private function getParamUrl($request) { 
		$paramUrl = str_replace($request->getServerParam('BASE'), '', $request->getServerParam('REQUEST_URI'));
		$paramUrl = explode('/', $paramUrl);
		array_splice($paramUrl, 0, 1);
		return $paramUrl;
	}

	private function returnKeyPath($paramUrl) { 
		global $configRoutes;
		$keys = array();
		foreach ($configRoutes as $key => $value) array_push($keys, $key);

		for ($i=0; $i < sizeof($paramUrl); $i++) { 
			$paramUrl[$i] = explode('?', $paramUrl[$i]);
			$paramUrl[$i] = $paramUrl[$i][0];

			if (in_array($paramUrl[$i], $keys)) { 
				return $paramUrl[$i];
			}
		}
		return '';
	}

	private function setLog($container, $info="Slim-Skeleton '/' route", $key='app') { 
		if ($key != 'app') {
			eval("\$container['logger'] = function(\$c) { return \$this->defineLog(\$c, '$key'); };");
		}
		$container->get('logger')->info($info);
	}

	private function defineLog($c, $fileName) { 
		$settings = $c->get('settings')['logger'];
		$logger = new \Monolog\Logger($settings['name']);
		$logger->setTimezone(new DateTimeZone('America/Sao_Paulo'));
		$logger->pushHandler(new Monolog\Handler\StreamHandler(__DIR__ . '/../logs/'.$fileName.'.log', \Monolog\Logger::DEBUG));
		return $logger;
	}
}

return new Request();

?>