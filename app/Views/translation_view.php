<!DOCTYPE html>
<html>
<head>
  <title>Transcripción con Traducción</title>
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
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
    let socket = null;
    let isStarted = false;

    const transcriptionOutput = document.getElementById('transcription_output');
    const translationOutput = document.getElementById('translation_output');
    const startButton = document.getElementById('start_button');

    startButton.addEventListener('click', () => {
      if (isStarted) return;
      isStarted = true;

      const source = document.getElementById('source_language').value;
      const target = document.getElementById('target_language').value;

      // Conexión WebSocket
      socket = io('<?=$_SERVER['WEBSOCKET_URL']?>', {
        query: { session_id },
        path: '/socket.io'
      });

      socket.on('connect', () => {
        console.log('Conectado al WebSocket');
      });

      socket.on('transcription', async (data) => {
        if (!data || !data.text) return;

        const { text, speaker_name } = data;

        const para = document.createElement('p');
        para.textContent = `[${speaker_name}] ${text}`;
        transcriptionOutput.appendChild(para);

        try {
          const res = await fetch('<?= base_url('/translate-api') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ text, source, target })
          });

          const json = await res.json();
          const translated = document.createElement('p');
          translated.textContent = `[${speaker_name}] ${json.translated_text}`;
          translationOutput.appendChild(translated);
        } catch (e) {
          console.error('Error al traducir:', e);
        }
      });

      socket.on('disconnect', () => {
        console.log('WebSocket desconectado');
      });

      // Deshabilitar el botón luego de iniciar
      startButton.disabled = true;
      startButton.textContent = 'Conectado';
    });
    <?php if($session_id && $lang_origin && $lang_target): ?>
      startButton.click();
    <?php endif; ?>
  </script>
</body>
</html>
