<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <div>
                    <h2 style="margin-bottom: 0.5rem;"><a href="/estoque" style="color: var(--text-secondary); text-decoration: none;"><i class="ph ph-arrow-left"></i> Estoque</a> / Kardex</h2>
                    <h3 style="color: var(--text-primary); font-weight: 500; font-size: 1.1rem;">
                        Produto: <?= htmlspecialchars($produto['nome_produto']) ?> 
                        <span style="color: var(--text-secondary); font-size: 0.9rem;">(Cód: <?= str_pad($produto['id_produto'], 4, '0', STR_PAD_LEFT) ?>)</span>
                    </h3>
                </div>
                <button class="btn btn-primary animate-fade-up delay-100" onclick="document.getElementById('modal-ajuste').style.display='flex'"><i class="ph ph-sliders"></i> Ajuste Manual</button>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Data e Hora</th>
                                <th>Tipo de Movimentação</th>
                                <th>Qtd. Movimentada</th>
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
                                        <td style="font-weight: 600; font-size: 1.1rem; text-align: center;">
                                            <?= $mov['tipo'] === 'Entrada' ? '+' : '-' ?><?= $mov['quantidade'] ?>
                                        </td>
                                        <td><?= htmlspecialchars($mov['usuario_nome'] ?? 'Sistema') ?></td>
                                        <td><?= htmlspecialchars($mov['observacao']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal de Ajuste Manual -->
    <div id="modal-ajuste" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1050; align-items:center; justify-content:center;">
        <div class="glass-panel" style="width: 100%; max-width: 500px; padding: 2rem; background: var(--bg-card);">
            <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Ajuste de Estoque Manual</h3>
                <button onclick="document.getElementById('modal-ajuste').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form action="/estoque/ajustar" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" name="id_produto" value="<?= $produto['id_produto'] ?>">
                
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr; gap: 1.5rem;">
                    <div class="input-group">
                        <label>Tipo de Ajuste</label>
                        <select name="tipo" required>
                            <option value="Entrada">Entrada (+)</option>
                            <option value="Saída">Saída (-)</option>
                            <option value="Transferência">Transferência (Saída)</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>Quantidade</label>
                        <input type="number" name="quantidade" min="1" required>
                    </div>
                    <div class="input-group">
                        <label>Motivo / Observação</label>
                        <input type="text" name="observacao" placeholder="Ex: Quebra, Transferido p/ Loja 2, Compra Direta" required>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                    <button type="button" class="btn" onclick="document.getElementById('modal-ajuste').style.display='none'" style="background-color: var(--bg-base);">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Confirmar Ajuste</button>
                </div>
            </form>
        </div>
    </div>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
