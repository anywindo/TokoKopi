function toggleView(view) {
    const loginView = document.getElementById('login-view');
    const registerView = document.getElementById('register-view');

    if (view === 'register') {
        loginView.classList.add('hidden');
        registerView.classList.remove('hidden');
        loadBranches(); // Load branches when switching to register view
        toggleBranchSelection(); // Ensure correct state on load
    } else {
        loginView.classList.remove('hidden');
        registerView.classList.add('hidden');
    }
}

function toggleBranchSelection() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const branchRow = document.getElementById('branch-row');
    const branchSelect = document.getElementById('branch-select');

    if (role === 'corporate') {
        branchRow.classList.add('hidden');
        branchSelect.removeAttribute('required');
        branchSelect.value = ""; // Clear selection
    } else {
        branchRow.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
    }
}

function loadBranches() {
    const select = document.getElementById('branch-select');
    if (select.options.length > 1) return; // Already loaded

    fetch('api/get_branches.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(branch => {
                const option = document.createElement('option');
                option.value = branch;
                option.textContent = branch;
                select.appendChild(option);
            });
        })
        .catch(err => console.error('Error loading branches:', err));
}
