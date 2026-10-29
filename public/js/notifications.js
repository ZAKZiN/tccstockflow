document.addEventListener('DOMContentLoaded', () => {
    const topbar = document.querySelector('.topbar');
    if (!topbar) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const notifContainer = document.createElement('div');
    notifContainer.className = 'notif-bell-wrapper';
    notifContainer.style.position = 'relative';
    notifContainer.style.marginLeft = 'auto';
    notifContainer.style.cursor = 'pointer';
    notifContainer.style.display = 'flex';
    notifContainer.style.alignItems = 'center';
    notifContainer.style.padding = '0.35rem';

    const bellIcon = document.createElement('i');
    bellIcon.className = 'ph ph-bell';
    bellIcon.style.fontSize = '1.45rem';
    bellIcon.style.color = 'var(--text-secondary)';
    
    const badge = document.createElement('span');
    badge.style.position = 'absolute';
    badge.style.top = '-2px';
    badge.style.right = '-2px';
    badge.style.backgroundColor = 'var(--danger)';
    badge.style.color = 'white';
    badge.style.borderRadius = '9999px';
    badge.style.padding = '0.1rem 0.38rem';
    badge.style.fontSize = '0.68rem';
    badge.style.fontWeight = 'bold';
    badge.style.display = 'none';

    const dropdown = document.createElement('div');
    dropdown.className = 'glass-panel';
    dropdown.style.position = 'absolute';
    dropdown.style.top = '100%';
    dropdown.style.right = '0';
    dropdown.style.width = '320px';
    dropdown.style.maxHeight = '400px';
    dropdown.style.overflowY = 'auto';
    dropdown.style.zIndex = '1050';
    dropdown.style.display = 'none';
    dropdown.style.padding = '1rem';
    dropdown.style.marginTop = '0.5rem';
    dropdown.style.cursor = 'default';

    notifContainer.appendChild(bellIcon);
    notifContainer.appendChild(badge);
    notifContainer.appendChild(dropdown);
    
    if (topbar.children.length > 1) {
        topbar.insertBefore(notifContainer, topbar.children[1]);
    } else {
        topbar.appendChild(notifContainer);
    }

    async function loadNotifications() {
        try {
            const res = await fetch('/api/notificacoes', { headers: { 'Accept': 'application/json' } });
            if (!res.ok) return;
            const data = await res.json();
            
            dropdown.replaceChildren();
            if (Array.isArray(data) && data.length > 0) {
                badge.style.display = 'block';
                badge.textContent = String(data.length);
                
                const title = document.createElement('h4');
                title.textContent = 'Notificações';
                title.style.marginBottom = '0.85rem';
                title.style.fontSize = '0.95rem';
                title.style.fontWeight = '600';
                dropdown.appendChild(title);

                data.forEach(n => {
                    const item = document.createElement('div');
                    item.style.borderBottom = '1px solid var(--border-subtle)';
                    item.style.paddingBottom = '0.75rem';
                    item.style.marginBottom = '0.75rem';
                    item.style.cursor = 'pointer';

                    // Construção via DOM segura contra XSS (textContent em vez de innerHTML)
                    const strong = document.createElement('strong');
                    strong.style.display = 'block';
                    strong.style.fontSize = '0.85rem';
                    strong.style.color = 'var(--text-primary)';
                    strong.style.marginBottom = '0.25rem';
                    strong.textContent = n.titulo || '';

                    const msg = document.createElement('span');
                    msg.style.fontSize = '0.8rem';
                    msg.style.color = 'var(--text-secondary)';
                    msg.style.display = 'block';
                    msg.style.lineHeight = '1.4';
                    msg.textContent = n.mensagem || '';

                    const hint = document.createElement('div');
                    hint.style.fontSize = '0.7rem';
                    hint.style.color = 'var(--primary)';
                    hint.style.textAlign = 'right';
                    hint.style.marginTop = '0.45rem';
                    hint.textContent = 'Clique para marcar como lida';

                    item.appendChild(strong);
                    item.appendChild(msg);
                    item.appendChild(hint);
                    
                    item.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        await fetch('/api/notificacoes/ler/' + encodeURIComponent(n.id_notificacao), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-Token': csrfToken,
                                'Accept': 'application/json'
                            }
                        });
                        loadNotifications();
                    });
                    
                    dropdown.appendChild(item);
                });
            } else {
                badge.style.display = 'none';
                const empty = document.createElement('span');
                empty.style.fontSize = '0.85rem';
                empty.style.color = 'var(--text-secondary)';
                empty.textContent = 'Nenhuma notificação nova.';
                dropdown.appendChild(empty);
            }
        } catch (e) {
            // Falha silenciosa de rede
        }
    }

    notifContainer.addEventListener('click', () => {
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    });

    document.addEventListener('click', (e) => {
        if (!notifContainer.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });

    loadNotifications();
    setInterval(loadNotifications, 30000);
});
