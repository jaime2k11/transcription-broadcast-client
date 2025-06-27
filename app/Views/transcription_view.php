<!DOCTYPE html>
<html>
<head>
  <title>Recepción de Transcripción</title>
  <style>
    body { font-family: sans-serif; padding: 20px; }
    #transcription_output p { margin: 5px 0; }
  </style>
</head>
<body>
  <h2>Recepción de Transcripción</h2>
  <p><strong>Session ID:</strong> <span id="session_id_display">Cargando...</span></p>
  <div id="transcription_output"></div>

  <script>
    const session_id = '<?= esc($session_id) ?>';
    console.log('Session ID cargado desde PHP:', session_id);

    document.getElementById('session_id_display').textContent = session_id;

    const ws_url = '<?= $_SERVER['WEBSOCKET_URL'] ?>'; // Ej: ws://localhost:3000

    let transcription_socket = null;
    const transcriptionOutput = document.getElementById('transcription_output');

    transcription_socket = new WebSocket(`${ws_url}?session_id=${encodeURIComponent(session_id)}`);

    transcription_socket.addEventListener('open', () => {
      console.log('WebSocket conectado');
    });

    transcription_socket.addEventListener('message', async (event) => {
      try {
        const { data } = JSON.parse(event.data);
        if (!data || !data.text) return;

        const { text, speaker_labels, speaker_name, result_id } = data;
        const para_id = `result-${result_id}`;

        let para = document.getElementById(para_id);
        if(!para){
          para = document.createElement('p');
          para.id = para_id;
          transcriptionOutput.appendChild(para);
        }

        para.textContent = speaker_labels ? `[${speaker_name}] ${text}` : text;

        } catch (e) {
          console.error('Error procesando mensaje:', e);
        }
      });

      transcription_socket.addEventListener('close', () => {
        console.log('WebSocket cerrado');
      });

      transcription_socket.addEventListener('error', (err) => {
        console.error('Error en WebSocket:', err);
      });
  </script>
</body>
</html>
