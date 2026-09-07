<?php
require_once __DIR__ . '/env.php';
return [
 'host' => eva_env('SMTP_HOST','smtp.hostinger.com'),
 'port' => (int)eva_env('SMTP_PORT',465),
 'username' => eva_env('SMTP_USER','no-reply@dashboard.elvigilantedeagua.com'),
 'password' => eva_env('SMTP_PASS','#VALzona122233'),
 'encryption' => eva_env('SMTP_ENCRYPTION','ssl'),
 'from_email' => eva_env('SMTP_FROM_EMAIL','no-reply@dashboard.elvigilantedeagua.com'),
 'from_name' => eva_env('SMTP_FROM_NAME','EVA - El Vigilante de Agua'),
 'base_url' => eva_env('BASE_URL','https://dashboard.elvigilantedeagua.com'),
];
