<?php

declare(strict_types=1);

// General Routes
$app->get('/', 'App\Controller\Hello:getStatusAPI')->setName('main');

// Authentication Routes
$app->post('/auth/signin', 'App\Controller\Auth:signin');

// User Routes
$app->get('/users/info', 'App\Controller\Users:info');