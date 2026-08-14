<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Gestão de Fornecedores</h2>
                <button class="btn btn-primary animate-fade-up delay-100" onclick="document.getElementById('modal-novo').style.display='flex'"><i class="ph ph-plus"></i> Novo Fornecedor</button>
            </header>

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
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($fornecedores)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum fornecedor cadastrado.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($fornecedores as $forn): ?>
                                    <tr>
                                        <td>#<?= str_pad($forn['id_fornecedor'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 600; color: var(--text-primary);"><?= htmlspecialchars($forn['nome_fantasia']) ?></td>
                                        <td><?= htmlspecialchars($forn['cnpj']) ?></td>
                                        <td><?= htmlspecialchars($forn['email']) ?></td>
                                        <td><?= htmlspecialchars($forn['telefone']) ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($forn['criado_em'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Novo Fornecedor (Simples) -->
    <div id="modal-novo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1050; align-items:center; justify-content:center;">
        <div class="glass-panel" style="width: 100%; max-width: 600px; padding: 2rem; background: var(--bg-card);">
            <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Cadastrar Fornecedor</h3>
                <button onclick="document.getElementById('modal-novo').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form action="/fornecedores" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>Nome Fantasia / Razão Social</label>
                        <input type="text" name="nome_fantasia" required>
                    </div>
                    <div class="input-group">
                        <label>CNPJ</label>
                        <input type="text" name="cnpj" placeholder="00.000.000/0000-00">
                    </div>
                    <div class="input-group">
                        <label>Telefone</label>
                        <input type="text" name="telefone" placeholder="(00) 0000-0000">
                    </div>
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>E-mail de Contato</label>
                        <input type="email" name="email">
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn" onclick="document.getElementById('modal-novo').style.display='none'" style="background-color: var(--bg-base);">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar Fornecedor</button>
                </div>
            </form>
        </div>
    </div>
    
<?php include __DIR__ . '/../layouts/footer.php'; ?>
