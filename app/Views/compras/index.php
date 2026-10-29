<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Ordens de Compra</h2>
                <div class="topbar-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaCompra"><i class="ph ph-plus"></i> Registrar Compra</button>
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
                                <th>OC #</th>
                                <th>Material (Requisição)</th>
                                <th>Qtd</th>
                                <th>Fornecedor</th>
                                <th>Valor Total</th>
                                <th>Data da Compra</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($compras)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhuma ordem de compra registrada.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($compras as $comp): ?>
                                    <tr>
                                        <td>#<?= str_pad((string)$comp['id_compra'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($comp['material']) ?></td>
                                        <td><?= rtrim(rtrim(number_format((float)$comp['quantidade'], 3, ',', '.'), '0'), ',') ?></td>
                                        <td><?= htmlspecialchars($comp['nome_fantasia'] ?? '-') ?></td>
                                        <td style="font-weight: 600; color: var(--success);">R$ <?= number_format((float)$comp['valor_total'], 2, ',', '.') ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($comp['data_compra'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Nova Compra -->
    <div class="modal fade" id="modalNovaCompra" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Registrar Ordem de Compra</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <form action="/compras" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Requisição Aprovada *</label>
                      <select name="id_requisicao" required>
                          <option value="">Selecione uma requisição aprovada...</option>
                          <?php foreach($requisicoes as $r): ?>
                              <option value="<?= (int)$r['id_requisicao'] ?>">
                                  #<?= (int)$r['id_requisicao'] ?> — <?= htmlspecialchars($r['material']) ?> (Qtd: <?= rtrim(rtrim(number_format((float)$r['quantidade'], 3, ',', '.'), '0'), ',') ?>)
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="input-group">
                      <label>Fornecedor *</label>
                      <select name="id_fornecedor" required>
                          <option value="">Selecione o fornecedor...</option>
                          <?php foreach($fornecedores as $f): ?>
                              <option value="<?= (int)$f['id_fornecedor'] ?>"><?= htmlspecialchars($f['nome_fantasia']) ?></option>
                          <?php endforeach; ?>
                      </select>
                  </div>
                  <div class="input-group" style="margin-bottom: 0.75rem;">
                      <label>Valor Total da Nota/Compra (R$) *</label>
                      <input type="number" step="0.01" min="0.01" name="valor_total" placeholder="0.00" required>
                  </div>
                  <small style="color: var(--text-secondary); display: block;">
                      <i class="ph ph-info"></i> Se o material corresponder a um produto do estoque, a quantidade comprada entrará automaticamente no saldo e no Kardex.
                  </small>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Registrar Compra</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    
<?php include __DIR__ . '/../layouts/footer.php'; ?>
