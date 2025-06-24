<?php

namespace App\WebSockets;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Aws\Translate\TranslateClient;

class TranslationWsServer implements MessageComponentInterface
{
    protected $clients;
    protected $translator;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();

        $this->translator = new TranslateClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key' => $_ENV['AWS_ACCESS_KEY_ID'],
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'],
            ]
        ]);
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        echo "Nueva conexión: ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo "Mensaje recibido: {$msg}\n";

        try {
            $data = json_decode($msg, true);
            if (!$data || !isset($data['text'], $data['source'], $data['target'])) {
                $from->send(json_encode(['error' => 'Datos inválidos']));
                return;
            }

            $result = $this->translator->translateText([
                'SourceLanguageCode' => $data['source'],
                'TargetLanguageCode' => $data['target'],
                'Text' => $data['text'],
            ]);

            $response = [
                'translated_text' => $result['TranslatedText'],
                'result_id' => $data['result_id'] ?? null,
                'speaker_name' => $data['speaker_name'] ?? null,
                'text_original' => $data['text']
            ];

            $from->send(json_encode($response));
        } catch (\Throwable $e) {
            $from->send(json_encode(['error' => $e->getMessage()]));
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        echo "Conexión cerrada ({$conn->resourceId})\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "Error: {$e->getMessage()}\n";
        $conn->close();
    }
}
