<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Gestão de Fornecedores</h2>
                <div class="topbar-actions">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoFornecedor"><i class="ph ph-plus"></i> Novo Fornecedor</button>
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
                                <th>ID</th>
                                <th>Fornecedor</th>
                                <th>CNPJ</th>
                                <th>E-mail</th>
                                <th>Telefone</th>
                                <th>Data Cadastro</th>
                                <th style="text-align: right;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($fornecedores)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum fornecedor cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($fornecedores as $forn): ?>
                                    <tr>
                                        <td>#<?= str_pad((string)$forn['id_fornecedor'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($forn['nome_fantasia']) ?></td>
                                        <td><?= htmlspecialchars($forn['cnpj'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($forn['email'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($forn['telefone'] ?: '-') ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($forn['criado_em'])) ?></td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <button type="button" class="btn btn-edit-forn" style="padding: 0.35rem 0.65rem; font-size: 0.8rem; background-color: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-subtle);"
                                                data-id="<?= (int)$forn['id_fornecedor'] ?>"
                                                data-nome="<?= htmlspecialchars($forn['nome_fantasia'], ENT_QUOTES) ?>"
                                                data-cnpj="<?= htmlspecialchars($forn['cnpj'] ?? '', ENT_QUOTES) ?>"
                                                data-telefone="<?= htmlspecialchars($forn['telefone'] ?? '', ENT_QUOTES) ?>"
                                                data-email="<?= htmlspecialchars($forn['email'] ?? '', ENT_QUOTES) ?>"
                                                title="Editar Fornecedor">
                                                <i class="ph ph-pencil-simple"></i>
                                            </button>
                                            <form action="/fornecedores/excluir" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir este fornecedor?');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="id_fornecedor" value="<?= (int)$forn['id_fornecedor'] ?>">
                                                <button type="submit" class="btn" style="padding: 0.35rem 0.65rem; font-size: 0.8rem; background-color: var(--danger-bg); color: var(--danger);" title="Excluir Fornecedor">
                                                    <i class="ph ph-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Novo Fornecedor -->
    <div class="modal fade" id="modalNovoFornecedor" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Cadastrar Fornecedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <form action="/fornecedores" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Nome Fantasia / Razão Social *</label>
                      <input type="text" name="nome_fantasia" required placeholder="Ex: Distribuidora Central Ltda">
                  </div>
                  <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                      <div class="input-group">
                          <label>CNPJ</label>
                          <input type="text" name="cnpj" placeholder="00.000.000/0001-00">
                      </div>
                      <div class="input-group">
                          <label>Telefone</label>
                          <input type="text" name="telefone" placeholder="(11) 99999-9999">
                      </div>
                  </div>
                  <div class="input-group" style="margin-bottom:0;">
                      <label>E-mail de Contato</label>
                      <input type="email" name="email" placeholder="contato@fornecedor.com.br">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Fornecedor</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Editar Fornecedor -->
    <div class="modal fade" id="modalEditarFornecedor" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Editar Fornecedor</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
          </div>
          <form action="/fornecedores/editar" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="id_fornecedor" id="editFornId">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Nome Fantasia / Razão Social *</label>
                      <input type="text" name="nome_fantasia" id="editFornNome" required>
                  </div>
                  <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                      <div class="input-group">
                          <label>CNPJ</label>
                          <input type="text" name="cnpj" id="editFornCnpj">
                      </div>
                      <div class="input-group">
                          <label>Telefone</label>
                          <input type="text" name="telefone" id="editFornTelefone">
                      </div>
                  </div>
                  <div class="input-group" style="margin-bottom:0;">
                      <label>E-mail de Contato</label>
                      <input type="email" name="email" id="editFornEmail">
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        document.querySelectorAll('.btn-edit-forn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.getElementById('editFornId').value = btn.dataset.id;
                document.getElementById('editFornNome').value = btn.dataset.nome;
                document.getElementById('editFornCnpj').value = btn.dataset.cnpj;
                document.getElementById('editFornTelefone').value = btn.dataset.telefone;
                document.getElementById('editFornEmail').value = btn.dataset.email;
                new bootstrap.Modal(document.getElementById('modalEditarFornecedor')).show();
            });
        });
    </script>
    
<?php include __DIR__ . '/../layouts/footer.php'; ?>
