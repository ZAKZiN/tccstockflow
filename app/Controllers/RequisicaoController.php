<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Requisicao;

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
        $this->redirect('/requisicoes');
    }

    public function reject($id) {
        if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_nivel'], ['Coordenador', 'Almoxarife'])) {
            $this->redirect('/requisicoes');
        }
        
        Requisicao::updateStatus($id, 'Recusado');
        $this->redirect('/requisicoes');
    }
}
