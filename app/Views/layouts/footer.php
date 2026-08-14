        </main>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- LGPD: Banner de Cookies -->
    <div id="cookieBanner" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background-color: var(--surface); border-top: 1px solid var(--border); padding: 1rem 2rem; z-index: 9999; box-shadow: 0 -4px 15px rgba(0,0,0,0.1); justify-content: space-between; align-items: center; gap: 1rem;">
        <div style="flex: 1; color: var(--text-secondary); font-size: 0.9rem;">
            <strong style="color: var(--text-primary);"><i class="ph ph-shield-check"></i> Política de Privacidade e LGPD:</strong> Nós utilizamos cookies essenciais (como token de sessão e CSRF) estritamente necessários para o funcionamento seguro do sistema. Nenhum dado de clientes é compartilhado com terceiros.
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
        });
    </script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="/js/notifications.js"></script>
</body>
</html>
