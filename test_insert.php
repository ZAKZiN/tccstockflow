<?php
require __DIR__ . '/vendor/autoload.php';
$_SESSION = ['usuario_id' => 1];
$db = \App\Core\Database::getConnection();
try {
    \App\Models\Produto::create([
        'nome_produto' => 'Test',
        'codigo_barras' => '123',
        'sku' => null,
        'id_categoria' => null,
        'preco_custo' => 10,
        'preco_venda' => 20,
        'quantidade_estoque' => 0,
        'estoque_minimo' => 5,
        'lote' => null,
        'data_validade' => '2024-10-10'
    ]);
    echo "Success";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
