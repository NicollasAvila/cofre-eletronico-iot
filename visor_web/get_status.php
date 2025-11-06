<?php
clearstatcache();
// Define o tipo de conteúdo como texto plano
header('Content-Type: text/plain; charset=utf-8');
// Força o navegador a não usar cache
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Data no passado

$file = 'status.txt';

if (file_exists($file)) {
    // Lê o conteúdo do arquivo e envia para o navegador
    echo htmlspecialchars(file_get_contents($file));
} else {
    // Mensagem padrão se o arquivo ainda não foi criado
    echo 'Aguardando...';
}
?>