<?php include __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Frente de Caixa (PDV)</h1>
</div>

<div class="row">
    <!-- Esquerda: Busca e Produtos -->
    <div class="col-md-7">
        <div class="card mb-4 shadow-sm">
            <div class="card-body">
                <div class="input-group mb-3">
                    <span class="input-group-text"><i class="bi bi-upc-scan"></i></span>
                    <input type="text" id="codigoBusca" class="form-control form-control-lg" placeholder="Código de barras ou Nome do Produto..." autofocus>
                    <button class="btn btn-outline-secondary" type="button" id="btnScanner" data-bs-toggle="modal" data-bs-target="#scannerModal">
                        <i class="bi bi-camera"></i> Câmera
                    </button>
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-hover" id="tabelaBusca">
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Estoque</th>
                                <th>Preço</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody id="listaProdutos">
                            <!-- Preenchido via JS -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Direita: Carrinho e Checkout -->
    <div class="col-md-5">
        <div class="card shadow-sm border-primary">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-cart3"></i> Carrinho</h5>
                <span class="badge bg-light text-primary rounded-pill" id="cartCount">0 itens</span>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush" id="carrinhoLista" style="max-height: 300px; overflow-y: auto;">
                    <li class="list-group-item text-center text-muted py-4">Carrinho vazio</li>
                </ul>
            </div>
            <div class="card-footer bg-light">
                <div class="d-flex justify-content-between mb-3">
                    <span class="h5">Total:</span>
                    <span class="h4 text-success fw-bold" id="totalCarrinho">R$ 0,00</span>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Cliente</label>
                    <select id="clienteSelect" class="form-select">
                        <?php foreach($clientes as $cli): ?>
                            <option value="<?= $cli['id_cliente'] ?>" data-phone="<?= $cli['telefone'] ?>"><?= $cli['nome'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Forma de Pagamento</label>
                    <select id="pagamentoSelect" class="form-select">
                        <option value="Dinheiro">Dinheiro</option>
                        <option value="Pix">Pix</option>
                        <option value="Cartão de Crédito">Cartão de Crédito</option>
                        <option value="Cartão de Débito">Cartão de Débito</option>
                        <option value="Fiado (Caderninho)">Fiado (Caderninho)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button class="btn btn-success btn-lg" id="btnFinalizar" disabled>
                        <i class="bi bi-check-circle"></i> Finalizar Venda
                    </button>
                    <button class="btn btn-outline-danger" id="btnLimpar">Limpar Carrinho</button>
                </div>
            </div>
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
        <div id="reader" width="100%"></div>
      </div>
    </div>
  </div>
</div>

<!-- Script do Barcode Scanner -->
<script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>

<script>
    const produtosRaw = <?= $produtos_json ?>;
    let carrinho = [];

    const formatCurrency = (val) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(val);

    // Filtro de Busca
    const renderBusca = (termo = '') => {
        const tbody = document.getElementById('listaProdutos');
        tbody.innerHTML = '';
        
        const filtrados = produtosRaw.filter(p => 
            p.nome_produto.toLowerCase().includes(termo.toLowerCase()) || 
            (p.codigo_barras && p.codigo_barras.includes(termo))
        ).slice(0, 10); // Mostra no máx 10

        filtrados.forEach(p => {
            tbody.innerHTML += `
                <tr>
                    <td>${p.nome_produto} <br><small class="text-muted">${p.codigo_barras || 'S/N'}</small></td>
                    <td>${p.quantidade_estoque}</td>
                    <td class="text-success">${formatCurrency(p.preco_venda)}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="adicionarAoCarrinho(${p.id_produto})">
                            <i class="bi bi-plus"></i> Add
                        </button>
                    </td>
                </tr>
            `;
        });
    };

    document.getElementById('codigoBusca').addEventListener('input', (e) => {
        const termo = e.target.value;
        renderBusca(termo);
    });

    document.getElementById('codigoBusca').addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const termo = e.target.value.trim();
            if (!termo) return;
            
            let buscaReal = termo;
            let quantidadePonderada = 1;

            // Se for EAN-13 de Balança (começa com 2 e tem 13 digitos)
            // Formato comum: 2 CCCCC PPPPPP D
            if (termo.length === 13 && termo.startsWith('2')) {
                const codigoBalanca = termo.substring(1, 6);
                const valorEtiqueta = parseInt(termo.substring(6, 12), 10) / 100;
                
                // Encontra produto cujo ID ou código de barras bate com o código da balança
                let prodBalanca = produtosRaw.find(p => p.codigo_barras == codigoBalanca || p.id_produto == codigoBalanca);
                if (prodBalanca) {
                    buscaReal = prodBalanca.codigo_barras;
                    quantidadePonderada = valorEtiqueta / parseFloat(prodBalanca.preco_venda);
                }
            }

            // Procura exato por código de barras primeiro
            let prod = produtosRaw.find(p => p.codigo_barras === buscaReal);
            
            // Se não achar por código, mas tiver só 1 resultado na busca por nome, pega ele
            if (!prod) {
                const filtrados = produtosRaw.filter(p => p.nome_produto.toLowerCase().includes(buscaReal.toLowerCase()));
                if (filtrados.length === 1) prod = filtrados[0];
            }

            if (prod) {
                adicionarAoCarrinho(prod.id_produto, quantidadePonderada);
                // Em vez de limpar, seleciona o texto. Assim o usuário vê o que digitou, e o próximo scan sobrescreve!
                e.target.select();
                renderBusca(); // volta a mostrar a lista normal ou filtrada
            } else {
                Swal.fire('Não Encontrado', 'Nenhum produto exato encontrado para este código.', 'warning');
                e.target.select();
            }
        }
    });

    // Foco Global: Se o usuário começar a digitar/bipar o leitor fora do input, foca automaticamente
    document.addEventListener('keydown', (e) => {
        const active = document.activeElement.tagName;
        if (active !== 'INPUT' && active !== 'TEXTAREA' && active !== 'SELECT') {
            // Se for uma tecla visível (letra/número)
            if (e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                document.getElementById('codigoBusca').focus();
            }
        }
    });

    // Lógica do Carrinho
    const adicionarAoCarrinho = (id, qtd = 1) => {
        const prod = produtosRaw.find(p => p.id_produto == id);
        const itemCarrinho = carrinho.find(i => i.id == id);
        
        if (itemCarrinho) {
            if ((itemCarrinho.quantidade + qtd) <= prod.quantidade_estoque) {
                itemCarrinho.quantidade += qtd;
            } else {
                Swal.fire('Atenção', 'Estoque insuficiente para esta quantidade!', 'warning');
            }
        } else {
            if (qtd <= prod.quantidade_estoque) {
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
        let total = 0;
        let count = 0;

        carrinho.forEach(item => {
            const subtotal = item.preco * item.quantidade;
            total += subtotal;
            count += 1;
            
            // Formatando quantidade para mostrar casas decimais caso seja peso (ex: 0.250)
            const qtyStr = item.quantidade % 1 !== 0 ? item.quantidade.toFixed(3) : item.quantidade;

            lista.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-center animate-fade-up">
                    <div style="flex:1;">
                        <strong style="color: var(--text-primary); font-size: 1rem;">${item.nome}</strong><br>
                        <small style="color: var(--text-secondary);">${qtyStr} x R$ ${item.preco.toFixed(2)}</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="fw-bold me-3">${formatCurrency(subtotal)}</span>
                        <button class="btn btn-sm btn-outline-danger" onclick="removerDoCarrinho(${item.id})"><i class="bi bi-trash"></i></button>
                    </div>
                </li>
            `;
        });

        document.getElementById('totalCarrinho').innerText = formatCurrency(total);
        document.getElementById('cartCount').innerText = count + ' itens';
    };

    document.getElementById('btnLimpar').addEventListener('click', () => {
        carrinho = [];
        atualizarCarrinho();
    });

    // Scanner
    let html5QrcodeScanner;
    document.getElementById('scannerModal').addEventListener('shown.bs.modal', function () {
        html5QrcodeScanner = new Html5QrcodeScanner("reader", { fps: 10, qrbox: {width: 250, height: 100} }, false);
        html5QrcodeScanner.render((decodedText) => {
            document.getElementById('codigoBusca').value = decodedText;
            document.getElementById('closeScanner').click();
            document.getElementById('codigoBusca').dispatchEvent(new Event('input'));
        });
    });

    document.getElementById('scannerModal').addEventListener('hidden.bs.modal', function () {
        if (html5QrcodeScanner) {
            html5QrcodeScanner.clear();
        }
    });

    // Finalizar Venda
    document.getElementById('btnFinalizar').addEventListener('click', async () => {
        const clienteSelect = document.getElementById('clienteSelect');
        const id_cliente = clienteSelect.value;
        const telefone = clienteSelect.options[clienteSelect.selectedIndex].getAttribute('data-phone');
        const metodo_pagamento = document.getElementById('pagamentoSelect').value;
        
        let trocoMessage = '';
        if (metodo_pagamento === 'Dinheiro') {
            const valorRecebidoStr = prompt('Qual o valor recebido em dinheiro? (Ex: 50.00)');
            if (valorRecebidoStr) {
                const recebido = parseFloat(valorRecebidoStr.replace(',','.'));
                const totalCalculado = parseFloat(document.getElementById('totalCarrinho').innerText.replace('R$','').replace('.','').replace(',','.'));
                if (recebido < totalCalculado) {
                    Swal.fire('Atenção', 'Valor recebido é menor que o total.', 'warning');
                    return;
                }
                const troco = recebido - totalCalculado;
                trocoMessage = `<br><br><strong>Troco:</strong> ${formatCurrency(troco)}`;
            } else {
                return; // Cancelou o prompt
            }
        }
        
        const btnFinalizar = document.getElementById('btnFinalizar');
        btnFinalizar.disabled = true;
        btnFinalizar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Processando...';

        try {
            const res = await fetch('/pdv/finalizar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_cliente, metodo_pagamento, carrinho })
            });
            const data = await res.json();
            
            if (data.success) {
                // Preparar recibo WhatsApp
                let msg = `*COMPROVANTE DE VENDA #${data.id_venda}*\n\n`;
                carrinho.forEach(i => {
                    msg += `${i.quantidade}x ${i.nome} - R$ ${i.preco.toFixed(2)}\n`;
                });
                msg += `\n*Total: R$ ${data.total.toFixed(2)}*\n*Pagamento:* ${metodo_pagamento}\n\nObrigado pela preferência!`;
                
                let swalConfig = {
                    title: 'Venda Finalizada!',
                    html: `A venda #${data.id_venda} foi concluída com sucesso.${trocoMessage}`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Nova Venda',
                    cancelButtonText: '<i class="ph ph-printer"></i> Imprimir Cupom',
                    cancelButtonColor: '#3b82f6',
                    confirmButtonColor: '#10b981'
                };
                
                if (telefone && telefone.length > 8) {
                    swalConfig.showDenyButton = true;
                    swalConfig.denyButtonText = '<i class="ph ph-whatsapp-logo"></i> Enviar Recibo';
                    swalConfig.denyButtonColor = '#25D366';
                }
                
                Swal.fire(swalConfig).then((result) => {
                    if (result.isDenied) {
                        const encodedMsg = encodeURIComponent(msg);
                        window.open(`https://wa.me/${telefone.replace(/\D/g,'')}?text=${encodedMsg}`, '_blank');
                    } else if (result.dismiss === Swal.DismissReason.cancel) {
                        window.open('/pdv/recibo/' + data.id_venda, '_blank', 'width=400,height=600');
                    }
                    
                    carrinho = [];
                    atualizarCarrinho();
                    btnFinalizar.disabled = true;
                    btnFinalizar.innerHTML = '<i class="ph ph-check-circle"></i> Finalizar Venda';
                    document.getElementById('codigoBusca').focus();
                });
                
            } else {
                Swal.fire('Erro', 'Erro ao finalizar venda: ' + data.message, 'error');
                btnFinalizar.disabled = false;
                btnFinalizar.innerHTML = '<i class="bi bi-check-circle"></i> Finalizar Venda';
            }
        } catch(e) {
            Swal.fire('Erro', 'Erro de conexão', 'error');
            btnFinalizar.disabled = false;
            btnFinalizar.innerHTML = '<i class="bi bi-check-circle"></i> Finalizar Venda';
        }
    });

    // Inicializa
    renderBusca();

</script>

<?php include __DIR__ . '/../layouts/footer.php'; ?>
