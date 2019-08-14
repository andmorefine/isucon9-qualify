<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\StatusCode;

return function (App $app) {
    $container = $app->getContainer();

    $app->get('/', function (Request $request, Response $response, array $args) use ($container) {
        return $container->get('renderer')->render($response, 'index.html');
    });

    $app->get('/new_items.json', \App\Service::class . ':new_items');
    $app->get('/new_items/{id}.json', \App\Service::class . ':new_category_items');
    $app->get('/users/{id}.json', \App\Service::class . ':user_items');
    $app->get('/users/transactions', \App\Service::class . ':transactions');
    $app->get('/items/{id}.json', \App\Service::class . ':item');
    $app->post('/items/edit', \App\Service::class . ':edit');
    $app->post('/buy', \App\Service::class . ':buy');
    $app->post('/sell', \App\Service::class . ':sell');
    $app->post('/ship', \App\Service::class . ':ship');
    $app->post('/ship_done', \App\Service::class . ':ship_done');
    $app->post('/complete', \App\Service::class . ':complete');
    $app->get('/transactions/{id}.png', \App\Service::class . ':qrcode');
    $app->post('/bump', \App\Service::class . ':bump');
    $app->get('/settings', \App\Service::class . ':settings');
    $app->post('/login', \App\Service::class . ':login');
    $app->post('/register', \App\Service::class . ':register');

    // to serve as a static file, for anything else
    $app->get('/{name:.+}', function (Request $request, Response $response, array $args) use ($container) {
        // static
        $template = $container->get('renderer')->getTemplatePath();
        $path = $template . $args['name'];

        if (! is_readable($path)) {
            return $response->withStatus(StatusCode::HTTP_NOT_FOUND);
        }

        // find webapp/public -type d -name '.git' -prune -o -type f -exec basename {} \; | grep -o '\.[^.]*$' | sort | uniq | grep -v git
        $content_type_map = [
            'css' => 'text/css',
            'html' => 'text/html',
            'ico' => 'image/x-icon',
            'png' => 'image/png',
            'js' => 'application/javascript',
            'json' => 'application/json ',
            'map' => 'application/json ', // source map, main.js.map
        ];

        $ext = pathinfo($path, PATHINFO_EXTENSION);
        if (array_key_exists($ext, $content_type_map)) {
            $mimeType = $content_type_map[$ext];
        } else {
            $mimeType = 'application/octet-stream';
        }

        $response->getBody()->write(file_get_contents($path));
        return $response->withHeader('Content-Type', $mimeType);
    })->setName('static');
};
