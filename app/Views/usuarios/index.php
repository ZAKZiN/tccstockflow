<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Gestão de Usuários e Cargos</h2>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoUsuario">
                    <i class="ph ph-plus"></i> Novo Usuário
                </button>
            </header>

            <?php if(isset($error)): ?>
                <div class="alert alert-error animate-fade-up">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if(isset($success)): ?>
                <div class="alert alert-success animate-fade-up">
                    <i class="ph ph-check-circle"></i> <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Login</th>
                                <th>Cargo (Nível de Acesso)</th>
                                <th>Data de Criação</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($usuarios)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum usuário cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($usuarios as $user): ?>
                                    <tr>
                                        <td style="font-weight: 500; color: var(--text-primary);">
                                            <?= htmlspecialchars($user['nome']) ?>
                                            <?php if($user['id_usuario'] == $_SESSION['usuario_id']): ?>
                                                <span class="badge badge-success" style="margin-left: 0.5rem;">Você</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($user['login']) ?></td>
                                        <td>
                                            <?php 
                                                $nivel = $user['nivel_acesso'];
                                                $badgeClass = 'badge-success'; // Padrão
                                                if ($nivel === 'Administrador') $badgeClass = 'badge-danger';
                                                elseif ($nivel === 'Gerente') $badgeClass = 'badge-warning';
                                                elseif ($nivel === 'Estoquista') $badgeClass = 'badge-success';
                                            ?>
                                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($nivel) ?></span>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($user['criado_em'])) ?></td>
                                        <td>
                                            <?php if($user['id_usuario'] != $_SESSION['usuario_id']): ?>
                                                <form action="/usuarios/excluir" method="POST" style="display:inline;" onsubmit="return confirmExclusao(event, this);">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="id_usuario" value="<?= $user['id_usuario'] ?>">
                                                    <button type="submit" class="btn" style="background-color: var(--danger-bg); color: var(--danger); padding: 0.25rem 0.5rem; font-size: 0.85rem;">
                                                        <i class="ph ph-trash"></i> Excluir
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Novo Usuário -->
    <div class="modal fade" id="modalNovoUsuario" tabindex="-1">
      <div class="modal-dialog">
        <div class="modal-content" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);">
          <div class="modal-header border-0">
            <h5 class="modal-title" style="font-weight: 600;">Cadastrar Novo Usuário</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="/usuarios" method="POST">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <div class="modal-body">
                  <div class="input-group">
                      <label>Nome Completo</label>
                      <input type="text" name="nome_usuario" required placeholder="Ex: João Silva">
                  </div>
                  
                  <div class="input-group">
                      <label>Login de Acesso</label>
                      <input type="text" name="login" required placeholder="Ex: joao.silva">
                  </div>
                  
                  <div class="input-group">
                      <label>Senha Provisória</label>
                      <input type="password" name="senha" required placeholder="******">
                  </div>
                  
                  <div class="input-group">
                      <label>Cargo (Permissões)</label>
                      <select name="nivel_acesso" required>
                          <option value="Operador de Caixa">Operador de Caixa (Só Vendas e Fiado)</option>
                          <option value="Estoquista">Estoquista (Só Estoque e Compras)</option>
                          <option value="Gerente">Gerente (Acesso aos Dashboards e Lojas)</option>
                          <option value="Administrador">Administrador (Acesso Total)</option>
                      </select>
                  </div>
                  
                  <div class="alert alert-warning" style="margin-bottom: 0;">
                      <i class="ph ph-info"></i> As permissões bloqueiam o acesso às URLs automaticamente.
                  </div>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn" style="background-color: var(--bg-base); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Salvar Usuário</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script>
        function confirmExclusao(event, form) {
            event.preventDefault();
            Swal.fire({
                title: 'Remover Usuário?',
                text: "Esta ação revogará o acesso dele ao sistema imediatamente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
