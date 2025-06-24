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
        <option value="<?= $k ?>" <?= $lang_origin == $k ? ' selected' : '' ?>><?= $name ?></option>
      <?php endforeach;  ?>
    </select>

    <label>Idioma destino:</label>
    <select id="target_language">
      <?php foreach($languages as $k => $name): ?>
        <option value="<?= $k ?>" <?= $lang_target == $k ? ' selected' : '' ?>><?= $name ?></option>
      <?php endforeach; ?>
    </select>

    <button id="start_button">Iniciar Traducción</button>
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
    const ws_url = '<?= $_SERVER['WEBSOCKET_URL'] ?>'; // Ej: ws://localhost:3000
    const ws_translate_url = '<?= $_SERVER['WS_TRANSLATE_URL'] ?>'; // Ej: ws://localhost:8081


    let transcription_socket = null;
    let translation_socket = null;
    let isStarted = false;

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
          const { data } = JSON.parse(event.data);
          if (!data || !data.text) return;


          const { text, speaker_name, result_id } = data;
          const para_id = `result-${result_id}`;

          let para = document.getElementById(para_id);
          if(!para){
            para = document.createElement('p');
            para.id = para_id;
            transcriptionOutput.appendChild(para);
          }

          para.textContent = `[${speaker_name}] ${text}`;

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
          const { translated_text, result_id, speaker_name } = data;

          const trans_id = `trans-${result_id}`;
          let translated = document.getElementById(trans_id);
          if (!translated) {
            translated = document.createElement('p');
            translated.id = trans_id;
            translationOutput.appendChild(translated);
          }

          translated.textContent = `[${speaker_name}] ${translated_text}`;
        } catch (err) {
          console.error('Error al procesar traducción:', err);
        }
      });
      // Deshabilitar botón
      startButton.disabled = true;
      startButton.textContent = 'Conectado';
    };

    startButton.addEventListener('click', startTranslation);

    <?php if ($session_id && $lang_origin && $lang_target): ?>
      startButton.click();
    <?php endif; ?>
  </script>
</body>
</html>
