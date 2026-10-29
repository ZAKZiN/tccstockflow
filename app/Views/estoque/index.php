<?php include __DIR__ . '/../layouts/header.php'; ?>

            <header class="topbar animate-fade-up">
                <h2>Controle de Estoque</h2>
                <div class="topbar-actions">
                    <a href="?export=csv" class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);"><i class="ph ph-file-csv"></i> Exportar CSV</a>
                    <button class="btn" style="background-color: var(--bg-card); color: var(--text-primary); border: 1px solid var(--border-subtle);" onclick="window.print()"><i class="ph ph-printer"></i> Imprimir</button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoProduto"><i class="ph ph-plus"></i> Cadastrar Produto</button>
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
                
                <div style="display: flex; gap: 0.75rem; margin-bottom: 1.5rem; max-width: 520px; flex-wrap: wrap;">
                    <div class="input-icon-wrapper" style="flex:1; min-width: 240px;">
                        <i class="ph ph-magnifying-glass"></i>
                        <input type="text" id="searchInput" placeholder="Buscar por Nome, ID, Categoria ou Código de barras...">
                    </div>
                    <button class="btn" style="background-color: var(--accent-light); color: var(--primary);" onclick="startScanner()"><i class="ph ph-barcode"></i> Escanear</button>
                </div>

                <div class="table-container">
                    <table id="estoqueTable">
                        <thead>
                            <tr>
                                <th>Cód.</th>
                                <th>Produto / Cód. Barras</th>
                                <th>Qtd Atual</th>
                                <th>Preço Venda</th>
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
                                        <td class="prod-id">#<?= str_pad((string)$prod['id_produto'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="prod-nome" style="font-weight: 500; color: var(--text-primary);">
                                            <?= htmlspecialchars($prod['nome_produto']) ?>
                                            <br>
                                            <small style="color: var(--text-secondary); font-weight: normal;">
                                                <?= htmlspecialchars($prod['codigo_barras'] ?? 'S/N') ?>
                                                <?= !empty($prod['nome_categoria']) ? ' • ' . htmlspecialchars($prod['nome_categoria']) : '' ?>
                                            </small>
                                        </td>
                                        <td style="font-size: 1.05rem; font-weight: 600;">
                                            <?= rtrim(rtrim(number_format((float)$prod['quantidade_estoque'], 3, ',', '.'), '0'), ',') ?>
                                        </td>
                                        <td style="color: var(--success); font-weight: 600;">R$ <?= number_format((float)($prod['preco_venda'] ?? 0), 2, ',', '.') ?></td>
                                        <td>
                                            <?php if(!empty($prod['data_validade'])): 
                                                $dias = (strtotime($prod['data_validade']) - strtotime(date('Y-m-d'))) / 86400;
                                                $color = $dias <= 7 ? 'var(--danger)' : ($dias <= 30 ? 'var(--warning)' : 'var(--text-secondary)');
                                            ?>
                                                <span style="color: <?= $color ?>; font-weight: <?= $dias <= 30 ? '600' : 'normal' ?>;">
                                                    <?= date('d/m/Y', strtotime($prod['data_validade'])) ?>
                                                </span>
                                                <?php if(!empty($prod['lote'])): ?>
                                                    <br><small style="color: var(--text-secondary);">Lote: <?= htmlspecialchars($prod['lote']) ?></small>
                                                <?php endif; ?>
                                            <?php elseif(!empty($prod['lote'])): ?>
                                                <small style="color: var(--text-secondary);">Lote: <?= htmlspecialchars($prod['lote']) ?></small>
                                            <?php else: ?>
                                                <span style="color: var(--text-secondary);">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php 
                                                if ((float)$prod['quantidade_estoque'] <= 0) {
                                                    echo '<span class="badge badge-danger">Esgotado</span>';
                                                } elseif ((float)$prod['quantidade_estoque'] <= (float)$prod['estoque_minimo']) {
                                                    echo '<span class="badge badge-warning">Crítico</span>';
                                                } else {
                                                    echo '<span class="badge badge-success">Regular</span>';
                                                }
                                            ?>
                                        </td>
                                        <td style="color: var(--text-secondary);"><?= date('d/m/Y H:i', strtotime($prod['atualizado_em'])) ?></td>
                                        <td>
                                            <div style="display: flex; gap: 0.4rem;">
                                                <a href="/estoque/historico/<?= (int)$prod['id_produto'] ?>" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary); padding: 0.25rem 0.55rem; font-size: 0.8rem;" title="Movimentações Kardex">
                                                    <i class="ph ph-clock-counter-clockwise"></i> Kardex
                                                </a>
                                                <button type="button" class="btn btn-editar-prod"
                                                    data-prod='<?= htmlspecialchars(json_encode($prod, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES) ?>'
                                                    style="background-color: var(--accent-light); color: var(--primary); padding: 0.25rem 0.55rem; font-size: 0.8rem;" title="Editar Produto">
                                                    <i class="ph ph-pencil-simple"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <tr id="noResultsRow" style="display: none;">
                                <td colspan="8" style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                                    <i class="ph ph-magnifying-glass" style="font-size: 2rem; opacity: 0.5; margin-bottom: 0.5rem; display: block;"></i>
                                    Nenhum produto encontrado na pesquisa.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

    <!-- Modal do Scanner -->
    <div id="scanner-modal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); z-index:1060; flex-direction:column; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); color:var(--text-primary); padding:1.25rem; border-radius:var(--border-radius); width:90%; max-width:500px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1rem;">
                <h3 style="font-size: 1.15rem; font-weight:600; margin:0;">Escanear Código</h3>
                <button onclick="stopScanner()" style="background:none; border:none; color:var(--text-primary); cursor:pointer; font-size:1.5rem;"><i class="ph ph-x"></i></button>
            </div>
            <div id="reader" style="width:100%;"></div>
        </div>
    </div>
    
    <!-- Modal Novo Produto -->
    <div class="modal fade" id="modalNovoProduto" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Cadastrar Novo Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="/estoque/novo" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <div class="modal-body">
                  <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                      <div class="input-group" style="grid-column: 1 / -1;">
                          <label>Nome do Produto *</label>
                          <input type="text" name="nome_produto" required placeholder="Ex: Arroz Tipo 1 5kg">
                      </div>
                      <div class="input-group">
                          <label>Categoria</label>
                          <select name="id_categoria">
                              <option value="">Sem categoria</option>
                              <?php foreach($categorias as $cat): ?>
                                  <option value="<?= (int)$cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="input-group">
                          <label>Código de Barras (Opcional — gera automático)</label>
                          <input type="text" name="codigo_barras" placeholder="EAN-13 ou código interno">
                      </div>
                      <div class="input-group">
                          <label>Preço de Custo (R$)</label>
                          <input type="number" step="0.01" min="0" name="preco_custo" placeholder="0.00">
                      </div>
                      <div class="input-group">
                          <label>Preço de Venda (R$) *</label>
                          <input type="number" step="0.01" min="0.01" name="preco_venda" required placeholder="0.00">
                      </div>
                      <div class="input-group">
                          <label>Estoque Mínimo</label>
                          <input type="number" step="0.001" min="0" name="estoque_minimo" value="5">
                      </div>
                      <div class="input-group">
                          <label>Saldo Inicial</label>
                          <input type="number" step="0.001" min="0" name="quantidade_estoque" value="0">
                      </div>
                      <div class="input-group">
                          <label>Lote (Opcional)</label>
                          <input type="text" name="lote" placeholder="Ex: LT-2026A">
                      </div>
                      <div class="input-group">
                          <label>Data de Validade (Opcional)</label>
                          <input type="date" name="data_validade">
                      </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Cadastrar Produto</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Editar Produto -->
    <div class="modal fade" id="modalEditarProduto" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" style="font-weight: 600;">Editar Produto</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="/estoque/editar" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="id_produto" id="editIdProduto">
              <div class="modal-body">
                  <div class="form-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
                      <div class="input-group" style="grid-column: 1 / -1;">
                          <label>Nome do Produto *</label>
                          <input type="text" name="nome_produto" id="editNomeProduto" required>
                      </div>
                      <div class="input-group">
                          <label>Categoria</label>
                          <select name="id_categoria" id="editIdCategoria">
                              <option value="">Sem categoria</option>
                              <?php foreach($categorias as $cat): ?>
                                  <option value="<?= (int)$cat['id_categoria'] ?>"><?= htmlspecialchars($cat['nome_categoria']) ?></option>
                              <?php endforeach; ?>
                          </select>
                      </div>
                      <div class="input-group">
                          <label>Código de Barras *</label>
                          <input type="text" name="codigo_barras" id="editCodigoBarras" required>
                      </div>
                      <div class="input-group">
                          <label>Preço de Custo (R$)</label>
                          <input type="number" step="0.01" min="0" name="preco_custo" id="editPrecoCusto">
                      </div>
                      <div class="input-group">
                          <label>Preço de Venda (R$) *</label>
                          <input type="number" step="0.01" min="0.01" name="preco_venda" id="editPrecoVenda" required>
                      </div>
                      <div class="input-group">
                          <label>Estoque Mínimo</label>
                          <input type="number" step="0.001" min="0" name="estoque_minimo" id="editEstoqueMinimo">
                      </div>
                      <div class="input-group">
                          <label>Lote</label>
                          <input type="text" name="lote" id="editLote">
                      </div>
                      <div class="input-group">
                          <label>Data de Validade</label>
                          <input type="date" name="data_validade" id="editDataValidade">
                      </div>
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

    <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
    <script>
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('.produto-row');
            if (rows.length === 0) return;

            let hasVisible = false;
            rows.forEach(row => {
                const id = row.querySelector('.prod-id').innerText.toLowerCase();
                const nome = row.querySelector('.prod-nome').innerText.toLowerCase();
                if (id.includes(term) || nome.includes(term)) {
                    row.style.display = '';
                    hasVisible = true;
                } else {
                    row.style.display = 'none';
                }
            });
            
            const noResults = document.getElementById('noResultsRow');
            if (noResults) {
                noResults.style.display = hasVisible ? 'none' : '';
            }
        });

        document.querySelectorAll('.btn-editar-prod').forEach(btn => {
            btn.addEventListener('click', () => {
                const p = JSON.parse(btn.getAttribute('data-prod'));
                document.getElementById('editIdProduto').value = p.id_produto;
                document.getElementById('editNomeProduto').value = p.nome_produto || '';
                document.getElementById('editIdCategoria').value = p.id_categoria || '';
                document.getElementById('editCodigoBarras').value = p.codigo_barras || '';
                document.getElementById('editPrecoCusto').value = p.preco_custo ?? 0;
                document.getElementById('editPrecoVenda').value = p.preco_venda ?? 0;
                document.getElementById('editEstoqueMinimo').value = p.estoque_minimo ?? 5;
                document.getElementById('editLote').value = p.lote || '';
                document.getElementById('editDataValidade').value = p.data_validade || '';
                new bootstrap.Modal(document.getElementById('modalEditarProduto')).show();
            });
        });

        let html5QrcodeScanner = null;

        function startScanner() {
            document.getElementById('scanner-modal').style.display = 'flex';
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
            html5QrcodeScanner.render(onScanSuccess, () => {});
        }

        function stopScanner() {
            if (html5QrcodeScanner) {
                html5QrcodeScanner.clear().catch(() => {});
                html5QrcodeScanner = null;
            }
            document.getElementById('scanner-modal').style.display = 'none';
        }

        function onScanSuccess(decodedText) {
            const searchInput = document.getElementById('searchInput');
            searchInput.value = decodedText;
            searchInput.dispatchEvent(new Event('input'));
            stopScanner();
            searchInput.focus();
        }

        document.querySelector('.topbar h2').setAttribute('data-date', new Date().toLocaleString('pt-BR'));
    </script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
