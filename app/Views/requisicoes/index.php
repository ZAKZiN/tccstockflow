<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Gestão de Requisições Internas</h2>
                <div class="topbar-actions">
                    <button class="btn" onclick="window.print()" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-printer"></i> Imprimir</button>
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <a href="/requisicoes/nova" class="btn btn-primary"><i class="ph ph-plus"></i> Nova Requisição</a>
                </div>
            </header>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error animate-fade-up">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_GET['success'])): ?>
                <div class="alert alert-success animate-fade-up">
                    <i class="ph ph-check-circle"></i> <?= htmlspecialchars($_GET['success']) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Material</th>
                                <th>Qtd</th>
                                <th>Prioridade</th>
                                <th>Solicitante</th>
                                <th>Setor</th>
                                <th>Status</th>
                                <th>Data</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($requisicoes)): ?>
                                <tr>
                                    <td colspan="9" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma requisição encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($requisicoes as $req): ?>
                                    <tr>
                                        <td>#<?= str_pad((string)$req['id_requisicao'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);">
                                            <?= htmlspecialchars($req['material']) ?>
                                            <?php if(!empty($req['justificativa'])): ?>
                                                <br><small style="color: var(--text-secondary); font-weight: normal;"><?= htmlspecialchars($req['justificativa']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= rtrim(rtrim(number_format((float)$req['quantidade'], 3, ',', '.'), '0'), ',') ?></td>
                                        <td><?= htmlspecialchars($req['prioridade'] ?? 'Média') ?></td>
                                        <td><?= htmlspecialchars($req['solicitante']) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><i class="ph ph-buildings"></i> <?= htmlspecialchars($req['nome_setor'] ?? 'Geral') ?></td>
                                        <td>
                                            <?php 
                                                $s = $req['status'];
                                                $badgeClass = 'badge-warning';
                                                if (strpos($s, 'Almoxarifado') !== false) $badgeClass = 'badge-primary';
                                                elseif (strpos($s, 'Efetuada') !== false || strpos($s, 'Despachado') !== false) $badgeClass = 'badge-success';
                                                elseif ($s === 'Recusado') $badgeClass = 'badge-danger';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($s) ?></span>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($req['data_solicitacao'])) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.45rem; flex-wrap: wrap;">
                                                <?php if($req['status'] === 'Pendente Coordenador' && in_array($_SESSION['usuario_nivel'] ?? '', ['Coordenador', 'Gerente', 'Administrador'], true)): ?>
                                                    <form action="/requisicoes/aprovar/<?= (int)$req['id_requisicao'] ?>" method="POST" style="display:inline; margin:0;">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn" style="background-color: var(--success-bg); color: var(--success); padding: 0.25rem 0.55rem; font-size: 0.78rem;">
                                                            <i class="ph ph-check"></i> Aprovar
                                                        </button>
                                                    </form>
                                                    <form action="/requisicoes/recusar/<?= (int)$req['id_requisicao'] ?>" method="POST" style="display:inline; margin:0;" onsubmit="return confirmarAcaoReq(event, this, 'Recusar Requisição?', 'Deseja recusar esta solicitação?');">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn" style="background-color: var(--danger-bg); color: var(--danger); padding: 0.25rem 0.55rem; font-size: 0.78rem;">
                                                            <i class="ph ph-x"></i> Recusar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if(in_array($req['status'], ['Pendente Almoxarifado', 'Compra Efetuada'], true) && in_array($_SESSION['usuario_nivel'] ?? '', ['Estoquista', 'Gerente', 'Administrador'], true)): ?>
                                                    <form action="/requisicoes/despachar/<?= (int)$req['id_requisicao'] ?>" method="POST" style="display:inline; margin:0;" onsubmit="return confirmarAcaoReq(event, this, 'Despachar Material?', 'Isso registrará a saída do item no Kardex e marcará a requisição como atendida.');">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn" style="background-color: var(--accent-light); color: var(--primary); padding: 0.25rem 0.55rem; font-size: 0.78rem;">
                                                            <i class="ph ph-package"></i> Despachar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <script>
        document.querySelector('.topbar h2').setAttribute('data-date', new Date().toLocaleString('pt-BR'));

        function confirmarAcaoReq(event, form, titulo, texto) {
            event.preventDefault();
            Swal.fire({
                title: titulo,
                text: texto,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#2563eb'
            }).then((res) => {
                if (res.isConfirmed) form.submit();
            });
            return false;
        }
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
