        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- LGPD: Banner de Cookies -->
    <div id="cookieBanner" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background-color: var(--surface); border-top: 1px solid var(--border-subtle); padding: 1rem 2rem; z-index: 9999; box-shadow: 0 -4px 15px rgba(0,0,0,0.12); justify-content: space-between; align-items: center; gap: 1rem; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; color: var(--text-secondary); font-size: 0.9rem;">
            <strong style="color: var(--text-primary);"><i class="ph ph-shield-check"></i> Política de Privacidade e LGPD:</strong> Utilizamos cookies estritamente essenciais (sessão autenticada e proteção CSRF) e criptografia AES-256-GCM para proteger dados pessoais em repouso.
        </div>
        <button id="aceitarCookies" class="btn btn-primary" style="white-space: nowrap;">Entendido</button>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            if (!localStorage.getItem('lgpd_accepted')) {
                document.getElementById('cookieBanner').style.display = 'flex';
            }
            
            document.getElementById('aceitarCookies').addEventListener('click', () => {
                localStorage.setItem('lgpd_accepted', 'true');
                document.getElementById('cookieBanner').style.display = 'none';
            });
            
            // Prevenção Global de Duplo Clique (Double Submit)
            document.querySelectorAll('form').forEach(form => {
                if (form.hasAttribute('data-no-spinner')) return;
                form.addEventListener('submit', function() {
                    const btns = this.querySelectorAll('button[type="submit"]');
                    btns.forEach(btn => {
                        if (!btn.hasAttribute('data-no-disable')) {
                            btn.disabled = true;
                            const originalText = btn.innerHTML;
                            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Aguarde...';
                            setTimeout(() => {
                                btn.disabled = false;
                                btn.innerHTML = originalText;
                            }, 5000);
                        }
                    });
                });
            });
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/notifications.js"></script>
</body>
</html>
