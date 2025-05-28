<!DOCTYPE html>
<html>
<head>
  <title>Recepción de Transcripción</title>
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
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

    const socket = io('<?=$_SERVER['WEBSOCKET_URL']?>', {
      query: { session_id },
      path: '/socket.io'
    });

    socket.on('connect', () => {
      console.log('Conectado al WebSocket desde CodeIgniter');
    });

    socket.on('transcription', (data) => {
      console.log('Transcripción recibida:', data);
      const para = document.createElement('p');
      para.textContent = `[${data.speaker_name}] ${data.text}`;
      document.getElementById('transcription_output').appendChild(para);
    });

    socket.on('disconnect', () => {
      console.log('WebSocket desconectado');
    });
  </script>
</body>
</html>
