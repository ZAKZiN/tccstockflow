<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <div style="display: flex; align-items: center; gap: 0.85rem;">
                    <a href="/requisicoes" style="color: var(--text-secondary); text-decoration: none;"><i class="ph ph-arrow-left" style="font-size: 1.4rem;"></i></a>
                    <h2>Nova Requisição de Material</h2>
                </div>
            </header>

            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-error animate-fade-up">
                    <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
                </div>
            <?php endif; ?>

            <div class="glass-panel animate-fade-up delay-100" style="padding: 2rem; max-width: 780px;">
                <form action="/requisicoes/nova" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem;">
                        <div class="input-group" style="grid-column: 1 / -1;">
                            <label for="material">Material / Produto *</label>
                            <input type="text" id="material" name="material" list="catalogoProdutos" placeholder="Digite ou selecione um produto cadastrado..." required>
                            <?php if(!empty($produtosCatalogo)): ?>
                                <datalist id="catalogoProdutos">
                                    <?php foreach($produtosCatalogo as $nomeProd): ?>
                                        <option value="<?= htmlspecialchars($nomeProd) ?>"></option>
                                    <?php endforeach; ?>
                                </datalist>
                            <?php endif; ?>
                        </div>
                        
                        <div class="input-group">
                            <label for="quantidade">Quantidade *</label>
                            <input type="number" step="0.001" id="quantidade" name="quantidade" min="0.001" value="1" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="prioridade">Prioridade</label>
                            <select id="prioridade" name="prioridade">
                                <option value="Baixa">Baixa</option>
                                <option value="Média" selected>Média</option>
                                <option value="Alta">Alta</option>
                                <option value="Urgente">Urgente</option>
                            </select>
                        </div>
                        
                        <div class="input-group" style="grid-column: 1 / -1;">
                            <label for="justificativa">Justificativa / Finalidade (Opcional)</label>
                            <textarea id="justificativa" name="justificativa" rows="3" placeholder="Descreva a necessidade de reposição ou uso pelo setor..."></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 0.85rem; margin-top: 1rem;">
                        <a href="/requisicoes" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-subtle);">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-tilt"></i> Enviar Requisição</button>
                    </div>
                </form>
            </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
