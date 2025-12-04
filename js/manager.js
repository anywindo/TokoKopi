// <![CDATA[

const API_BASE = '../api/manager_api.php';

// Tab
function showTab(tabId) {
    ['revenue', 'stock'].forEach(id => {
        document.getElementById('tab-' + id).classList.add('hidden');
        document.getElementById('nav-' + id).classList.remove('active');
    });
    document.getElementById('tab-' + tabId).classList.remove('hidden');
    document.getElementById('nav-' + tabId).classList.add('active');
}

// API
async function api(action, method = 'GET', body = null) {
    const options = { method };
    if (body) {
        options.body = JSON.stringify(body);
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
        loadHistory();
    }
}

// Muat Data
async function loadHistory() {
    // Ambil nilai filter
    const revStart = document.getElementById('rev-start-date')?.value || '';
    const revEnd = document.getElementById('rev-end-date')?.value || '';
    const stockStart = document.getElementById('stock-start-date')?.value || '';
    const stockEnd = document.getElementById('stock-end-date')?.value || '';

    const data = await api(`get_history&rev_start=${revStart}&rev_end=${revEnd}&stock_start=${stockStart}&stock_end=${stockEnd}`);

    // Tabel Pendapatan
    const revBody = document.getElementById('revenue-table-body');
    revBody.innerHTML = '';
    let totalOmzet = 0;
    data.revenue.forEach(row => {
        totalOmzet += parseInt(row.omzet);
        revBody.innerHTML += `<tr>
                <td>${row.id_laporan}</td>
                <td>${row.tanggal}</td>
                <td>Rp ${parseInt(row.omzet).toLocaleString()}</td>
                <td>
                    <button onclick="openEditRevenueModal('${encodeURIComponent(JSON.stringify(row))}')" class="action-btn btn-edit">Edit</button>
                    <button onclick="deleteReport('revenue', ${row.id_laporan})" class="action-btn btn-delete">Delete</button>
                </td>
            </tr>`;
    });
    document.getElementById('revenue-summary-total').textContent = 'Rp ' + totalOmzet.toLocaleString();

    // Tabel Stok
    const stockBody = document.getElementById('stock-table-body');
    stockBody.innerHTML = '';
    let totals = { arabica: 0, robusta: 0, liberica: 0, decaf: 0, susu: 0 };
    data.stock.forEach(row => {
        totals.arabica += parseFloat(row.arabica || 0);
        totals.robusta += parseFloat(row.robusta || 0);
        totals.liberica += parseFloat(row.liberica || 0);
        totals.decaf += parseFloat(row.decaf || 0);
        totals.susu += parseFloat(row.susu || 0);

        stockBody.innerHTML += `<tr>
                <td>${row.id_laporan}</td>
                <td>${row.tanggal}</td>
                <td>${row.arabica}</td>
                <td>${row.robusta}</td>
                <td>${row.liberica}</td>
                <td>${row.decaf}</td>
                <td>${row.susu}</td>
                <td>
                    <button onclick="openEditStockModal('${encodeURIComponent(JSON.stringify(row))}')" class="action-btn btn-edit">Edit</button>
                    <button onclick="deleteReport('stock', ${row.id_laporan})" class="action-btn btn-delete">Delete</button>
                </td>
            </tr>`;
    });
    document.getElementById('sum-arabica').textContent = totals.arabica.toFixed(1) + ' Kg';
    document.getElementById('sum-robusta').textContent = totals.robusta.toFixed(1) + ' Kg';
    document.getElementById('sum-liberica').textContent = totals.liberica.toFixed(1) + ' Kg';
    document.getElementById('sum-decaf').textContent = totals.decaf.toFixed(1) + ' Kg';
    document.getElementById('sum-susu').textContent = totals.susu.toFixed(1) + ' L';
}

// Aksi
async function addRevenue(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const res = await api('add_revenue', 'POST', data);
    if (res.success) {
        e.target.reset();
        loadHistory();
    } else {
        alert('Error: ' + res.error);
    }
}

async function addStock(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const res = await api('add_stock', 'POST', data);
    if (res.success) {
        e.target.reset();
        loadHistory();
    } else {
        alert('Error: ' + res.error);
    }
}

async function deleteReport(type, id) {
    if (!confirm('Delete this report?')) return;
    const formData = new FormData();
    formData.append('type', type);
    formData.append('id', id);
    await fetch(`${API_BASE}?action=delete_report`, { method: 'POST', body: formData });
    await fetch(`${API_BASE}?action=delete_report`, { method: 'POST', body: formData });
    loadHistory();
}

// Edit Revenue Logic
function openEditRevenueModal(rowJson) {
    const row = JSON.parse(decodeURIComponent(rowJson));
    document.getElementById('edit-rev-id').value = row.id_laporan;
    document.getElementById('edit-rev-date').value = row.tanggal;
    document.getElementById('edit-rev-omzet').value = row.omzet;
    document.getElementById('edit-revenue-modal').classList.remove('hidden');
}

function closeEditRevenueModal() {
    document.getElementById('edit-revenue-modal').classList.add('hidden');
}

async function updateRevenue(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const res = await api('update_revenue', 'POST', data);
    if (res.success) {
        closeEditRevenueModal();
        loadHistory();
    } else {
        alert('Error: ' + res.error);
    }
}

// Edit Stock Logic
function openEditStockModal(rowJson) {
    const row = JSON.parse(decodeURIComponent(rowJson));
    document.getElementById('edit-stock-id').value = row.id_laporan;
    document.getElementById('edit-stock-date').value = row.tanggal;
    document.getElementById('edit-stock-arabica').value = row.arabica;
    document.getElementById('edit-stock-robusta').value = row.robusta;
    document.getElementById('edit-stock-liberica').value = row.liberica;
    document.getElementById('edit-stock-decaf').value = row.decaf;
    document.getElementById('edit-stock-susu').value = row.susu;
    document.getElementById('edit-stock-modal').classList.remove('hidden');
}

function closeEditStockModal() {
    document.getElementById('edit-stock-modal').classList.add('hidden');
}

async function updateStock(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const res = await api('update_stock', 'POST', data);
    if (res.success) {
        closeEditStockModal();
        loadHistory();
    } else {
        alert('Error: ' + res.error);
    }
}

// Inisialisasi
document.addEventListener('DOMContentLoaded', () => {
    // Set default presets
    handlePresetChange('rev');
    handlePresetChange('stock');

    // Set default date for forms
    const today = new Date().toISOString().split('T')[0];
    const revDateInput = document.querySelector('form[onsubmit="addRevenue(event)"] input[name="tanggal"]');
    const stockDateInput = document.querySelector('form[onsubmit="addStock(event)"] input[name="tanggal"]');

    if (revDateInput) revDateInput.value = today;
    if (stockDateInput) stockDateInput.value = today;

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
        if (data.branch_name) {
            document.getElementById('rev-branch-name').textContent = data.branch_name;
            document.getElementById('stock-branch-name').textContent = data.branch_name;
        }
    } catch (e) {
        console.error('Failed to load profile', e);
    }
}
// ]]>