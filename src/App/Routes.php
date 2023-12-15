<?php

declare(strict_types=1);

// General Routes
$app->get('/', 'App\Controller\Hello:getStatusAPI')->setName('main');
$app->get('/payment-method', 'App\Controller\Umum:listBank');

// Authentication Routes
$app->post('/auth/signin', 'App\Controller\Auth:signin');
$app->post('/auth/signup', 'App\Controller\Auth:signup');

// Donasi Routes
$app->post('/donasi', 'App\Controller\Umum:donasi');

// Peminjam Routes
$app->post('/pinjaman/ajukan-pinjaman', 'App\Controller\Peminjam:ajukanPinjaman');
$app->get('/pinjaman/pinjaman-aktif', 'App\Controller\Peminjam:listPinjamanaAktif'); 
$app->get('/pinjaman/detail/{id}', 'App\Controller\Peminjam:detail'); 
$app->get('/pinjaman/riwayat', 'App\Controller\Peminjam:riwayatPinjaman'); 
$app->post('/pinjaman/payment', 'App\Controller\Peminjam:payment'); 

// User Routes
$app->get('/users/info', 'App\Controller\Users:info');