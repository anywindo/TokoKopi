//<![CDATA[

// Tab
function showTab(tabId) {
    ['dashboard', 'revenue', 'stock', 'branches'].forEach(id => {
        document.getElementById('tab-' + id).classList.add('hidden');
        document.getElementById('nav-' + id).classList.remove('active');
    });
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    document.getElementById('nav-' + tabId).classList.add('active');
}

// API
const API_BASE = '../api/corporate_api.php';

async function api(action, method = 'GET', data = null) {
    const options = { method };
    if (data) {
        options.body = JSON.stringify(data);
        options.headers = { 'Content-Type': 'application/json' };
    }
    const res = await fetch(`${API_BASE}?action=${action}`, options);
    return await res.json();
}

// Logika Preset
function handlePresetChange(type) {
    const preset = document.getElementById(type + '-preset').value;
    const customDiv = document.getElementById(type + '-custom-range');

    if (preset === 'custom') {
        customDiv.classList.remove('hidden');
    } else {
        customDiv.classList.add('hidden');

        // Hitung tanggal
        const today = new Date();
        let start = new Date();
        let end = new Date();

        if (preset === 'today') {
            // start dan end adalah hari ini
        } else if (preset === 'yesterday') {
            start.setDate(today.getDate() - 1);
            end.setDate(today.getDate() - 1);
        } else if (preset === 'last7') {
            start.setDate(today.getDate() - 7);
        } else if (preset === 'this_month') {
            start = new Date(today.getFullYear(), today.getMonth(), 1);
        }

        // Set input (meskipun tersembunyi, agar fungsi muat dapat membacanya)
        document.getElementById(type + '-start-date').value = start.toISOString().split('T')[0];
        document.getElementById(type + '-end-date').value = end.toISOString().split('T')[0];

        // Muat Ulang
        if (type === 'rev') loadRevenue();
        if (type === 'stock') loadStock();
    }
}

// Dashboard
async function loadDashboard() {
    const data = await api('get_dashboard_data');

    // Grafik Pendapatan
    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: {
            labels: data.revenue_chart.map(d => d.tanggal),
            datasets: [{
                label: 'Revenue',
                data: data.revenue_chart.map(d => d.total),
                borderColor: '#8c6a48',
                tension: 0.4
            }]
        }
    });

    // Grafik Stok
    if (data.stock_avg) {
        new Chart(document.getElementById('stockChart'), {
            type: 'bar',
            data: {
                labels: ['Arabica', 'Robusta', 'Liberica', 'Decaf', 'Milk'],
                datasets: [{
                    label: 'Avg Usage',
                    data: [data.stock_avg.arabica, data.stock_avg.robusta, data.stock_avg.liberica, data.stock_avg.decaf, data.stock_avg.susu],
                    backgroundColor: '#d4bba2'
                }]
            }
        });
    }

    // Grafik Cabang
    new Chart(document.getElementById('branchChart'), {
        type: 'bar',
        data: {
            labels: data.branch_revenue.map(d => d.nama),
            datasets: [{
                label: 'Total Revenue',
                data: data.branch_revenue.map(d => d.total),
                backgroundColor: '#5d4037'
            }]
        }
    });
}

// Pendapatan
async function loadRevenue() {
    const startDate = document.getElementById('rev-start-date').value;
    const endDate = document.getElementById('rev-end-date').value;
    const branch = document.getElementById('rev-branch').value;
    const url = `get_revenue_history&start_date=${startDate}&end_date=${endDate}&branch=${branch}`;

    // Fetch manual untuk query params
    const res = await fetch(`${API_BASE}?action=${url}`);
    const data = await res.json();

    const tbody = document.getElementById('revenue-table-body');
    tbody.innerHTML = '';

    let totalOmzet = 0;

    data.forEach(row => {
        totalOmzet += parseInt(row.omzet);
        tbody.innerHTML += `<tr>
                <td>${row.tanggal}</td>
                <td>${row.branch_name}</td>
                <td>${row.pelapor}</td>
                <td>Rp ${parseInt(row.omzet).toLocaleString()}</td>
            </tr>`;
    });

    document.getElementById('revenue-summary-total').textContent = 'Rp ' + totalOmzet.toLocaleString();
}

// Stok
async function loadStock() {
    const startDate = document.getElementById('stock-start-date').value;
    const endDate = document.getElementById('stock-end-date').value;
    const branch = document.getElementById('stock-branch').value;
    const url = `get_stock_history&start_date=${startDate}&end_date=${endDate}&branch=${branch}`;

    const res = await fetch(`${API_BASE}?action=${url}`);
    const data = await res.json();

    const tbody = document.getElementById('stock-table-body');
    tbody.innerHTML = '';

    let totals = { arabica: 0, robusta: 0, liberica: 0, decaf: 0, susu: 0 };

    data.forEach(row => {
        totals.arabica += parseFloat(row.arabica || 0);
        totals.robusta += parseFloat(row.robusta || 0);
        totals.liberica += parseFloat(row.liberica || 0);
        totals.decaf += parseFloat(row.decaf || 0);
        totals.susu += parseFloat(row.susu || 0);

        tbody.innerHTML += `<tr>
                <td>${row.tanggal}</td>
                <td>${row.branch_name}</td>
                <td>${row.pelapor}</td>
                <td>${row.arabica}</td>
                <td>${row.robusta}</td>
                <td>${row.liberica}</td>
                <td>${row.decaf}</td>
                <td>${row.susu}</td>
            </tr>`;
    });

    document.getElementById('sum-arabica').textContent = totals.arabica.toFixed(1) + ' Kg';
    document.getElementById('sum-robusta').textContent = totals.robusta.toFixed(1) + ' Kg';
    document.getElementById('sum-liberica').textContent = totals.liberica.toFixed(1) + ' Kg';
    document.getElementById('sum-decaf').textContent = totals.decaf.toFixed(1) + ' Kg';
    document.getElementById('sum-susu').textContent = totals.susu.toFixed(1) + ' L';
}

// Cabang
async function loadBranches() {
    const data = await api('get_branches');
    const tbody = document.getElementById('branch-table-body');
    const selectRev = document.getElementById('rev-branch');
    const selectStock = document.getElementById('stock-branch');

    // Isi Tabel
    tbody.innerHTML = '';
    data.forEach(row => {
        const rowData = encodeURIComponent(JSON.stringify(row)).replace(/'/g, '%27');
        tbody.innerHTML += `<tr>
                <td>${row.id_branch}</td>
                <td>${row.nama}</td>
                <td>${row.alamat}</td>
                <td>
                    <button onclick="openEditModal('branch', '${rowData}')" class="action-btn btn-edit">Edit</button>
                    <button onclick="deleteBranch(${row.id_branch})" class="action-btn btn-delete">Delete</button>
                </td>
            </tr>`;
    });

    // Isi Filter
    const opts = '<option value="">All Branches</option>' + data.map(b => `<option value="${b.id_branch}">${b.nama}</option>`).join('');
    selectRev.innerHTML = opts;
    selectStock.innerHTML = opts;
}

async function addBranch(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const res = await api('add_branch', 'POST', data);
    if (res.success) {
        e.target.reset();
        loadBranches();
    }
}

async function deleteBranch(id) {
    if (!confirm('Delete branch?')) return;
    const formData = new FormData();
    formData.append('id', id);
    await fetch(`${API_BASE}?action=delete_branch`, { method: 'POST', body: formData });
    loadBranches();
}

// Logika Modal Edit
function openEditModal(type, encodedData) {
    const data = JSON.parse(decodeURIComponent(encodedData));
    const modal = document.getElementById('edit-modal');
    const fields = document.getElementById('edit-fields');

    document.getElementById('edit-type').value = type;

    fields.innerHTML = '';

    // Helper untuk escape aman atribut HTML
    const safe = (str) => str ? String(str).replace(/"/g, '&quot;') : '';

    if (type === 'branch') {
        document.getElementById('edit-id').value = data.id_branch;
        fields.innerHTML = `
            <div>
                <label class="input-label block mb-1">Name</label>
                <input type="text" name="nama" value="${safe(data.nama)}" class="skeuo-input-box w-full" required="required" />
            </div>
            <div>
                <label class="input-label block mb-1">Address</label>
                <input type="text" name="alamat" value="${safe(data.alamat)}" class="skeuo-input-box w-full" required="required" />
            </div>
        `;
    } else if (type === 'revenue') {
        document.getElementById('edit-id').value = data.id_laporan;
        fields.innerHTML = `
            <div>
                <label class="input-label block mb-1">Revenue Amount</label>
                <input type="number" name="omzet" value="${data.omzet}" class="skeuo-input-box w-full" required="required" />
            </div>
            <div class="text-xs text-gray-500 italic">Date: ${data.tanggal} | Branch: ${data.branch_name}</div>
        `;
    } else if (type === 'stock') {
        document.getElementById('edit-id').value = data.id_laporan;
        fields.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div><label class="input-label block mb-1">Arabica</label><input type="number" name="arabica" value="${data.arabica}" class="skeuo-input-box w-full" /></div>
                <div><label class="input-label block mb-1">Robusta</label><input type="number" name="robusta" value="${data.robusta}" class="skeuo-input-box w-full" /></div>
                <div><label class="input-label block mb-1">Liberica</label><input type="number" name="liberica" value="${data.liberica}" class="skeuo-input-box w-full" /></div>
                <div><label class="input-label block mb-1">Decaf</label><input type="number" name="decaf" value="${data.decaf}" class="skeuo-input-box w-full" /></div>
                <div><label class="input-label block mb-1">Milk</label><input type="number" name="susu" value="${data.susu}" class="skeuo-input-box w-full" /></div>
            </div>
            <div class="text-xs text-gray-500 italic mt-2">Date: ${data.tanggal} | Branch: ${data.branch_name}</div>
        `;
    }

    modal.classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('edit-modal').classList.add('hidden');
}

async function submitEdit(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const type = data.type;

    let action = '';
    if (type === 'branch') action = 'update_branch';
    if (type === 'revenue') action = 'update_omzet';
    if (type === 'stock') action = 'update_stock';

    const res = await api(action, 'POST', data);

    if (res.success) {
        closeEditModal();
        if (type === 'branch') loadBranches();
        if (type === 'revenue') loadRevenue();
        if (type === 'stock') loadStock();
    } else {
        alert('Update failed: ' + res.error);
    }
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', () => {
    loadDashboard();
    loadBranches();

    // Set default presets
    handlePresetChange('rev');
    handlePresetChange('stock');

    loadUserProfile();
});

async function loadUserProfile() {
    try {
        const res = await fetch('../api/profile_api.php');
        const data = await res.json();
        if (data.username) {
            document.getElementById('sidebar-username').textContent = 'Hello, ' + data.username;
        }
        if (data.profile_photo) {
            const sbAvatar = document.querySelector('.profile-avatar-small');
            if (sbAvatar) sbAvatar.src = '../uploads/profiles/' + data.profile_photo;
        }
    } catch (e) {
        console.error('Failed to load profile', e);
    }
}
//]]>