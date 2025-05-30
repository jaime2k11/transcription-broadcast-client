<?php

namespace App\Controllers;

class TranscriptionClient extends BaseController
{
    public function index(){
        $session_id = $this->request->getGet('session_id');
        return view('transcription_view', ['session_id' => $session_id]);
    }

    public function translate(){
        //TODO: Moverlo a algun lado de config
        $languages = ['es' => 'Español', 'en' => 'Inglés'];
        $session_id = $this->request->getGet('session_id');
        $lang_origin = $this->request->getGet('ori');
        $lang_target = $this->request->getGet('dest');
        if(!in_array($lang_origin, array_keys($languages))) $lang_origin = null;
        if(!in_array($lang_target, array_keys($languages))) $lang_target = null;
        return view('translation_view', ['languages' => $languages, 'session_id' => $session_id, 'lang_origin' => $lang_origin, 'lang_target' => $lang_target]);
    }

}
