<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Helpers\AuditHelper;
use App\Models\Requisicao;
use App\Models\Notificacao;
use PDO;

class RequisicaoController extends Controller {
    
    public function index() {
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        
        if (in_array($nivel, ['Solicitante', 'Coordenador'], true)) {
            $requisicoes = Requisicao::getBySetor($_SESSION['usuario_id_setor'] ?? null, $_SESSION['usuario_nome'] ?? '');
        } else {
            $requisicoes = Requisicao::getAll();
        }
        
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=requisicoes_export.csv');
            $output = fopen('php://output', 'w');
            fputs($output, "\xEF\xBB\xBF");
            fputcsv($output, ['ID', 'Material', 'Quantidade', 'Prioridade', 'Solicitante', 'Setor', 'Status', 'Data'], ';', '"', '');
            foreach ($requisicoes as $r) {
                fputcsv($output, $this->sanitizeCsvRow([
                    $r['id_requisicao'],
                    $r['material'],
                    $r['quantidade'],
                    $r['prioridade'] ?? 'Média',
                    $r['solicitante'],
                    $r['nome_setor'] ?? 'Geral',
                    $r['status'],
                    date('d/m/Y H:i', strtotime($r['data_solicitacao']))
                ]), ';', '"', '');
            }
            fclose($output);
            exit;
        }
        
        $this->view('requisicoes/index', ['requisicoes' => $requisicoes]);
    }
    
    public function create() {
        $db = Database::getConnection();
        $produtosCatalogo = $db->query("SELECT nome_produto FROM produtos ORDER BY nome_produto ASC")->fetchAll(PDO::FETCH_COLUMN);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $material = trim($_POST['material'] ?? '');
            $quantidade = round(floatval(str_replace(',', '.', $_POST['quantidade'] ?? '1')), 3);
            $prioridade = $_POST['prioridade'] ?? 'Média';
            if (!in_array($prioridade, ['Baixa', 'Média', 'Alta', 'Urgente'], true)) {
                $prioridade = 'Média';
            }

            if ($material === '' || $quantidade <= 0) {
                $this->redirect('/requisicoes/nova?error=' . urlencode('Informe o material e uma quantidade válida.'));
            }

            $dados = [
                'solicitante' => $_SESSION['usuario_nome'],
                'id_setor' => $_SESSION['usuario_id_setor'] ?? null,
                'material' => $material,
                'quantidade' => $quantidade,
                'prioridade' => $prioridade,
                'justificativa' => trim($_POST['justificativa'] ?? '')
            ];
            
            $idReq = Requisicao::create($dados);
            AuditHelper::log('requisicoes', (int)$idReq, 'CRIAR_REQUISICAO', "Solicitação de {$quantidade}x '{$material}' (Prioridade: {$prioridade})");
            
            Notificacao::create(
                "Nova Requisição #{$idReq}: {$material}", 
                "{$_SESSION['usuario_nome']} solicitou {$quantidade} unidade(s).",
                null, 
                'Coordenador'
            );
            
            $this->redirect('/requisicoes?success=' . urlencode('Requisição enviada para aprovação!'));
        }
        
        $this->view('requisicoes/create', ['produtosCatalogo' => $produtosCatalogo]);
    }
    
    public function approve($id) {
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        if (!in_array($nivel, ['Coordenador', 'Gerente', 'Administrador'], true)) {
            $this->redirect('/requisicoes?error=' . urlencode('Você não tem permissão para aprovar requisições.'));
        }

        $idReq = (int)$id;
        $req = Requisicao::find($idReq);
        if (!$req || $req['status'] !== 'Pendente Coordenador') {
            $this->redirect('/requisicoes?error=' . urlencode('Esta requisição não está pendente de aprovação.'));
        }

        if ($nivel === 'Coordenador' && !empty($_SESSION['usuario_id_setor']) && (int)$req['id_setor'] !== (int)$_SESSION['usuario_id_setor']) {
            $this->redirect('/requisicoes?error=' . urlencode('Coordenadores só podem aprovar requisições do próprio setor.'));
        }
        
        try {
            Requisicao::updateStatus($idReq, 'Pendente Almoxarifado', $_SESSION['usuario_nome']);
            AuditHelper::log('requisicoes', $idReq, 'APROVAR_REQUISICAO', "Requisição #{$idReq} ({$req['material']}) aprovada");
            
            // Corrigido: o cargo real do sistema é 'Estoquista' (anteriormente enviava para 'Almoxarife')
            Notificacao::create(
                "Requisição #{$idReq} Aprovada", 
                "A requisição de {$req['quantidade']}x {$req['material']} aguarda atendimento no Estoque.",
                null, 
                'Estoquista'
            );
            $this->redirect('/requisicoes?success=' . urlencode('Requisição aprovada e encaminhada ao Estoque!'));
        } catch (\Exception $e) {
            error_log('Erro ao aprovar requisição: ' . $e->getMessage());
            $this->redirect('/requisicoes?error=' . urlencode('Erro ao aprovar requisição.'));
        }
    }

    public function reject($id) {
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        if (!in_array($nivel, ['Coordenador', 'Gerente', 'Administrador'], true)) {
            $this->redirect('/requisicoes?error=' . urlencode('Você não tem permissão para recusar requisições.'));
        }

        $idReq = (int)$id;
        $req = Requisicao::find($idReq);
        if (!$req || $req['status'] !== 'Pendente Coordenador') {
            $this->redirect('/requisicoes?error=' . urlencode('Esta requisição não pode mais ser recusada.'));
        }

        if ($nivel === 'Coordenador' && !empty($_SESSION['usuario_id_setor']) && (int)$req['id_setor'] !== (int)$_SESSION['usuario_id_setor']) {
            $this->redirect('/requisicoes?error=' . urlencode('Coordenadores só podem avaliar requisições do próprio setor.'));
        }
        
        try {
            Requisicao::updateStatus($idReq, 'Recusado', $_SESSION['usuario_nome']);
            AuditHelper::log('requisicoes', $idReq, 'RECUSAR_REQUISICAO', "Requisição #{$idReq} ({$req['material']}) recusada");

            Notificacao::create(
                "Requisição #{$idReq} Recusada", 
                "A requisição de {$req['material']} foi recusada pela coordenação.",
                null, 
                'Solicitante'
            );
            $this->redirect('/requisicoes?success=' . urlencode('Requisição recusada.'));
        } catch (\Exception $e) {
            error_log('Erro ao recusar requisição: ' . $e->getMessage());
            $this->redirect('/requisicoes?error=' . urlencode('Erro ao recusar requisição.'));
        }
    }

    public function despachar($id) {
        $nivel = $_SESSION['usuario_nivel'] ?? '';
        if (!in_array($nivel, ['Estoquista', 'Gerente', 'Administrador'], true)) {
            $this->redirect('/requisicoes?error=' . urlencode('Apenas o Estoque ou Administração pode despachar materiais.'));
        }

        $idReq = (int)$id;
        $db = Database::getConnection();
        
        $req = Requisicao::find($idReq);
        if (!$req || !in_array($req['status'], ['Pendente Almoxarifado', 'Compra Efetuada'], true)) {
            $this->redirect('/requisicoes?error=' . urlencode('Esta requisição não está apta para despacho ou já foi despachada.'));
        }
        
        try {
            $agora = date('Y-m-d H:i:s');
            $db->beginTransaction();
            
            // Encontrar o produto no estoque pelo nome (case-insensitive)
            $stmtProd = $db->prepare("SELECT id_produto, nome_produto, quantidade_estoque FROM produtos WHERE nome_produto = ? COLLATE NOCASE");
            $stmtProd->execute([trim($req['material'])]);
            $produto = $stmtProd->fetch(PDO::FETCH_ASSOC);
            
            if ($produto) {
                if ((float)$produto['quantidade_estoque'] < (float)$req['quantidade']) {
                    throw new \Exception("Estoque insuficiente de '{$produto['nome_produto']}' para despachar (Saldo atual: {$produto['quantidade_estoque']}). Registre uma Ordem de Compra primeiro.");
                }
                
                $novaQtd = round((float)$produto['quantidade_estoque'] - (float)$req['quantidade'], 3);
                $stmtEstoque = $db->prepare("UPDATE produtos SET quantidade_estoque = ?, atualizado_em = ? WHERE id_produto = ?");
                $stmtEstoque->execute([$novaQtd, $agora, $produto['id_produto']]);
                
                $sqlMov = "INSERT INTO movimentacoes_estoque (id_produto, id_usuario, tipo, quantidade, observacao, data_movimentacao) VALUES (?, ?, 'Saída', ?, ?, ?)";
                $stmtMov = $db->prepare($sqlMov);
                $stmtMov->execute([
                    $produto['id_produto'], 
                    $_SESSION['usuario_id'], 
                    $req['quantidade'], 
                    "Requisição #{$idReq} despachada ao setor solicitante",
                    $agora
                ]);
            }

            $stmtUpdate = $db->prepare("UPDATE requisicoes SET status = 'Despachado', atualizado_em = ? WHERE id_requisicao = ?");
            $stmtUpdate->execute([$agora, $idReq]);
            Requisicao::registrarHistorico($idReq, 'Material despachado ao setor solicitante', $_SESSION['usuario_nome']);
            
            $db->commit();
            AuditHelper::log('requisicoes', $idReq, 'DESPACHAR_REQUISICAO', "Requisição #{$idReq} ({$req['material']}) despachada");
            
            Notificacao::create(
                "Requisição #{$idReq} Despachada", 
                "Sua requisição ({$req['material']}) foi atendida e despachada pelo Estoque.",
                null, 
                'Solicitante'
            );
            
            $this->redirect('/requisicoes?success=' . urlencode('Requisição despachada com sucesso!'));
        } catch (\Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            $this->redirect('/requisicoes?error=' . urlencode($e->getMessage()));
        }
    }
}
