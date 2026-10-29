<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <div>
                    <h2 style="margin-bottom: 0.35rem;">
                        <a href="/estoque" style="color: var(--text-secondary); text-decoration: none;"><i class="ph ph-arrow-left"></i> Estoque</a> / Kardex
                    </h2>
                    <div style="color: var(--text-primary); font-weight: 500; font-size: 0.95rem;">
                        Produto: <strong><?= htmlspecialchars($produto['nome_produto']) ?></strong>
                        <span style="color: var(--text-secondary);">
                            (Cód: #<?= str_pad((string)$produto['id_produto'], 4, '0', STR_PAD_LEFT) ?> • Saldo Atual: <?= rtrim(rtrim(number_format((float)$produto['quantidade_estoque'], 3, ',', '.'), '0'), ',') ?>)
                        </span>
                    </div>
                </div>
                <div class="topbar-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAjusteKardex"><i class="ph ph-sliders"></i> Ajuste Manual</button>
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
                                <th>Data e Hora</th>
                                <th>Tipo de Movimentação</th>
                                <th style="text-align: center;">Qtd. Movimentada</th>
                                <th>Usuário Responsável</th>
                                <th>Observação / Origem</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($movimentacoes)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma movimentação registrada para este produto.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($movimentacoes as $mov): ?>
                                    <tr>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i:s', strtotime($mov['data_movimentacao'])) ?></td>
                                        <td>
                                            <?php if($mov['tipo'] === 'Entrada'): ?>
                                                <span class="badge badge-success"><i class="ph ph-arrow-down-left"></i> Entrada</span>
                                            <?php elseif($mov['tipo'] === 'Saída'): ?>
                                                <span class="badge badge-danger"><i class="ph ph-arrow-up-right"></i> Saída</span>
                                            <?php else: ?>
                                                <span class="badge badge-warning"><i class="ph ph-arrows-left-right"></i> Transferência</span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="font-weight: 600; font-size: 1.05rem; text-align: center; color: <?= $mov['tipo'] === 'Entrada' ? 'var(--success)' : 'var(--danger)' ?>;">
                                            <?= $mov['tipo'] === 'Entrada' ? '+' : '-' ?><?= rtrim(rtrim(number_format((float)$mov['quantidade'], 3, ',', '.'), '0'), ',') ?>
                                        </td>
                                        <td><?= htmlspecialchars($mov['usuario_nome'] ?? 'Sistema') ?></td>
                                        <td><?= htmlspecialchars($mov['observacao'] ?? '') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal de Ajuste Manual -->
    <div class="modal fade" id="modalAjusteKardex" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Ajuste de Estoque Manual</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <form action="/estoque/ajustar" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="id_produto" value="<?= (int)$produto['id_produto'] ?>">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Tipo de Ajuste *</label>
                      <select name="tipo" required>
                          <option value="Entrada">Entrada (+)</option>
                          <option value="Saída">Saída / Baixa por Perda (-)</option>
                          <option value="Transferência">Transferência entre Setores (-)</option>
                      </select>
                  </div>
                  <div class="input-group">
                      <label>Quantidade *</label>
                      <input type="number" step="0.001" min="0.001" name="quantidade" required placeholder="Ex: 5">
                  </div>
                  <div class="input-group" style="margin-bottom:0;">
                      <label>Motivo / Observação *</label>
                      <input type="text" name="observacao" placeholder="Ex: Inventário físico, avaria, reposição avulsa" required>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Confirmar Ajuste</button>
              </div>
          </form>
        </div>
      </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
