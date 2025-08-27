</div> </main>

    <footer class="bg-slate-800 text-white text-center p-4">
        <p>&copy; <?php echo date('Y'); ?> - Sistema de Controle de Estoque</p>
    </footer>

    <script>
        // Script para o menu hambúrguer
        if (document.getElementById('mobile-menu-button')) {
            document.getElementById('mobile-menu-button').addEventListener('click', function() {
                var menu = document.getElementById('mobile-menu');
                menu.classList.toggle('hidden');
            });
        }
    </script>
</body>
</html>