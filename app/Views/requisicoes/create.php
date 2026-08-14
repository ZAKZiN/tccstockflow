<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <a href="/requisicoes" style="color: var(--text-secondary); text-decoration: none; transition: var(--transition);" onmouseover="this.style.color='var(--text-primary)'" onmouseout="this.style.color='var(--text-secondary)'"><i class="ph ph-arrow-left" style="font-size: 1.5rem;"></i></a>
                    <h2>Nova Requisição</h2>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-100" style="padding: 2.5rem; max-width: 800px;">
                <form action="/requisicoes/nova" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    
                    <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div class="input-group" style="grid-column: 1 / -1;">
                            <label for="material">Material/Produto</label>
                            <input type="text" id="material" name="material" placeholder="Ex: Pacote de Folha Sulfite A4" required>
                        </div>
                        
                        <div class="input-group">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" min="1" value="1" required>
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
                            <label for="justificativa">Justificativa (Opcional)</label>
                            <textarea id="justificativa" name="justificativa" rows="3" placeholder="Por que este material é necessário?"></textarea>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                        <a href="/requisicoes" class="btn" style="background-color: rgba(255,255,255,0.05); color: var(--text-primary); border: 1px solid var(--border-subtle);">Cancelar</a>
                        <button type="submit" class="btn btn-primary"><i class="ph ph-paper-plane-tilt"></i> Enviar Requisição</button>
                    </div>

                </form>
            </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
