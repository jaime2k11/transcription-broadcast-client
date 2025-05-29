<?php

namespace App\Controllers;

class TranscriptionClient extends BaseController
{
    public function index(){
        $session_id = $this->request->getGet('session_id');
        return view('transcription_view', ['session_id' => $session_id]);
    }

    public function translate(){
        $session_id = $this->request->getGet('session_id');
        return view('translation_view', ['session_id' => $session_id]);
    }

}
