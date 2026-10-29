<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Fiado / Contas a Receber</h2>
                <div class="topbar-actions">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoClienteFiado">
                        <i class="ph ph-user-plus"></i> Cadastrar Cliente
                    </button>
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
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; max-width: 420px;">
                    <div class="input-icon-wrapper" style="flex:1;">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por Cliente ou Nº da Venda...">
                    </div>
                </div>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Cód. Conta</th>
                                <th>Venda Ref.</th>
                                <th>Cliente</th>
                                <th>Valor Total</th>
                                <th>Status</th>
                                <th>Data da Venda</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($contas)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma conta a receber encontrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($contas as $c): ?>
                                    <?php
                                        $foneDigits = preg_replace('/\D/', '', $c['telefone'] ?? '');
                                        if (strlen($foneDigits) === 10 || strlen($foneDigits) === 11) {
                                            $foneDigits = '55' . $foneDigits;
                                        }
                                    ?>
                                    <tr class="fiado-row">
                                        <td>#<?= str_pad((string)$c['id_conta'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="fiado-venda">
                                            <a href="/pdv/recibo/<?= (int)$c['id_venda'] ?>" target="_blank" style="color: var(--primary); font-weight: 500; text-decoration: none;">
                                                <i class="ph ph-receipt"></i> Venda #<?= str_pad((string)$c['id_venda'], 4, '0', STR_PAD_LEFT) ?>
                                            </a>
                                        </td>
                                        <td class="fiado-cliente" style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($c['cliente']) ?></td>
                                        <td style="color: var(--danger); font-weight: 600;">R$ <?= number_format((float)$c['valor_total'], 2, ',', '.') ?></td>
                                        <td>
                                            <?php if($c['status'] === 'Pendente'): ?>
                                                <span class="badge badge-warning">Pendente</span>
                                            <?php else: ?>
                                                <span class="badge badge-success">Pago</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($c['criado_em'])) ?></td>
                                        <td>
                                            <?php if($c['status'] === 'Pendente'): ?>
                                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                    <form action="/fiado/pagar/<?= (int)$c['id_conta'] ?>" method="POST" style="display:inline; margin:0;" data-cliente="<?= htmlspecialchars($c['cliente'], ENT_QUOTES) ?>" onsubmit="return confirmarBaixa(event, this);">
                                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                        <button type="submit" class="btn" style="background-color: var(--success-bg); color: var(--success); padding: 0.3rem 0.65rem; font-size: 0.82rem;">
                                                            <i class="ph ph-check-circle"></i> Dar Baixa
                                                        </button>
                                                    </form>
                                                    <?php if($foneDigits !== ''): ?>
                                                        <a href="https://wa.me/<?= $foneDigits ?>?text=<?= urlencode("Olá " . $c['cliente'] . ", consta um débito pendente de R$ " . number_format((float)$c['valor_total'], 2, ',', '.') . " referente à Venda #" . str_pad((string)$c['id_venda'], 4, '0', STR_PAD_LEFT) . ". Podemos ajudar com o pagamento?") ?>" target="_blank" rel="noopener noreferrer" class="btn" style="background-color: #25D366; color: white; padding: 0.3rem 0.6rem; font-size: 0.85rem;" title="Cobrar por WhatsApp">
                                                            <i class="ph ph-whatsapp-logo"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: var(--text-secondary);">Quitado</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Novo Cliente -->
    <div class="modal fade" id="modalNovoClienteFiado" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Cadastrar Novo Cliente</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <form action="/clientes" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Nome Completo *</label>
                      <input type="text" name="nome" required placeholder="Ex: Carlos Mendes">
                  </div>
                  <div class="input-group">
                      <label>WhatsApp / Telefone (com DDD)</label>
                      <input type="text" name="telefone" placeholder="Ex: 11999998888">
                  </div>
                  <div class="input-group" style="margin-bottom:0;">
                      <label>E-mail (Opcional)</label>
                      <input type="email" name="email" placeholder="cliente@email.com">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Cliente</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.fiado-row').forEach(row => {
                const cliente = row.querySelector('.fiado-cliente').innerText.toLowerCase();
                const venda = row.querySelector('.fiado-venda').innerText.toLowerCase();
                row.style.display = (cliente.includes(term) || venda.includes(term)) ? '' : 'none';
            });
        });

        function confirmarBaixa(event, form) {
            event.preventDefault();
            const cliente = form.getAttribute('data-cliente') || 'este cliente';
            Swal.fire({
                title: 'Confirmar Recebimento',
                text: `Deseja confirmar o pagamento da conta de ${cliente}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#059669',
                cancelButtonColor: '#dc2626',
                confirmButtonText: 'Sim, Dar Baixa',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
            return false;
        }
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
