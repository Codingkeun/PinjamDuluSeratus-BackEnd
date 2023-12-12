<?php

declare(strict_types=1);

// General Routes
$app->get('/', 'App\Controller\Hello:getStatusAPI')->setName('main');

// Authentication Routes
$app->post('/auth/signin', 'App\Controller\Auth:signin');
$app->post('/auth/signup', 'App\Controller\Auth:signup');

// Donasi Routes
$app->post('/donasi', 'App\Controller\Umum:donasi');

// Peminjam Routes
$app->post('/pinjaman/ajukan-pinjaman', 'App\Controller\Peminjam:ajukanPinjaman');

// User Routes
$app->get('/users/info', 'App\Controller\Users:info');