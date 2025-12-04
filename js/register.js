// Inisialisasi
window.addEventListener('DOMContentLoaded', function () {
    fetch('../api/get_branches.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('branch');
            data.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch;
                option.textContent = branch;
                select.appendChild(option);
            });
        })
        .catch(err => console.error('Error loading branches:', err));
});
