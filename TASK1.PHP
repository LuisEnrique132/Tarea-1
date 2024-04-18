
<?php
// Cargar el contenido del archivo de registro 
$log_file = 'gstGCJv4.log';
$log_content = file_get_contents($log_file);
$log_entries = explode("\n", $log_content);

// Inicializar contadores
$ssh_config_requests = 0;
$non_200_responses = 0;
$total_requests = 0;
$put_requests = 0;
$ip_requests = array();

// Analizar cada entrada del archivo de registro
foreach ($log_entries as $entry) {
    // Extraer la URL y el código de respuesta de cada entrada
    if (preg_match('/\"(GET|PUT) ([^\s]+) HTTP\/1\.\d\" (\d+)/', $entry, $matches)) {
        $method = $matches[1];
        $url = $matches[2];
        $status_code = intval($matches[3]);

        // Contar las solicitudes de sshd_config
        if ($url === '/production/file_metadata/modules/ssh/sshd_config?') {
            $ssh_config_requests++;
        }

        // Contar las solicitudes con códigos de respuesta distintos de 200
        if ($status_code !== 200) {
            $non_200_responses++;
        }

        // Contar todas las solicitudes
        $total_requests++;

        // Contar las solicitudes PUT en /dev/informe/
        if ($method === 'PUT' && strpos($url, '/dev/informe/') === 0) {
            $put_requests++;
        }

        // Contar las solicitudes por dirección IP
        if (preg_match('/^(\d+\.\d+\.\d+\.\d+) /', $entry, $ip_matches)) {
            $ip = $ip_matches[1];
            if (!isset($ip_requests[$ip])) {
                $ip_requests[$ip] = 1;
            } else {
                $ip_requests[$ip]++;
            }
        }
    }
}

// Imprimir los resultados
echo "<table border='1'>";
echo "<tr><th>Métrica</th><th>Valor</th></tr>";
echo "<tr><td>Solicitudes de /production/file_metadata/modules/ssh/sshd_config</td><td>$ssh_config_requests</td></tr>";
echo "<tr><td>Solicitudes no 200</td><td>$non_200_responses</td></tr>";
echo "<tr><td>Total de solicitudes no 200 o distintas de 200</td><td>" . ($total_requests - $ssh_config_requests) . "</td></tr>";
echo "<tr><td>Solicitudes PUT en /dev/informe/</td><td>$put_requests</td></tr>";
echo "<tr><td colspan='2'><strong>Desglose de solicitudes PUT por dirección IP:</strong></td></tr>";
foreach ($ip_requests as $ip => $count) {
    echo "<tr><td>$ip</td><td>$count</td></tr>";
}
echo "</table>";
?>
