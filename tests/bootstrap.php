<?php

require __DIR__ . '/../vendor/autoload.php';

// Charge le .env normal (clés Stripe test-mode, mailer...) puis force la
// base de données de test — Database::getInstance() est un singleton lu
// paresseusement, donc écraser $_ENV['DB_NAME'] ici, avant toute requête,
// suffit à aiguiller TOUTE la suite vers toile_test sans jamais toucher
// la base de développement.
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$_ENV['DB_NAME'] = 'toile_test';
putenv('DB_NAME=toile_test');
