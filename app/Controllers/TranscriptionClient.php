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
        $languages = $this->getAvailableLanguages();
        $session_id = $this->request->getGet('session_id');
        $lang_origin = $this->request->getGet('ori');
        $lang_target = $this->request->getGet('dest');
        $auto_start = $lang_origin && $lang_target;
        if(!$lang_origin) $lang_origin = 'es';
        if(!$lang_target) $lang_target = 'en';
        if(!in_array($lang_origin, array_keys($languages))) $lang_origin = null;
        if(!in_array($lang_target, array_keys($languages))) $lang_target = null;
        return view('translation_view', ['languages' => $languages, 'session_id' => $session_id, 'lang_origin' => $lang_origin, 'lang_target' => $lang_target, 'auto_start' => $auto_start]);
    }

    protected function getAvailableLanguages(){
        return [
          "af" => "Afrikaans",
          "sq" => "Albanian",
          "am" => "Amharic",
          "ar" => "Arabic",
          "hy" => "Armenian",
          "az" => "Azerbaijani",
          "bn" => "Bengali",
          "bs" => "Bosnian",
          "bg" => "Bulgarian",
          "ca" => "Catalan",
          "zh" => "Chinese (Simplified)",
          "zh-TW" => "Chinese (Traditional)",
          "hr" => "Croatian",
          "cs" => "Czech",
          "da" => "Danish",
          "fa-AF" => "Dari",
          "nl" => "Dutch",
          "en" => "English",
          "et" => "Estonian",
          "fa" => "Farsi (Persian)",
          "tl" => "Filipino (Tagalog)",
          "fi" => "Finnish",
          "fr" => "French",
          "fr-CA" => "French (Canada)",
          "ka" => "Georgian",
          "de" => "German",
          "el" => "Greek",
          "gu" => "Gujarati",
          "ht" => "Haitian Creole",
          "ha" => "Hausa",
          "he" => "Hebrew",
          "hi" => "Hindi",
          "hu" => "Hungarian",
          "is" => "Icelandic",
          "id" => "Indonesian",
          "ga" => "Irish",
          "it" => "Italian",
          "ja" => "Japanese",
          "kn" => "Kannada",
          "kk" => "Kazakh",
          "ko" => "Korean",
          "lv" => "Latvian",
          "lt" => "Lithuanian",
          "mk" => "Macedonian",
          "ms" => "Malay",
          "ml" => "Malayalam",
          "mt" => "Maltese",
          "mr" => "Marathi",
          "mn" => "Mongolian",
          "no" => "Norwegian",
          "ps" => "Pashto",
          "pl" => "Polish",
          "pt" => "Portuguese",
          "pt-PT" => "Portuguese (Portugal)",
          "pa" => "Punjabi",
          "ro" => "Romanian",
          "ru" => "Russian",
          "sr" => "Serbian",
          "si" => "Sinhala",
          "sk" => "Slovak",
          "sl" => "Slovenian",
          "so" => "Somali",
          "es" => "Spanish",
          "sw" => "Swahili",
          "sv" => "Swedish",
          "ta" => "Tamil",
          "te" => "Telugu",
          "th" => "Thai",
          "tr" => "Turkish",
          "uk" => "Ukrainian",
          "ur" => "Urdu",
          "uz" => "Uzbek",
          "vi" => "Vietnamese",
          "cy" => "Welsh"
        ];
    }
}
