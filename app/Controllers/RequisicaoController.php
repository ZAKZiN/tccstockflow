<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Requisicao;
use App\Models\Notificacao;

class RequisicaoController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $nivel = $_SESSION['usuario_nivel'];
        
        // Regra de Negócio: Solicitante e Coordenador veem apenas do seu setor
        // Almoxarife e Admin veem de todos
        if (in_array($nivel, ['Solicitante', 'Coordenador'])) {
            $requisicoes = Requisicao::getBySetor($_SESSION['usuario_id_setor']);
        } else {
            $requisicoes = Requisicao::getAll();
        }
        
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=requisicoes_export.csv');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Material', 'Quantidade', 'Solicitante', 'Setor', 'Status', 'Data'], ';');
            foreach ($requisicoes as $r) {
                fputcsv($output, [$r['id_requisicao'], $r['material'], $r['quantidade'], $r['solicitante'], $r['nome_setor'], $r['status'], date('d/m/Y H:i', strtotime($r['data_solicitacao']))], ';');
            }
            fclose($output);
            exit;
        }
        
        $this->view('requisicoes/index', ['requisicoes' => $requisicoes]);
    }
    
    public function create() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $dados = [
                'solicitante' => $_SESSION['usuario_nome'],
                'id_setor' => $_SESSION['usuario_id_setor'],
                'material' => $_POST['material'] ?? '',
                'quantidade' => $_POST['quantidade'] ?? 1,
                'prioridade' => $_POST['prioridade'] ?? 'Média',
                'justificativa' => $_POST['justificativa'] ?? ''
            ];
            
            Requisicao::create($dados);
            
            // Notificar Coordenador
            Notificacao::create(
                "Nova Requisição: {$_POST['material']}", 
                "{$_SESSION['usuario_nome']} solicitou {$_POST['quantidade']} unidade(s).",
                null, 
                'Coordenador'
            );
            
            $this->redirect('/requisicoes');
        }
        
        $this->view('requisicoes/create');
    }
    
    public function approve($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        try {
            Requisicao::updateStatus($id, 'Pendente Almoxarifado');
            
            Notificacao::create(
                "Requisição Aprovada", 
                "A requisição #{$id} foi aprovada e enviada ao Almoxarifado.",
                null, 
                'Almoxarife'
            );
            $this->redirect('/requisicoes?success=' . urlencode('Requisição aprovada.'));
        } catch (\Exception $e) {
            $this->redirect('/requisicoes?error=' . urlencode('Erro ao aprovar requisição.'));
        }
    }

    public function reject($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        try {
            Requisicao::updateStatus($id, 'Recusado');
            Notificacao::create(
                "Requisição Recusada", 
                "A requisição #{$id} foi recusada pelo Coordenador.",
                null, 
                'Solicitante'
            );
            $this->redirect('/requisicoes?success=' . urlencode('Requisição recusada.'));
        } catch (\Exception $e) {
            $this->redirect('/requisicoes?error=' . urlencode('Erro ao recusar requisição.'));
        }
    }

    public function despachar($id) {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/dashboard');
        }
        
        $db = \App\Core\Database::getConnection();
        
        // Buscar a requisição
        $stmt = $db->prepare("SELECT * FROM requisicoes WHERE id_requisicao = ?");
        $stmt->execute([$id]);
        $req = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($req) {
            try {
                $db->beginTransaction();
                
                // Mudar status para Despachado
                $stmtUpdate = $db->prepare("UPDATE requisicoes SET status = 'Despachado' WHERE id_requisicao = ?");
                $stmtUpdate->execute([$id]);
                
                // Tentar encontrar o produto no estoque com o mesmo nome exato
                $stmtProd = $db->prepare("SELECT id_produto, quantidade_estoque FROM produtos WHERE nome_produto = ? COLLATE NOCASE");
                $stmtProd->execute([$req['material']]);
                $produto = $stmtProd->fetch(\PDO::FETCH_ASSOC);
                
                if ($produto) {
                    if ($produto['quantidade_estoque'] < $req['quantidade']) {
                        throw new \Exception("Estoque insuficiente para despachar. (Atual: {$produto['quantidade_estoque']})");
                    }
                    
                    // Dar baixa no estoque
                    $novaQtd = $produto['quantidade_estoque'] - $req['quantidade'];
                    $stmtEstoque = $db->prepare("UPDATE produtos SET quantidade_estoque = ? WHERE id_produto = ?");
                    $stmtEstoque->execute([$novaQtd, $produto['id_produto']]);
                    
                    // Registrar no Kardex
                    $sqlMov = "INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao) VALUES (?, ?, 'Saída', ?, ?)";
                    $stmtMov = $db->prepare($sqlMov);
                    $stmtMov->execute([
                        $produto['id_produto'], 
                        $_SESSION['usuario_id'], 
                        $req['quantidade'], 
                        "Requisição #{$id} despachada ao setor"
                    ]);
                }
                
                $db->commit();
                
                Notificacao::create(
                    "Requisição Despachada", 
                    "Sua requisição #{$id} ({$req['material']}) foi despachada.",
                    null, 
                    'Solicitante'
                );
                
                $this->redirect('/requisicoes?success=' . urlencode('Requisição despachada com sucesso!'));
            } catch (\Exception $e) {
                $db->rollBack();
                $this->redirect('/requisicoes?error=' . urlencode('Erro ao despachar: ' . $e->getMessage()));
            }
        } else {
            $this->redirect('/requisicoes');
        }
    }
}
