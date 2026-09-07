<?php
$dbPath = __DIR__ . '/database.sqlite';

if (!file_exists($dbPath)) {
    echo "Banco de dados não encontrado em $dbPath.\n";
    exit;
}

try {
    $db = new PDO("sqlite:" . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Add google_id column
    $db->exec("ALTER TABLE usuarios ADD COLUMN google_id VARCHAR(255) UNIQUE;");
    echo "Coluna google_id adicionada com sucesso!\n";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'duplicate column name') !== false) {
        echo "A coluna google_id já existe na tabela.\n";
    } else {
        echo "Erro ao alterar o banco de dados: " . $e->getMessage() . "\n";
    }
}
