<?php

use Slim\Http\Request;
use Slim\Http\Response;

use App\TxPayload;

// Routes

$app->post('/login/', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();

    if(!isset($data['name']) || !isset($data['password']))
    {
        return $response->withStatus(422);
    }

    $name = $data['name'];
    $password = $data['password'];

    if(!$this->db->checkUser($name, $password))
    {
        return $response->withStatus(401);
    }

    $outputPayload = array('token' => $this->settings['auth_token']);

    return $response->withJson(['data' => $outputPayload, 'status' => 'OK']);
});


$app->post('/rx/', function (Request $request, Response $response, array $args) {

    $data = $request->getParsedBody();
    if(!$data || !isset($data['data']))
    {
        $this->logger->info(var_export($data, true));
        return $response->withStatus(422);
    }

    $payload = $this->tx->input($data['data']);

    $rrdData = array(
        'temperature' => $payload->temp,
        'voltage' => $payload->supplyV,
        'intensity' => $payload->sig
    );
    $rrd = $this->rrd;
    $rrd->update($rrdData, $payload->sonde);

    $outputPayload = new TxPayload($payload->sonde, TxPayload::FLAG_ACK);

    return $response->withJson(['data' => $this->tx->output($outputPayload), 'status' => 'OK']);
})->add($app->getContainer()->token_guard);

$app->get('/info/{sonde}/', function (Request $request, Response $response, array $args) {

    $sonde = $args['sonde'];
    $info = $this->rrd->info($sonde);

    return $response->withJson(['data' => $info, 'status' => 'OK']);
})->add($app->getContainer()->token_guard);

$app->get('/graph/{probe}/{periode}/', function (Request $request, Response $response, array $args) {

    $probe = $args['probe'];
    $periode = $args['periode'];
    $width = $request->getQueryParam('width');
    $height = $request->getQueryParam('height');

    if(!$width || !$height)
    {
        $this->logger->info("No dimensions given to graph");
        return $response->withStatus(422);
    }

    $graph = $this->rrd->graph($probe, $periode, $width, $height, $this->json->read());

    return $response->withJson(['data' => ['mediatype' => 'image/png', 'data' => base64_encode($graph)], 'status' => 'OK']);
})->add($app->getContainer()->token_guard);
