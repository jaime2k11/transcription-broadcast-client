<?php

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use App\WebSockets\TranslationWsServer;

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__); // o el path donde esté tu .env
$dotenv->load();
$ws_url = $_ENV['WS_TRANSLATE_URL'];
$parsed = parse_url($ws_url);
$port = $parsed['port'] ?? 80; // default a 80 si no hay puerto explícito

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TranslationWsServer()
        )
    ),
    $port
);

echo "Servidor WebSocket de traducción iniciado en puerto $port...\n";
$server->run();
