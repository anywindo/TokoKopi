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

// Handle Login Form
function handleLogin(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('api/login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Login failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during login.');
    });
    return false;
}

// Handle Registration Form
function handleRegister(e) {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('api/register.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Registration successful! Please login.');
            toggleView('login');
            form.reset();
        } else {
            alert(data.error || 'Registration failed.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred during registration.');
    });
    return false;
}
