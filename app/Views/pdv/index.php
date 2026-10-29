<?php include __DIR__ . '/../layouts/header.php'; ?>

<header class="topbar animate-fade-up">
    <h2>Frente de Caixa (PDV)</h2>
</header>

<?php if(isset($_GET['error'])): ?>
    <div class="alert alert-error animate-fade-up">
        <i class="ph ph-warning-circle"></i> <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<div class="row">
    <!-- Esquerda: Busca, Categorias e Produtos -->
    <div class="col-lg-7">
        <div class="card mb-4 glass-panel">
            <div class="card-body">
                <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem; flex-wrap: wrap;">
                    <div class="input-icon-wrapper" style="flex: 1; min-width: 220px;">
                        <i class="ph ph-barcode"></i>
                        <input type="text" id="codigoBusca" placeholder="Código de barras ou Nome do Produto..." autofocus>
                    </div>
                    <button class="btn" type="button" id="btnScanner" data-bs-toggle="modal" data-bs-target="#scannerModal" style="background-color: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-subtle);">
                        <i class="ph ph-camera" style="font-size: 1.15rem;"></i> Câmera
                    </button>
                </div>

                <?php if(!empty($categorias)): ?>
                    <div id="categoriaFiltros" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                        <button type="button" class="btn btn-primary btn-cat-filter" data-cat="" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">Todas</button>
                        <?php foreach($categorias as $cat): ?>
                            <button type="button" class="btn btn-cat-filter" data-cat="<?= (int)$cat['id_categoria'] ?>" style="padding: 0.35rem 0.75rem; font-size: 0.8rem; background-color: var(--bg-secondary); color: var(--text-primary); border: 1px solid var(--border-subtle);">
                                <?= htmlspecialchars($cat['nome_categoria']) ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="table-responsive" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-hover align-middle" id="tabelaBusca">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Estoque</th>
                                <th>Preço</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="listaProdutos"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Direita: Carrinho e Checkout -->
    <div class="col-lg-5">
        <div class="card glass-panel border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0" style="font-size: 1.05rem;"><i class="ph ph-shopping-cart"></i> Carrinho</h5>
                <span class="badge bg-light text-primary rounded-pill" id="cartCount">0 itens</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="carrinhoLista" style="max-height: 300px; overflow-y: auto;">
                    <li class="list-group-item text-center text-muted py-4">Carrinho vazio</li>
                </ul>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <span class="h5 mb-0">Total:</span>
                    <span class="h4 text-success fw-bold mb-0" id="totalCarrinho">R$ 0,00</span>
                </div>
                <div class="d-flex justify-content-between mb-3 align-items-center">
                    <label for="valorDesconto" class="form-label mb-0">Desconto (R$):</label>
                    <input type="number" id="valorDesconto" class="form-control form-control-sm text-end" style="width: 110px;" value="0.00" step="0.01" min="0">
                </div>
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="clienteSelect" class="form-label mb-0">Cliente</label>
                        <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modalNovoCliente" style="padding: 0.2rem 0.55rem; font-size: 0.75rem; background-color: var(--accent-light); color: var(--primary);">
                            <i class="ph ph-user-plus"></i> Novo Cliente
                        </button>
                    </div>
                    <select id="clienteSelect" class="form-select">
                        <?php foreach($clientes as $cli): ?>
                            <option value="<?= (int)$cli['id_cliente'] ?>" data-phone="<?= htmlspecialchars($cli['telefone'] ?? '') ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="pagamentoSelect" class="form-label">Forma de Pagamento</label>
                    <select id="pagamentoSelect" class="form-select">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Pix">Pix</option>
                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                        <option value="Cartão de Débito">Cartão de Débito</option>
                        <option value="Misto">Misto (Dinheiro + Cartão/Pix)</option>
                        <option value="Fiado (Caderninho)">Fiado (Caderninho)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" id="btnFinalizar" disabled style="background-color: var(--success); color: #fff;">
                        <i class="ph ph-check-circle"></i> Finalizar Venda
                    </button>
                    <button class="btn btn-outline-danger" id="btnLimpar">Limpar Carrinho</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Novo Cliente Rápido -->
<div class="modal fade" id="modalNovoCliente" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Cadastrar Cliente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar" id="fecharModalCliente"></button>
      </div>
      <form id="formNovoCliente" data-no-spinner="true">
          <div class="modal-body">
              <div class="input-group">
                  <label>Nome Completo *</label>
                  <input type="text" id="novoCliNome" required placeholder="Ex: Maria Oliveira">
              </div>
              <div class="input-group">
                  <label>WhatsApp / Telefone (com DDD)</label>
                  <input type="text" id="novoCliTelefone" placeholder="Ex: 11999998888">
              </div>
              <div class="input-group" style="margin-bottom:0;">
                  <label>E-mail (Opcional)</label>
                  <input type="email" id="novoCliEmail" placeholder="cliente@email.com">
              </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn" style="background-color: var(--bg-secondary); color: var(--text-primary);" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-primary" id="btnSalvarCliente">Salvar Cliente</button>
          </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Scanner -->
<div class="modal fade" id="scannerModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Escanear Código de Barras</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" id="closeScanner"></button>
      </div>
      <div class="modal-body text-center">
        <div id="reader" style="width: 100%;"></div>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    const produtosRaw = <?= $produtos_json ?>;
    let carrinho = [];
    let categoriaSelecionada = '';

    const formatCurrency = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

    const escapeHtml = (str) => {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    };

    const formatWhatsAppPhone = (raw) => {
        const digits = (raw || '').replace(/\D/g, '');
        if (!digits) return '';
        return (digits.length === 10 || digits.length === 11) ? '55' + digits : digits;
    };

    const getTotalLiquido = () => {
        const bruto = carrinho.reduce((acc, item) => acc + (item.preco * item.quantidade), 0);
        const desconto = Math.max(0, parseFloat(document.getElementById('valorDesconto').value) || 0);
        return Math.max(0, Math.round((bruto - desconto) * 100) / 100);
    };

    // Filtro de Busca + Categoria (Protegido contra XSS)
    const renderBusca = (termo = '') => {
        const tbody = document.getElementById('listaProdutos');
        tbody.innerHTML = '';
        const termoLower = termo.toLowerCase();
        
        const filtrados = produtosRaw.filter(p => {
            const matchCat = !categoriaSelecionada || String(p.id_categoria || '') === String(categoriaSelecionada);
            const matchTermo = p.nome_produto.toLowerCase().includes(termoLower) ||
                (p.codigo_barras && String(p.codigo_barras).toLowerCase().includes(termoLower));
            return matchCat && matchTermo;
        }).slice(0, 15);

        if (filtrados.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">Nenhum produto disponível encontrado.</td></tr>';
            return;
        }

        filtrados.forEach(p => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${escapeHtml(p.nome_produto)} <br><small class="text-muted">${escapeHtml(p.codigo_barras || 'S/N')}</small></td>
                <td>${Number(p.quantidade_estoque)}</td>
                <td class="text-success fw-semibold">${formatCurrency(p.preco_venda)}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary" data-add-id="${Number(p.id_produto)}">
                        <i class="ph ph-plus"></i> Add
                    </button>
                </td>
            `;
            tr.querySelector('button').addEventListener('click', () => adicionarAoCarrinho(p.id_produto));
            tbody.appendChild(tr);
        });
    };

    document.querySelectorAll('.btn-cat-filter').forEach(btn => {
        btn.addEventListener('click', () => {
            categoriaSelecionada = btn.getAttribute('data-cat') || '';
            document.querySelectorAll('.btn-cat-filter').forEach(b => {
                b.classList.remove('btn-primary');
                b.style.backgroundColor = 'var(--bg-secondary)';
                b.style.color = 'var(--text-primary)';
            });
            btn.classList.add('btn-primary');
            btn.style.backgroundColor = '';
            btn.style.color = '';
            renderBusca(document.getElementById('codigoBusca').value);
        });
    });

    document.getElementById('codigoBusca').addEventListener('input', (e) => {
        renderBusca(e.target.value);
    });

    document.getElementById('codigoBusca').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const termo = e.target.value.trim();
            if (!termo) return;
            
            let buscaReal = termo;
            let quantidadePonderada = 1;

            if (termo.length === 13 && termo.startsWith('2')) {
                const codigoBalanca = termo.substring(1, 6);
                const valorEtiqueta = parseInt(termo.substring(6, 12), 10) / 100;
                
                let prodBalanca = produtosRaw.find(p => p.codigo_barras == codigoBalanca || p.id_produto == parseInt(codigoBalanca, 10));
                if (prodBalanca && parseFloat(prodBalanca.preco_venda) > 0) {
                    buscaReal = prodBalanca.codigo_barras;
                    quantidadePonderada = Math.round((valorEtiqueta / parseFloat(prodBalanca.preco_venda)) * 1000) / 1000;
                }
            }

            let prod = produtosRaw.find(p => p.codigo_barras === buscaReal);
            if (!prod) {
                const filtrados = produtosRaw.filter(p => p.nome_produto.toLowerCase().includes(buscaReal.toLowerCase()));
                if (filtrados.length === 1) prod = filtrados[0];
            }

            if (prod) {
                adicionarAoCarrinho(prod.id_produto, quantidadePonderada);
                e.target.select();
                renderBusca();
            } else {
                Swal.fire('Não Encontrado', 'Nenhum produto exato encontrado para este código.', 'warning');
                e.target.select();
            }
        }
    });

    document.addEventListener('keydown', (e) => {
        const active = document.activeElement.tagName;
        const modalAberto = document.querySelector('.modal.show') || document.querySelector('.swal2-shown');
        if (!modalAberto && active !== 'INPUT' && active !== 'TEXTAREA' && active !== 'SELECT') {
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                document.getElementById('codigoBusca').focus();
            }
        }
    });

    const adicionarAoCarrinho = (id, qtd = 1) => {
        const prod = produtosRaw.find(p => p.id_produto == id);
        if (!prod) return;
        const itemCarrinho = carrinho.find(i => i.id == id);
        const estoqueMax = parseFloat(prod.quantidade_estoque);
        
        if (itemCarrinho) {
            const novaQtd = Math.round((itemCarrinho.quantidade + qtd) * 1000) / 1000;
            if (novaQtd <= estoqueMax) {
                itemCarrinho.quantidade = novaQtd;
            } else {
                Swal.fire('Atenção', 'Estoque insuficiente para esta quantidade!', 'warning');
            }
        } else {
            if (qtd <= estoqueMax) {
                carrinho.push({ id: prod.id_produto, nome: prod.nome_produto, preco: parseFloat(prod.preco_venda), quantidade: qtd });
            } else {
                Swal.fire('Atenção', 'Estoque insuficiente para esta quantidade!', 'warning');
            }
        }
        atualizarCarrinho();
    };

    const removerDoCarrinho = (id) => {
        carrinho = carrinho.filter(i => i.id != id);
        atualizarCarrinho();
    };

    const atualizarCarrinho = () => {
        const lista = document.getElementById('carrinhoLista');
        const btnFinalizar = document.getElementById('btnFinalizar');
        
        if (carrinho.length === 0) {
            lista.innerHTML = '<li class="list-group-item text-center text-muted py-4">Carrinho vazio</li>';
            document.getElementById('totalCarrinho').innerText = 'R$ 0,00';
            document.getElementById('cartCount').innerText = '0 itens';
            btnFinalizar.disabled = true;
            return;
        }

        btnFinalizar.disabled = false;
        lista.innerHTML = '';

        carrinho.forEach(item => {
            const subtotal = item.preco * item.quantidade;
            const qtyStr = item.quantidade % 1 !== 0 ? item.quantidade.toFixed(3) : item.quantidade;

            const li = document.createElement('li');
            li.className = 'list-group-item d-flex justify-content-between align-items-center';
            li.innerHTML = `
                <div style="flex:1;">
                    <strong style="color: var(--text-primary); font-size: 0.95rem;">${escapeHtml(item.nome)}</strong><br>
                    <small style="color: var(--text-secondary);">${qtyStr} x ${formatCurrency(item.preco)}</small>
                </div>
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">${formatCurrency(subtotal)}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger"><i class="ph ph-trash"></i></button>
                </div>
            `;
            li.querySelector('button').addEventListener('click', () => removerDoCarrinho(item.id));
            lista.appendChild(li);
        });

        document.getElementById('totalCarrinho').innerText = formatCurrency(getTotalLiquido());
        document.getElementById('cartCount').innerText = carrinho.length + (carrinho.length === 1 ? ' item' : ' itens');
    };

    document.getElementById('valorDesconto').addEventListener('input', atualizarCarrinho);

    document.getElementById('btnLimpar').addEventListener('click', () => {
        carrinho = [];
        atualizarCarrinho();
    });

    // Cadastro Rápido de Cliente no PDV (sem perder o carrinho)
    document.getElementById('formNovoCliente').addEventListener('submit', async (e) => {
        e.preventDefault();
        const nome = document.getElementById('novoCliNome').value.trim();
        const telefone = document.getElementById('novoCliTelefone').value.trim();
        const email = document.getElementById('novoCliEmail').value.trim();
        if (!nome) return;

        const btn = document.getElementById('btnSalvarCliente');
        btn.disabled = true;
        try {
            const res = await fetch('/clientes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({ nome, telefone, email })
            });
            const data = await res.json();
            if (data.success && data.cliente) {
                const select = document.getElementById('clienteSelect');
                const opt = document.createElement('option');
                opt.value = data.cliente.id_cliente;
                opt.setAttribute('data-phone', data.cliente.telefone || '');
                opt.textContent = data.cliente.nome;
                select.appendChild(opt);
                select.value = String(data.cliente.id_cliente);

                document.getElementById('formNovoCliente').reset();
                document.getElementById('fecharModalCliente').click();
                Swal.fire('Sucesso', 'Cliente cadastrado e selecionado!', 'success');
            } else {
                Swal.fire('Atenção', data.message || 'Erro ao cadastrar cliente.', 'warning');
            }
        } catch (err) {
            Swal.fire('Erro', 'Falha de comunicação ao salvar cliente.', 'error');
        } finally {
            btn.disabled = false;
        }
    });

    // Finalizar Venda
    document.getElementById('btnFinalizar').addEventListener('click', async () => {
        const clienteSelect = document.getElementById('clienteSelect');
        const id_cliente = parseInt(clienteSelect.value, 10);
        const telefone = clienteSelect.options[clienteSelect.selectedIndex]?.getAttribute('data-phone') || '';
        const metodo_pagamento = document.getElementById('pagamentoSelect').value;
        const totalCalculado = getTotalLiquido();
        
        if (metodo_pagamento === 'Fiado (Caderninho)' && id_cliente <= 1) {
            Swal.fire('Cliente Obrigatório', 'Para vender no Fiado, selecione um cliente cadastrado ou clique em "+ Novo Cliente".', 'warning');
            return;
        }

        let trocoMessage = '';
        let metodo_pagamento_final = metodo_pagamento;
        let valor_dinheiro = null;

        if (metodo_pagamento === 'Dinheiro') {
            const { value: valorRec } = await Swal.fire({
                title: 'Pagamento em Dinheiro',
                html: `<p style="margin-bottom:0.5rem;">Total da Venda: <strong>${formatCurrency(totalCalculado)}</strong></p>
                       <input id="swalValorRecebido" type="number" step="0.01" min="${totalCalculado.toFixed(2)}" class="swal2-input" value="${totalCalculado.toFixed(2)}" placeholder="Valor recebido (R$)">`,
                showCancelButton: true,
                confirmButtonText: 'Confirmar Recebimento',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const v = parseFloat(document.getElementById('swalValorRecebido').value || '0');
                    if (isNaN(v) || v < totalCalculado) {
                        Swal.showValidationMessage('O valor recebido não pode ser menor que o total.');
                        return false;
                    }
                    return v;
                }
            });
            if (valorRec === undefined) return;
            const troco = Math.max(0, valorRec - totalCalculado);
            trocoMessage = `<br><br><strong>Troco:</strong> ${formatCurrency(troco)}`;
        } else if (metodo_pagamento === 'Misto') {
            const { value: formValues } = await Swal.fire({
                title: 'Pagamento Misto',
                html: `
                    <p style="margin-bottom:0.75rem;">Total da Compra: <strong>${formatCurrency(totalCalculado)}</strong></p>
                    <div style="text-align: left;">
                        <label>Valor em Dinheiro (R$):</label>
                        <input id="swal-input1" class="swal2-input" type="number" step="0.01" min="0" placeholder="0.00">
                        <label>Valor Cartão/Pix (R$):</label>
                        <input id="swal-input2" class="swal2-input" type="number" step="0.01" min="0" placeholder="0.00">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirmar Pagamento',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const valDin = parseFloat(document.getElementById('swal-input1').value || '0');
                    const valOutro = parseFloat(document.getElementById('swal-input2').value || '0');
                    if (valDin < 0 || valOutro < 0 || (valDin + valOutro) < totalCalculado) {
                        Swal.showValidationMessage('A soma dos pagamentos é menor que o total da compra.');
                        return false;
                    }
                    return [valDin, valOutro];
                }
            });

            if (!formValues) return;
            const [valDin, valOutro] = formValues;
            const troco = Math.max(0, (valDin + valOutro) - totalCalculado);
            if (troco > 0) {
                trocoMessage = `<br><br><strong>Troco:</strong> ${formatCurrency(troco)}`;
            }
            valor_dinheiro = Math.max(0, valDin - troco);
            metodo_pagamento_final = `Misto: Dinheiro (${formatCurrency(valor_dinheiro)}) + Cartão/Pix (${formatCurrency(valOutro)})`;
        } else if (metodo_pagamento === 'Pix') {
            const valorDot = totalCalculado.toFixed(2);
            const lenCampo = String(valorDot.length).padStart(2, '0');
            const pixData = `00020126330014BR.GOV.BCB.PIX01111234567890052040000530398654${lenCampo}${valorDot}5802BR5913STOCKFLOW PDV6009SAO PAULO62070503***6304`;
            const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(pixData)}`;
            
            const result = await Swal.fire({
                title: 'Pagamento via PIX',
                html: `
                    <p>Aguardando pagamento de <strong>${formatCurrency(totalCalculado)}</strong>...</p>
                    <img src="${qrUrl}" alt="QR Code Pix" style="margin: 15px auto; border-radius: 10px; border: 2px solid #ddd; padding: 10px; background: #fff;">
                    <p class="text-muted" style="font-size: 0.85em;">Confira o recebimento no aplicativo do banco antes de confirmar.</p>
                `,
                showCancelButton: true,
                confirmButtonText: 'Confirmar Recebimento',
                confirmButtonColor: '#059669',
                cancelButtonText: 'Cancelar'
            });
            
            if (!result.isConfirmed) return;
        }
        
        const btnFinalizar = document.getElementById('btnFinalizar');
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';

        try {
            const descontoFinal = Math.max(0, parseFloat(document.getElementById('valorDesconto').value) || 0);
            const res = await fetch('/pdv/finalizar', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    id_cliente,
                    metodo_pagamento: metodo_pagamento_final,
                    valor_dinheiro,
                    carrinho,
                    desconto: descontoFinal
                })
            });
            const data = await res.json();
            
            if (data.success) {
                // Atualiza saldo local dos produtos em memória
                carrinho.forEach(item => {
                    const p = produtosRaw.find(pr => pr.id_produto == item.id);
                    if (p) p.quantidade_estoque = Math.max(0, Math.round((parseFloat(p.quantidade_estoque) - item.quantidade) * 1000) / 1000);
                });

                let msg = `*COMPROVANTE DE VENDA #${data.id_venda}*\n\n`;
                carrinho.forEach(i => {
                    msg += `${i.quantidade}x ${i.nome} - ${formatCurrency(i.preco)}\n`;
                });
                msg += `\n*Total: ${formatCurrency(data.total)}*\n*Pagamento:* ${metodo_pagamento_final}\n\nObrigado pela preferência!`;
                
                const foneWa = formatWhatsAppPhone(telefone);
                let swalConfig = {
                    title: 'Venda Finalizada!',
                    html: `A venda #${data.id_venda} foi concluída com sucesso.${trocoMessage}`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Nova Venda',
                    cancelButtonText: 'Imprimir Cupom',
                    cancelButtonColor: '#2563eb',
                    confirmButtonColor: '#059669'
                };
                
                if (foneWa.length >= 10) {
                    swalConfig.showDenyButton = true;
                    swalConfig.denyButtonText = 'Enviar no WhatsApp';
                    swalConfig.denyButtonColor = '#25D366';
                }
                
                Swal.fire(swalConfig).then((result) => {
                    if (result.isDenied) {
                        window.open(`https://wa.me/${foneWa}?text=${encodeURIComponent(msg)}`, '_blank');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open('/pdv/recibo/' + data.id_venda, '_blank', 'width=420,height=650');
                    }
                    
                    carrinho = [];
                    document.getElementById('valorDesconto').value = '0.00';
                    atualizarCarrinho();
                    renderBusca(document.getElementById('codigoBusca').value);
                    btnFinalizar.innerHTML = '<i class="ph ph-check-circle"></i> Finalizar Venda';
                    document.getElementById('codigoBusca').focus();
                });
            } else {
                Swal.fire('Atenção', data.message || 'Erro ao finalizar venda.', 'error');
                btnFinalizar.disabled = false;
                btnFinalizar.innerHTML = '<i class="ph ph-check-circle"></i> Finalizar Venda';
            }
        } catch (e) {
            Swal.fire('Erro', 'Erro de conexão com o servidor.', 'error');
            btnFinalizar.disabled = false;
            btnFinalizar.innerHTML = '<i class="ph ph-check-circle"></i> Finalizar Venda';
        }
    });

    // Scanner de Câmera
    let html5QrcodeScanner = null;

    document.getElementById('scannerModal').addEventListener('shown.bs.modal', function () {
        if (!html5QrcodeScanner) {
            html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: { width: 250, height: 250 } }, false);
            html5QrcodeScanner.render((decodedText) => {
                document.getElementById('codigoBusca').value = decodedText;
                document.getElementById('closeScanner').click();
                document.getElementById('codigoBusca').dispatchEvent(new KeyboardEvent('keydown', { key: 'Enter', code: 'Enter' }));
                html5QrcodeScanner.clear().catch(() => {});
                html5QrcodeScanner = null;
            }, () => {});
        }
    });

    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function () {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear().catch(() => {});
            html5QrcodeScanner = null;
        }
    });

    renderBusca();
</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
