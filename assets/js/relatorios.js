
    function toggleDropdown() {
        document.getElementById('download-dropdown').classList.toggle('hidden');
    }

    // Fecha o dropdown se clicar fora dele
    window.onclick = function(event) {
        const container = document.getElementById('download-container');
        if (!container.contains(event.target)) {
            document.getElementById('download-dropdown').classList.add('hidden');
        }
    }
