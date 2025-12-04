//<![CDATA[

const API_BASE = '../api/admin_api.php';

// Tab Switching
function showTab(tabId) {
    ['users', 'branches'].forEach(id => {
        document.getElementById('tab-' + id).classList.add('hidden');
        document.getElementById('nav-' + id).classList.remove('active');
    });
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    document.getElementById('nav-' + tabId).classList.add('active');
}

// API Helper
async function api(action, method = 'GET', data = null) {
    const options = { method };
    
    if (data instanceof FormData) {
        options.body = data;
        // Don't set Content-Type for FormData, browser does it with boundary
    } else if (data) {
        options.body = JSON.stringify(data);
        options.headers = { 'Content-Type': 'application/json' };
    }

    const res = await fetch(`${API_BASE}?action=${action}`, options);
    return await res.json();
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', () => {
    checkSession();
    loadUsers();
    loadBranches();
    
    // Default tab
    showTab('users');
});

async function checkSession() {
    const res = await api('check_session');
    if (!res.authenticated) {
        window.location.href = '../index.xhtml';
    }
}



// User Management
async function loadUsers() {
    const data = await api('get_users');
    const tbody = document.getElementById('users-table-body');
    tbody.innerHTML = '';
    
    data.forEach(user => {
        const userJson = encodeURIComponent(JSON.stringify(user));
        tbody.innerHTML += `<tr>
            <td>${user.id_user}</td>
            <td>${user.username}</td>
            <td><span class="badge badge-${user.role}">${user.role}</span></td>
            <td>${user.branch_name || '-'}</td>
            <td>
                <button onclick="openEditUserModal('${userJson}')" class="action-btn btn-edit">Edit</button>
                <button onclick="deleteUser(${user.id_user})" class="action-btn btn-delete">Delete</button>
            </td>
        </tr>`;
    });
}

function toggleBranchSelect(select) {
    const container = document.getElementById('user-branch-select-container');
    const branchSelect = document.getElementById('user-branch-select');
    
    if (select.value === 'manager') {
        container.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
        loadBranchesForSelect();
    } else {
        container.classList.add('hidden');
        branchSelect.removeAttribute('required');
        branchSelect.value = "";
    }
}

async function loadBranchesForSelect() {
    const select = document.getElementById('user-branch-select');
    if (select.options.length > 1) return; // Already loaded
    
    const data = await api('get_branches');
    data.forEach(branch => {
        const option = document.createElement('option');
        option.value = branch.id_branch;
        option.textContent = branch.nama;
        select.appendChild(option);
    });
}

async function addUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const res = await api('add_user', 'POST', data); // Send as JSON
    
    if (res.success) {
        alert('User added successfully');
        e.target.reset();
        loadUsers();
    } else {
        alert('Failed to add user: ' + res.error);
    }
}

async function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    const res = await api('delete_user', 'POST', formData);
    
    if (res.success) {
        loadUsers();
    } else {
        alert('Failed to delete user: ' + res.error);
    }
}

// Edit User Logic
function openEditUserModal(userJson) {
    const user = JSON.parse(decodeURIComponent(userJson));
    document.getElementById('edit-user-id').value = user.id_user;
    document.getElementById('edit-user-username').value = user.username;
    
    const roleSelect = document.getElementById('edit-user-role');
    roleSelect.value = user.role;
    toggleEditBranchSelect(roleSelect);
    
    if (user.role === 'manager') {
        // We need to find the branch ID from the name, but the API returns branch_name.
        // Ideally API should return id_branch too. Assuming it does or we reload.
        // Let's check getUsers in API. It returns u.id_user, u.username, u.role, b.nama.
        // It does NOT return id_branch. We need to fix API to return id_branch.
        // For now, let's assume we fix API.
        loadBranchesForEdit(user.id_branch); 
    }
    
    document.getElementById('edit-user-modal').classList.remove('hidden');
}

function closeEditUserModal() {
    document.getElementById('edit-user-modal').classList.add('hidden');
}

async function loadBranchesForEdit(selectedId) {
    const select = document.getElementById('edit-user-branch');
    if (select.options.length <= 1) {
        const data = await api('get_branches');
        data.forEach(branch => {
            const option = document.createElement('option');
            option.value = branch.id_branch;
            option.textContent = branch.nama;
            select.appendChild(option);
        });
    }
    if (selectedId) select.value = selectedId;
}

function toggleEditBranchSelect(select) {
    const container = document.getElementById('edit-user-branch-container');
    const branchSelect = document.getElementById('edit-user-branch');
    
    if (select.value === 'manager') {
        container.classList.remove('hidden');
        branchSelect.setAttribute('required', 'required');
        loadBranchesForEdit();
    } else {
        container.classList.add('hidden');
        branchSelect.removeAttribute('required');
        branchSelect.value = "";
    }
}

async function updateUser(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const res = await api('update_user', 'POST', data);
    
    if (res.success) {
        alert('User updated successfully');
        closeEditUserModal();
        loadUsers();
    } else {
        alert('Failed to update user: ' + res.error);
    }
}

// Branch Management
async function loadBranches() {
    const data = await api('get_branches');
    const tbody = document.getElementById('branches-table-body');
    tbody.innerHTML = '';
    
    data.forEach(branch => {
        const branchJson = encodeURIComponent(JSON.stringify(branch));
        tbody.innerHTML += `<tr>
            <td>${branch.id_branch}</td>
            <td>${branch.nama}</td>
            <td>${branch.alamat}</td>
            <td>
                <button onclick="openEditBranchModal('${branchJson}')" class="action-btn btn-edit">Edit</button>
                <button onclick="deleteBranch(${branch.id_branch})" class="action-btn btn-delete">Delete</button>
            </td>
        </tr>`;
    });
}

async function addBranch(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const res = await api('add_branch', 'POST', data); // Send as JSON
    
    if (res.success) {
        alert('Branch added successfully');
        e.target.reset();
        loadBranches();
        // Reload select options if needed
        document.getElementById('user-branch-select').innerHTML = '<option value="">Select Branch</option>';
    } else {
        alert('Failed to add branch: ' + res.error);
    }
}

async function deleteBranch(id) {
    if (!confirm('Are you sure you want to delete this branch?')) return;
    
    const formData = new FormData();
    formData.append('id', id);
    
    const res = await api('delete_branch', 'POST', formData);
    
    if (res.success) {
        loadBranches();
        document.getElementById('user-branch-select').innerHTML = '<option value="">Select Branch</option>';
    } else {
        alert('Failed to delete branch: ' + res.error);
    }
}

// Edit Branch Logic
function openEditBranchModal(branchJson) {
    const branch = JSON.parse(decodeURIComponent(branchJson));
    document.getElementById('edit-branch-id').value = branch.id_branch;
    document.getElementById('edit-branch-nama').value = branch.nama;
    document.getElementById('edit-branch-alamat').value = branch.alamat;
    
    document.getElementById('edit-branch-modal').classList.remove('hidden');
}

function closeEditBranchModal() {
    document.getElementById('edit-branch-modal').classList.add('hidden');
}

async function updateBranch(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    
    const res = await api('update_branch', 'POST', data);
    
    if (res.success) {
        alert('Branch updated successfully');
        closeEditBranchModal();
        loadBranches();
    } else {
        alert('Failed to update branch: ' + res.error);
    }
}

//]]>
