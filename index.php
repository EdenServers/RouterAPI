<?php
require 'vendor/autoload.php';
require 'vendor/redbean/rb.php';

/* Initialisations */
$app = new \Slim\Slim();
R::setup('sqlite:database.db'); //sqlite database


$app->get('/routes', function () use ($app) {
    $routes = R::find('routes');
    $app->response()->header('Content-Type', 'application/json');
    echo json_encode(R::exportAll($routes));
});

// handle POST requests to /routes
$app->post('/routes', function () use ($app) {
    try {
        // Create route from params
        $request = $app->request();

        //get params from request
        $address = $request->post('address');
        $port = $request->post('port');
        $ip = $request->port('ip');

        //Create a new record
        $route = R::dispense('routes');
        $route->address = (string)$address;
        $route->port = intval($port);
        $route->ip = (string)$ip;
        $id = R::store($route);

        //Create the file in nginx
        file_put_contents("/etc/nginx/sites-enabled/$address", fileData($address, $port, $ip));

        //Display the created record
        $route = R::findOne('routes', 'id=?', array($id));
        $props = $route->getProperties();
        echo json_encode($props);

    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

$app->delete('/routes/:id', function ($id) use ($app) {
    try
    {
        $route = R::load('routes', $id);
        unlink("/etc/nginx/sites-enabled/$route->address");
        R::trash($route);
    } catch (Exception $e) {
        $app->response()->status(400);
        $app->response()->header('X-Status-Reason', $e->getMessage());
    }
});

/* Returns a string with the data to be in the file */
function fileData($address, $port, $ip){
    return "server {
                listen 80;
                location /$address/ {
                    proxy_pass http://$ip:$port/;
                }
            }";
}

$app->run();
