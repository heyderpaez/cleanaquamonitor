<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->get('/tiemporeal', function() use($app) {
  $app['monolog']->addDebug('logging output.');

  $dbconn = pg_connect("host=ec2-54-243-52-209.compute-1.amazonaws.com port=5432 dbname=d27bt4i4fpppbi user=vqerrktdlmffck password=uAzCqG1pvUCjIrYtfJti5BatTC");

  $consultaDB = pg_query($dbconn, "SELECT * FROM clean-aqua-db ORDER BY id DESC LIMIT 1");
  $datosRecibidos = pg_fetch_row($consultaDB);


    return $app['twig']->render('tiemporeal.twig', array(
        'fechaActualizacion' => $datosRecibidos[1],
        'nivelTanque' => 60,
        'estadoMotor' => $datosRecibidos[3],
        'ph' => $datosRecibidos[4],
    ));
});

$app->get('/guardar/{nivelTanque}/{stateMotor}/{ph}', function ($nivelTanque, $stateMotor, $ph) use ($app) {

    $dbconn = pg_connect("host=ec2-54-243-52-209.compute-1.amazonaws.com port=5432 dbname=d27bt4i4fpppbi user=vqerrktdlmffck password=uAzCqG1pvUCjIrYtfJti5BatTC");

    $fecha = strtotime ( '-5 hour' , strtotime ( date('Y-m-d H:i:s', time()) ) );

    $valores = array(
    "date" => date('Y-m-d H:i:s',$fecha),
    "level" => $nivelTanque,
    "state_motor" => $stateMotor,
    "ph" => $ph,
    );

    $resultado = pg_insert($dbconn, 'clean-aqua-db', $valores);

    return "<p>Ok</p>";
});

$app->get('/requestArduino', function () use ($app) {

    $data = file_get_contents('http://181.51.212.137:9000');
    $decodedData = json_decode($data);

    return var_dump($decodedData);
});


$app->run();
