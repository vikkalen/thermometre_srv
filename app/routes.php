<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use App\Core\TxPayload;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {

    $app->post('/login/', function (Request $request, Response $response, array $args) {

        $data = $request->getParsedBody();

        if(!isset($data['name']) || !isset($data['password']))
        {
            return $response->withStatus(422);
        }

        $name = $data['name'];
        $password = $data['password'];

        if(!$this->get('db')->checkUser($name, $password))
        {
            return $response->withStatus(401);
        }

        $outputPayload = array('token' => $this->get(SettingsInterface::class)->get('auth_token'));

        $response->getBody()->write(json_encode(['data' => $outputPayload, 'status' => 'OK']));
	
	return $response;
    });

    $app->post('/rx/', function (Request $request, Response $response, array $args) {

        $data = $request->getParsedBody();
        if(!$data || !isset($data['data']))
        {
            $this->get(LoggerInterface::class)->info(var_export($data, true));
            return $response->withStatus(422);
        }

        $payload = $this->get('tx')->input($data['data']);

        $rrdData = array(
            'temperature' => $payload->temp,
            'voltage' => $payload->supplyV,
            'intensity' => $payload->sig
        );
        $rrd = $this->get('rrd');
        $rrd->update($rrdData, $payload->sonde);

        $outputPayload = new TxPayload($payload->sonde, TxPayload::FLAG_ACK);

        $response->getBody()->write(json_encode(['data' => $this->get('tx')->output($outputPayload), 'status' => 'OK']));

	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->get('/info/{sonde}/', function (Request $request, Response $response, array $args) {

        $sonde = $args['sonde'];
        $info = $this->get('rrd')->info($sonde);

        $response->getBody()->write(json_encode(['data' => $info, 'status' => 'OK']));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));

    $app->get('/graph/{probe}/{periode}/', function (Request $request, Response $response, array $args) {

        $probe = $args['probe'];
	$periode = $args['periode'];
	$params = $request->getQueryParams();
        $width = $params['width'] ?? null;
        $height = $params['height'] ?? null;

        if(!$width || !$height)
        {
            $this->get(LoggerInterface::class)->info("No dimensions given to graph");
            return $response->withStatus(422);
        }

        date_default_timezone_set($this->get(SettingsInterface::class)->get('tz'));
        $graph = $this->get('rrd')->graph($probe, $periode, $width, $height, $this->get('json')->read());

        $response->getBody()->write(json_encode(['data' => ['mediatype' => 'image/png', 'data' => base64_encode($graph)], 'status' => 'OK']));
	
	return $response;
    })->add($app->getContainer()->get('token_guard'));


};


