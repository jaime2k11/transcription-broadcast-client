<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;

class TranslationApi extends ResourceController
{
    public function translate()
    {
        $body = $this->request->getJSON(true);
        $text = $body['text'] ?? '';
        $source = $body['source'] ?? 'es';
        $target = $body['target'] ?? 'en';

        // Amazon Translate logic using AWS SDK v3
        $translate = new \Aws\Translate\TranslateClient([
            'version' => 'latest',
            'region'  => 'us-east-1',
            'credentials' => [
                'key' => getenv('AWS_ACCESS_KEY_ID'),
                'secret' => getenv('AWS_SECRET_ACCESS_KEY'),
            ]
        ]);

        try {
            $result = $translate->translateText([
                'SourceLanguageCode' => $source,
                'TargetLanguageCode' => $target,
                'Text' => $text,
            ]);

            return $this->respond(['translated_text' => $result['TranslatedText']]);
        } catch (\Throwable $e) {
            return $this->failServerError($e->getMessage());
        }
    }
}
