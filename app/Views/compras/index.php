<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Ordens de Compra</h2>
                <button class="btn btn-primary animate-fade-up delay-100" onclick="document.getElementById('modal-novo').style.display='flex'"><i class="ph ph-plus"></i> Registrar Compra</button>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>OC #</th>
                                <th>Material (Req)</th>
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
                                        <td>#<?= str_pad($comp['id_compra'], 5, '0', STR_PAD_LEFT) ?></td>
                                        <td style="font-weight: 500; color: var(--text-primary);"><?= htmlspecialchars($comp['material']) ?></td>
                                        <td><?= $comp['quantidade'] ?></td>
                                        <td><?= htmlspecialchars($comp['nome_fantasia']) ?></td>
                                        <td style="font-weight: 600;">R$ <?= number_format($comp['valor_total'], 2, ',', '.') ?></td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($comp['data_compra'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal Nova Compra -->
    <div id="modal-novo" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:1050; align-items:center; justify-content:center;">
        <div class="glass-panel" style="width: 100%; max-width: 600px; padding: 2rem; background: var(--bg-card);">
            <div style="display:flex; justify-content:space-between; margin-bottom:1.5rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Registrar Ordem de Compra</h3>
                <button onclick="document.getElementById('modal-novo').style.display='none'" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            
            <form action="/compras" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>Vincular Requisição</label>
                        <select name="id_requisicao" required>
                            <option value="">Selecione uma requisição aprovada/pendente...</option>
                            <?php foreach($requisicoes as $r): ?>
                                <option value="<?= $r['id_requisicao'] ?>">#<?= $r['id_requisicao'] ?> - <?= htmlspecialchars($r['material']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>Fornecedor</label>
                        <select name="id_fornecedor" required>
                            <option value="">Selecione o fornecedor...</option>
                            <?php foreach($fornecedores as $f): ?>
                                <option value="<?= $f['id_fornecedor'] ?>"><?= htmlspecialchars($f['nome_fantasia']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group" style="grid-column: 1 / -1;">
                        <label>Valor Total (R$)</label>
                        <input type="number" step="0.01" name="valor_total" placeholder="0.00" required>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                    <button type="button" class="btn" onclick="document.getElementById('modal-novo').style.display='none'" style="background-color: var(--bg-base);">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Compra</button>
                </div>
            </form>
        </div>
    </div>
    
<?php include __DIR__ . '/../layouts/footer.php'; ?>
