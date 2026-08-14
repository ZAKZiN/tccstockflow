<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Controle de Estoque</h2>
                <div style="display: flex; gap: 1rem;">
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <button class="btn btn-primary" onclick="window.print()"><i class="ph ph-printer"></i> Imprimir Relatório</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoProduto"><i class="ph ph-plus"></i> Cadastrar Produto</button>
                </div>
            </header>

            <div class="glass-panel animate-fade-up delay-200" style="padding: 1.5rem;">
                
                <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; max-width: 500px;">
                    <div class="input-icon-wrapper" style="flex:1;">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por Nome, ID ou Código de barras...">
                    </div>
                    <button class="btn" style="background-color:var(--accent-light); color:var(--accent-color);" onclick="startScanner()"><i class="ph ph-barcode"></i> Escanear</button>
                </div>

                <div class="table-container">
                    <table id="estoqueTable">
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Produto (Cód Barras)</th>
                                <th>Qtd Atual</th>
                                <th>Preço de Venda</th>
                                <th>Validade / Lote</th>
                                <th>Status</th>
                                <th>Última Atualização</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($produtos)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                        Nenhum produto cadastrado no estoque.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($produtos as $prod): ?>
                                    <tr class="produto-row">
                                        <td class="prod-id">#<?= str_pad($prod['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="prod-nome" style="font-weight: 500; color: var(--text-primary);">
                                            <?= htmlspecialchars($prod['nome_produto']) ?>
                                            <br><small style="color: var(--text-secondary); font-weight: normal;"><?= htmlspecialchars($prod['codigo_barras'] ?? 'S/N') ?></small>
                                        </td>
                                        <td style="font-size: 1.125rem; font-weight: 600;"><?= $prod['quantidade_estoque'] ?></td>
                                        <td style="color: #10b981; font-weight: 600;">R$ <?= number_format($prod['preco_venda'] ?? 0, 2, ',', '.') ?></td>
                                        <td>
                                            <?php if(!empty($prod['data_validade'])): 
                                                $dias = (strtotime($prod['data_validade']) - time()) / (60 * 60 * 24);
                                                $color = $dias <= 7 ? 'red' : ($dias <= 30 ? 'orange' : 'var(--text-secondary)');
                                            ?>
                                                <span style="color: <?= $color ?>; font-weight: <?= $dias <= 30 ? '600' : 'normal' ?>;">
                                                    <?= date('d/m/Y', strtotime($prod['data_validade'])) ?>
                                                </span>
                                                <br><small style="color: var(--text-secondary);">Lote: <?= htmlspecialchars($prod['lote']) ?></small>
                                            <?php else: ?>
                                                <span style="color: var(--text-secondary);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if ($prod['quantidade_estoque'] <= 0) {
                                                    echo '<span class="badge badge-danger">Esgotado</span>';
                                                } elseif ($prod['quantidade_estoque'] <= $prod['estoque_minimo']) {
                                                    echo '<span class="badge badge-warning">Crítico</span>';
                                                } else {
                                                    echo '<span class="badge badge-success">Regular</span>';
                                                }
                                            ?>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($prod['atualizado_em'])) ?></td>
                                        <td>
                                            <a href="/estoque/historico/<?= $prod['id_produto'] ?>" class="btn" style="background-color: var(--bg-base); color: var(--text-primary); padding: 0.25rem 0.5rem; font-size: 0.85rem;">
                                                <i class="ph ph-clock-counter-clockwise"></i> Kardex
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal do Scanner -->
    <div id="scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:999; flex-direction:column; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); padding:1rem; border-radius:var(--border-radius); width:90%; max-width:500px;">
            <div style="display:flex; justify-content:space-between; margin-bottom:1rem;">
                <h3 style="font-size: 1.25rem; font-weight:600;">Escanear Código</h3>
                <button onclick="stopScanner()" style="background:none; border:none; cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            <div id="reader" style="width:100%;"></div>
        </div>
    </div>
    
    <!-- Modal Novo Produto -->
    <div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content glass-panel" style="background: var(--surface);">
          <div class="modal-header border-0">
            <h5 class="modal-title" style="color: var(--text-primary); font-weight: 600;">Cadastrar Novo Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="/estoque/novo" method="POST">
              <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
              <div class="modal-body">
                  <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                      <div class="input-group" style="grid-column: 1 / -1;">
                          <label>Nome do Produto *</label>
                          <input type="text" name="nome_produto" required placeholder="Ex: Arroz Tio João 5kg">
                      </div>
                      <div class="input-group">
                          <label>Código de Barras</label>
                          <input type="text" name="codigo_barras" placeholder="EAN-13 ou EAN-8">
                      </div>
                      <div class="input-group">
                          <label>Categoria (ID)</label>
                          <input type="number" name="id_categoria" placeholder="Ex: 1">
                      </div>
                      <div class="input-group">
                          <label>Preço de Custo (R$)</label>
                          <input type="number" step="0.01" name="preco_custo" placeholder="0.00">
                      </div>
                      <div class="input-group">
                          <label>Preço de Venda (R$) *</label>
                          <input type="number" step="0.01" name="preco_venda" required placeholder="0.00">
                      </div>
                      <div class="input-group">
                          <label>Estoque Mínimo</label>
                          <input type="number" name="estoque_minimo" placeholder="Ex: 10">
                      </div>
                      <div class="input-group">
                          <label>Data de Validade (Opcional)</label>
                          <input type="date" name="data_validade">
                      </div>
                  </div>
              </div>
              <div class="modal-footer border-0">
                <button type="button" class="btn" style="background-color: var(--surface-hover); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Cadastrar</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        // Filtro da tabela
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            document.querySelectorAll('.produto-row').forEach(row => {
                const id = row.querySelector('.prod-id').innerText.toLowerCase();
                const nome = row.querySelector('.prod-nome').innerText.toLowerCase();
                if (id.includes(term) || nome.includes(term)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });

        // Lógica do Scanner
        let html5QrcodeScanner = null;

        function startScanner() {
            document.getElementById('scanner-modal').style.display = 'flex';
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 250} }, false);
            html5QrcodeScanner.render(onScanSuccess, onScanFailure);
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear();
            }
            document.getElementById('scanner-modal').style.display = 'none';
        }

        function onScanSuccess(decodedText, decodedResult) {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = decodedText;
            searchInput.dispatchEvent(new Event('input'));
            stopScanner();
            
            searchInput.focus();
            searchInput.style.backgroundColor = 'var(--success-bg)';
            setTimeout(() => { searchInput.style.backgroundColor = 'var(--bg-card)'; }, 1000);
        }

        function onScanFailure(error) {
            // ignorar
        }
    </script>
    <script>
        // Data para a impressão
        const dateStr = new Date().toLocaleString('pt-BR');
        document.querySelector('.topbar h2').setAttribute('data-date', dateStr);
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
