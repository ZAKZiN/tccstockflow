<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Requisicao;
use App\Models\Notificacao;

class RequisicaoController extends Controller {
    
    public function index() {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirect('/');
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
        if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'Coordenador') {
            $this->redirect('/requisicoes');
        }
        
        // Passa para o próximo status
        Requisicao::updateStatus($id, 'Pendente Almoxarifado');
        
        Notificacao::create(
            "Requisição Aprovada", 
            "A requisição #{$id} foi aprovada e enviada ao Almoxarifado.",
            null, 
            'Almoxarife'
        );
        
        $this->redirect('/requisicoes');
    }

    public function reject($id) {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Coordenador', 'Almoxarife'])) {
            $this->redirect('/requisicoes');
        }
        
        Requisicao::updateStatus($id, 'Recusado');
        Notificacao::create(
            "Requisição Recusada", 
            "A requisição #{$id} foi recusada pelo Coordenador.",
            null, 
            'Solicitante'
        );
        $this->redirect('/requisicoes');
    }
}
