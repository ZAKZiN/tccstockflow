<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Controle de Caixa (Turno)</h2>
            </header>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error animate-fade-up">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <?php if(!$caixaAberto): ?>
                <div class="glass-panel animate-fade-up delay-200" style="padding: 3rem 2rem; text-align: center; max-width: 500px; margin: 1.5rem auto;">
                    <i class="ph ph-lock-key" style="font-size: 3.5rem; color: var(--text-secondary); margin-bottom: 1rem;"></i>
                    <h3 style="margin-bottom: 0.75rem;">Caixa Fechado</h3>
                    <p style="color: var(--text-secondary); margin-bottom: 2rem;">Abra um novo turno informando o fundo de troco inicial em dinheiro para habilitar as vendas no PDV.</p>
                    
                    <div class="input-group" style="text-align: left; margin-bottom: 1.5rem;">
                        <label for="saldoInicial">Fundo de Troco Inicial em Dinheiro (R$)</label>
                        <input type="number" step="0.01" min="0" id="saldoInicial" value="0.00">
                    </div>
                    <button class="btn btn-primary" onclick="abrirCaixa()" style="width: 100%;"><i class="ph ph-check-circle"></i> Abrir Caixa</button>
                </div>
            <?php else: ?>
                <div class="stats-grid animate-fade-up delay-100">
                    <div class="stat-card glass-panel" style="border-left: 4px solid var(--primary);">
                        <div class="stat-icon icon-blue">
                            <i class="ph ph-coins"></i>
                        </div>
                        <div class="stat-info">
                            <h3>R$ <?= number_format((float)$caixaAberto['saldo_atual'], 2, ',', '.') ?></h3>
                            <p>Saldo Físico na Gaveta (Dinheiro)</p>
                        </div>
                    </div>

                    <div class="stat-card glass-panel" style="border-left: 4px solid var(--success);">
                        <div class="stat-icon" style="background: var(--success-bg); color: var(--success);">
                            <i class="ph ph-credit-card"></i>
                        </div>
                        <div class="stat-info">
                            <h3>R$ <?= number_format((float)($caixaAberto['total_eletronico'] ?? 0), 2, ',', '.') ?></h3>
                            <p>Vendas Eletrônicas (Pix / Cartão)</p>
                        </div>
                    </div>
                    
                    <div class="stat-card glass-panel" style="justify-content: center; flex-direction: column; gap: 0.6rem;">
                        <div style="display: flex; gap: 0.75rem; width: 100%;">
                            <button class="btn btn-primary" onclick="lancar('Suprimento')" style="flex:1;"><i class="ph ph-plus-circle"></i> Suprimento</button>
                            <button class="btn" onclick="lancar('Sangria')" style="flex:1; background-color: var(--warning-bg); color: var(--warning);"><i class="ph ph-minus-circle"></i> Sangria</button>
                        </div>
                        <button class="btn" onclick="fecharCaixa(<?= (int)$caixaAberto['id_caixa'] ?>)" style="width: 100%; background-color: var(--danger-bg); color: var(--danger);"><i class="ph ph-lock-key"></i> Fechar Caixa e Emitir Z</button>
                    </div>
                </div>

                <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1.25rem;">
                        <h4 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Movimentações do Turno #<?= (int)$caixaAberto['id_caixa'] ?></h4>
                        <span style="font-size: 0.875rem; color: var(--text-secondary);">
                            Total Geral do Turno: <strong style="color: var(--text-primary);">R$ <?= number_format((float)($caixaAberto['saldo_geral'] ?? 0), 2, ',', '.') ?></strong>
                        </span>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Horário</th>
                                    <th>Tipo</th>
                                    <th>Meio</th>
                                    <th>Descrição</th>
                                    <th>Valor</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($movimentacoes as $mov): ?>
                                    <tr>
                                        <td style="color: var(--text-secondary);"><?= date('d/m H:i:s', strtotime($mov['data_movimentacao'])) ?></td>
                                        <td>
                                            <?php if($mov['tipo'] === 'Sangria'): ?>
                                                <span class="badge badge-warning">Sangria</span>
                                            <?php elseif($mov['tipo'] === 'Suprimento'): ?>
                                                <span class="badge badge-primary">Suprimento</span>
                                            <?php elseif($mov['tipo'] === 'Abertura'): ?>
                                                <span class="badge badge-primary">Abertura</span>
                                            <?php elseif($mov['tipo'] === 'Recebimento Fiado'): ?>
                                                <span class="badge badge-success">Rec. Fiado</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Venda</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><small style="color: var(--text-secondary);"><?= htmlspecialchars($mov['metodo_pagamento'] ?? 'Dinheiro') ?></small></td>
                                        <td><?= htmlspecialchars($mov['descricao']) ?></td>
                                        <td style="font-weight: 600; color: <?= $mov['tipo'] === 'Sangria' ? 'var(--danger)' : 'var(--success)' ?>">
                                            <?= $mov['tipo'] === 'Sangria' ? '-' : '+' ?> R$ <?= number_format((float)$mov['valor'], 2, ',', '.') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <script>
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                async function abrirCaixa() {
                    const saldo = parseFloat(document.getElementById('saldoInicial').value || '0');
                    if (isNaN(saldo) || saldo < 0) {
                        return Swal.fire('Atenção', 'Informe um valor inicial válido (>= 0).', 'warning');
                    }
                    const res = await fetch('/caixa/abrir', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                        body: `saldo_inicial=${encodeURIComponent(saldo)}&csrf_token=${encodeURIComponent(csrfToken)}`
                    });
                    const data = await res.json();
                    if (data.success) {
                        Swal.fire('Sucesso', 'Caixa aberto com sucesso!', 'success').then(() => window.location.reload());
                    } else {
                        Swal.fire('Erro', data.message || 'Erro ao abrir caixa.', 'error');
                    }
                }

                function lancar(tipo) {
                    Swal.fire({
                        title: `Lançar ${tipo}`,
                        html: `
                            <input type="number" id="swal-input1" class="swal2-input" placeholder="Valor em Dinheiro (R$)" step="0.01" min="0.01">
                            <input type="text" id="swal-input2" class="swal2-input" placeholder="Motivo / Descrição">
                        `,
                        focusConfirm: false,
                        showCancelButton: true,
                        confirmButtonText: 'Confirmar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            const val = document.getElementById('swal-input1').value;
                            const desc = document.getElementById('swal-input2').value.trim();
                            if (!val || parseFloat(val) <= 0 || !desc) {
                                Swal.showValidationMessage('Informe um valor positivo e o motivo.');
                                return false;
                            }
                            return [val, desc];
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const [val, desc] = result.value;
                            const res = await fetch('/caixa/lancar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                                body: `id_caixa=<?= (int)($caixaAberto['id_caixa'] ?? 0) ?>&tipo=${encodeURIComponent(tipo)}&valor=${encodeURIComponent(val)}&descricao=${encodeURIComponent(desc)}&csrf_token=${encodeURIComponent(csrfToken)}`
                            });
                            const data = await res.json();
                            if (data.success) window.location.reload();
                            else Swal.fire('Atenção', data.message || 'Não foi possível lançar.', 'error');
                        }
                    });
                }

                function fecharCaixa(id) {
                    Swal.fire({
                        title: 'Fechar Caixa',
                        text: 'Deseja encerrar este turno e emitir o Relatório Z?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, fechar!',
                        cancelButtonText: 'Cancelar',
                        confirmButtonColor: '#ef4444'
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            const res = await fetch('/caixa/fechar', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                                body: `id_caixa=${encodeURIComponent(id)}&csrf_token=${encodeURIComponent(csrfToken)}`
                            });
                            const data = await res.json();
                            if (data.success) {
                                Swal.fire({
                                    title: 'Caixa Fechado!',
                                    text: 'O turno foi encerrado com sucesso.',
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'Concluir',
                                    cancelButtonText: 'Imprimir Relatório Z'
                                }).then((r) => {
                                    if (r.dismiss === Swal.DismissReason.cancel) {
                                        window.open('/caixa/relatorio/' + data.id_caixa, '_blank', 'width=420,height=650');
                                    }
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire('Erro', data.message || 'Erro ao fechar caixa.', 'error');
                            }
                        }
                    });
                }
            </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
