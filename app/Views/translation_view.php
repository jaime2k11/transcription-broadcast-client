<!DOCTYPE html>
<html>
<head>
  <title>Transcripción con Traducción</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    .columns { display: flex; gap: 40px; }
    .column { flex: 1; }
    .translation-settings { margin-bottom: 20px; }
    #start_button { margin-top: 10px; }
  </style>
</head>
<body>
  <h2>Recepción y Traducción de Transcripción:</h2>

  <div class="translation-settings">
    <label>Idioma origen:</label>
    <select id="source_language">
      <?php foreach($languages as $k => $name): ?>
        <option value="<?= $k ?>" <?= $lang_origin === $k ? ' selected' : '' ?>><?= $name ?> (<?= $k ?>)</option>
      <?php endforeach;  ?>
    </select>

    <label>Idioma destino:</label>
    <select id="target_language">
      <?php foreach($languages as $k => $name): ?>
        <option value="<?= $k ?>" <?= $lang_target === $k ? ' selected' : '' ?>><?= $name ?> (<?= $k ?>)</option>
      <?php endforeach; ?>
    </select>

    <button id="start_button">Iniciar Traducción</button> <a id="download_srt" style="display: none;">Descargar SRT</a>
  </div>

  <p><strong>Session ID:</strong> <span id="session_id_display"><?= esc($session_id) ?></span></p>

  <div class="columns">
    <div class="column" style="display: none;">
      <h3>Transcripción Original</h3>
      <div id="transcription_output"></div>
    </div>
    <div class="column">
      <h3>Traducción</h3>
      <div id="translation_output"></div>
    </div>
  </div>

  <script>
    const session_id = '<?= esc($session_id) ?>';
    const ws_url = '<?= $_SERVER['WEBSOCKET_URL'] ?>';
    const ws_translate_url = '<?= $_SERVER['WS_TRANSLATE_URL'] ?>';
    let translationState = 'idle'; // 'idle' | 'running' | 'finished'


    let transcription_socket = null;
    let translation_socket = null;
    let isStarted = false;
    let speaker_labels;

    const transcriptionOutput = document.getElementById('transcription_output');
    const translationOutput = document.getElementById('translation_output');
    const startButton = document.getElementById('start_button');

    const startTranslation = () => {
      if (isStarted) return;
      isStarted = true;

      const source = document.getElementById('source_language').value;
      const target = document.getElementById('target_language').value;

      // Crear conexión WebSocket nativa
      transcription_socket = new WebSocket(`${ws_url}?session_id=${encodeURIComponent(session_id)}`);

      transcription_socket.addEventListener('open', () => {
        console.log('WebSocket conectado');
      });

      transcription_socket.addEventListener('message', async (event) => {
        try {
          const msg = JSON.parse(event.data);
          if (msg?.event === 'stop') {
            console.log("Transcripción terminada por origen.");
            translationState = 'finished';
            updateButtonState();
            return;
          }
          const { data } = msg;
          if (!data || !data.text) return;
          speaker_labels = data.speaker_labels;

          const { text, speaker_name, start_time, end_time, result_id } = data;
          const para_id = `result-${result_id}`;

          let para = document.getElementById(para_id);
          if(!para){
            para = document.createElement('p');
            para.id = para_id;
            transcriptionOutput.appendChild(para);
          }
          para.setAttribute('start_time', start_time);
          para.setAttribute('end_time', end_time);
          para.textContent = speaker_labels ? `[${speaker_name}] ${text}` : text;

          // Reenviar al socket de traducción
          if (translation_socket.readyState === WebSocket.OPEN) {
            translation_socket.send(JSON.stringify({
              text,
              source,
              target,
              result_id,
              speaker_name
            }));
          }
        } catch (e) {
          console.error('Error procesando mensaje o traduciendo:', e);
        }
      });

      transcription_socket.addEventListener('close', () => {
        console.log('WebSocket cerrado');
      });

      transcription_socket.addEventListener('error', (err) => {
        console.error('Error en WebSocket:', err);
      });

      // WebSocket 2: para enviar textos a traducir
      translation_socket = new WebSocket(ws_translate_url);

      translation_socket.addEventListener('open', () => {
        console.log('WebSocket de traducción conectado');
      });

      translation_socket.addEventListener('message', (event) => {
        try {
          const data = JSON.parse(event.data);
          //console.log("translation", data);
          const { translated_text, result_id, speaker_name } = data;

          const trans_id = `trans-${result_id}`;
          let translated = document.getElementById(trans_id);
          if (!translated) {
            translated = document.createElement('p');
            translated.id = trans_id;
            translated.classList.add('translation-item');
            translationOutput.appendChild(translated);
          }
          translated.textContent = speaker_labels ? `[${speaker_name}] ${translated_text}` : translated_text;
        } catch (err) {
          console.error('Error al procesar traducción:', err);
        }
      });
      // Deshabilitar botón
      startButton.disabled = true;
      startButton.textContent = 'Conectado';
    };

    startButton.addEventListener('click', () => {
      if (translationState === 'idle') {
        translationState = 'running';
        startTranslation();
        updateButtonState();
      } else if (translationState === 'running') {
        stopTranslation();
        translationState = 'finished';
        updateButtonState();
      }
    });

    const updateButtonState = () => {
      if (translationState === 'idle') {
        startButton.textContent = 'Iniciar Traducción';
        startButton.disabled = false;
        document.getElementById('download_srt').style.display = 'none';
      } else if (translationState === 'running') {
        startButton.textContent = 'Detener Traducción';
        startButton.disabled = false;
        document.getElementById('download_srt').style.display = 'none';
      } else if (translationState === 'finished') {
        startButton.textContent = 'Traducción finalizada';
        startButton.disabled = true;
        setupSRTLink();
      }
    };

    const stopTranslation = () => {
      if (transcription_socket) transcription_socket.close();
      if (translation_socket) translation_socket.close();
    };

    const formatTimestamp = (time_in_seconds) => {
      const date = new Date(0);
      date.setSeconds(time_in_seconds);
      return date.toISOString().substr(11, 8) + `,${Math.floor(time_in_seconds % 1 * 1000).toString().padStart(3, '0')}`;
    };

    const getSRTFile = () => {
      const translations = Array.from(document.querySelectorAll('.translation-item'));
      if(translations.length == 0) return null;
      return translations.map((t, i) => {
        const content = t.innerHTML.trim();
        const speaker = content.startsWith('[') ?  content.split(']')[0].replace('[', '') : null;
        const transcript = content.replace(`[${speaker}]`, '').trim();
        const result_id = t.id.replace('trans-', '');
        const original = document.getElementById(`result-${result_id}`);
        const start = formatTimestamp(original.getAttribute('start_time'));
        const end = formatTimestamp(original.getAttribute('end_time'));
        const text = speaker ? `${speaker}: ${transcript}` : transcript;
        return `${i + 1}\n${start} --> ${end}\n${text}\n\n`;
      }).join('');
    };

    const setupSRTLink = () => {
      const srt_content = getSRTFile();
      if(!srt_content) return;
      const srt_blob = new Blob([srt_content], { type: 'text/plain' });
      const link = document.getElementById('download_srt');
      link.href = URL.createObjectURL(srt_blob);
      link.download = `translation_${Date.now()}.srt`;
      link.style.display = 'inline';
    }


    <?php if ($auto_start): ?>
      startButton.click();
    <?php endif; ?>
  </script>
</body>
</html>
