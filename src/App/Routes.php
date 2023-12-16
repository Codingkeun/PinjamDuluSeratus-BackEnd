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
$app->get('/pinjaman/dashboard/statistic', 'App\Controller\Peminjam:statisticDashboard'); 
$app->post('/pinjaman/ajukan-pinjaman', 'App\Controller\Peminjam:ajukanPinjaman');
$app->get('/pinjaman/pinjaman-aktif', 'App\Controller\Peminjam:listPinjamanaAktif'); 
$app->get('/pinjaman/detail/{id}', 'App\Controller\Peminjam:detail'); 
$app->get('/pinjaman/riwayat', 'App\Controller\Peminjam:riwayatPinjaman'); 
$app->post('/pinjaman/payment', 'App\Controller\Peminjam:payment'); 

// Inevestor Routes
$app->post('/invesment/approve-pinjaman', 'App\Controller\Investor:approvePinjaman');
$app->get('/invesment/pinjaman-aktif', 'App\Controller\Investor:listPengajuanAktif'); 
$app->get('/invesment/piutang-aktif', 'App\Controller\Investor:listPiutangAktif'); 
$app->get('/invesment/detail/{id}', 'App\Controller\Investor:detail'); 
$app->get('/invesment/riwayat', 'App\Controller\Investor:riwayatPinjaman'); 
$app->post('/invesment/topup', 'App\Controller\Investor:topup');
$app->get('/invesment/saldo', 'App\Controller\Investor:saldo');

// User Routes
$app->get('/users/info', 'App\Controller\Users:info');
$app->get('/users/profile', 'App\Controller\Users:profile');