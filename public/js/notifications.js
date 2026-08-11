document.addEventListener('DOMContentLoaded', () => {
    const topbar = document.querySelector('.topbar');
    if (!topbar) return;

    // Criar o container do sino
    const notifContainer = document.createElement('div');
    notifContainer.style.position = 'relative';
    notifContainer.style.marginLeft = 'auto';
    notifContainer.style.marginRight = '1rem';
    notifContainer.style.cursor = 'pointer';
    notifContainer.style.display = 'flex';
    notifContainer.style.alignItems = 'center';

    const bellIcon = document.createElement('i');
    bellIcon.className = 'ph ph-bell';
    bellIcon.style.fontSize = '1.5rem';
    bellIcon.style.color = 'var(--text-secondary)';
    
    const badge = document.createElement('span');
    badge.style.position = 'absolute';
    badge.style.top = '-5px';
    badge.style.right = '-5px';
    badge.style.backgroundColor = 'var(--danger)';
    badge.style.color = 'white';
    badge.style.borderRadius = '50%';
    badge.style.padding = '0.15rem 0.35rem';
    badge.style.fontSize = '0.7rem';
    badge.style.fontWeight = 'bold';
    badge.style.display = 'none';

    // Dropdown
    const dropdown = document.createElement('div');
    dropdown.className = 'glass-panel';
    dropdown.style.position = 'absolute';
    dropdown.style.top = '100%';
    dropdown.style.right = '0';
    dropdown.style.width = '320px';
    dropdown.style.maxHeight = '400px';
    dropdown.style.overflowY = 'auto';
    dropdown.style.zIndex = '100';
    dropdown.style.display = 'none';
    dropdown.style.padding = '1rem';
    dropdown.style.marginTop = '0.5rem';

    notifContainer.appendChild(bellIcon);
    notifContainer.appendChild(badge);
    notifContainer.appendChild(dropdown);
    
    topbar.insertBefore(notifContainer, topbar.children[1]);

    // Lógica de Fetch
    async function loadNotifications() {
        try {
            const res = await fetch('/api/notificacoes');
            if(!res.ok) return;
            const data = await res.json();
            
            dropdown.innerHTML = '';
            if (data.length > 0) {
                badge.style.display = 'block';
                badge.innerText = data.length;
                
                const title = document.createElement('h4');
                title.innerText = 'Notificações';
                title.style.marginBottom = '1rem';
                title.style.fontSize = '1rem';
                dropdown.appendChild(title);

                data.forEach(n => {
                    const item = document.createElement('div');
                    item.style.borderBottom = '1px solid var(--border-subtle)';
                    item.style.paddingBottom = '0.75rem';
                    item.style.marginBottom = '0.75rem';
                    
                    item.innerHTML = `
                        <strong style="display:block; font-size:0.85rem; color:var(--text-primary); margin-bottom:0.25rem;">${n.titulo}</strong>
                        <span style="font-size:0.8rem; color:var(--text-secondary); display:block; line-height:1.4;">${n.mensagem}</span>
                        <div style="font-size:0.7rem; color:var(--text-secondary); text-align:right; margin-top:0.5rem;">Clique para marcar como lida</div>
                    `;
                    
                    item.addEventListener('click', async (e) => {
                        e.stopPropagation();
                        await fetch('/api/notificacoes/ler/' + n.id_notificacao, {method: 'POST'});
                        loadNotifications();
                    });
                    
                    dropdown.appendChild(item);
                });
            } else {
                badge.style.display = 'none';
                dropdown.innerHTML = '<span style="font-size:0.85rem; color:var(--text-secondary)">Nenhuma notificação nova.</span>';
            }
        } catch(e) {}
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
