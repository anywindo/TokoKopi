// Ganti Tampilan
function toggleView(view) {
    const loginView = document.getElementById('login-view');
    const registerView = document.getElementById('register-view');

    if (view === 'register') {
        loginView.classList.add('hidden');
        registerView.classList.remove('hidden');
        loadBranches();
        toggleBranchSelection();
    } else {
        loginView.classList.remove('hidden');
        registerView.classList.add('hidden');
    }
}

// Ganti Pilihan Cabang
function toggleBranchSelection() {
    const role = document.querySelector('input[name="role"]:checked').value;
    const branchRow = document.getElementById('branch-row');
    const branchSelect = document.getElementById('branch-select');

    if (role === 'corporate') {
        branchRow.classList.add('hidden');
        branchSelect.removeAttribute('required');
        branchSelect.value = "";
    } else {
        branchRow.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
    }
}

// Muat Cabang
function loadBranches() {
    const select = document.getElementById('branch-select');
    if (select.options.length > 1) return;

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

// Handle Registration Form
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('api/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Registration successful! Please login.');
                    toggleView('login');
                    this.reset();
                } else {
                    alert(data.error || 'Registration failed.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during registration.');
            });
        });
    }
});
