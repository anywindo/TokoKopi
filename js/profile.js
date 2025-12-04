// Muat Profil
async function loadProfile() {
    try {
        const res = await fetch('../api/profile_api.php');
        const user = await res.json();

        if (user.error) {
            window.location.href = '../index.xhtml';
            return;
        }

        // Isi Form
        document.getElementById('input-username').value = user.username;
        document.getElementById('input-fullname').value = user.full_name || '';
        document.getElementById('input-phone').value = user.telp || '';
        document.getElementById('input-role').value = user.role;
        document.getElementById('input-branch').value = user.branch_name || '-';
        document.getElementById('display-id').textContent = '#' + user.id_user;

        // Isi Bagian Atas
        document.getElementById('display-fullname').textContent = user.full_name || user.username;
        document.getElementById('display-role').textContent = user.role;

        if (user.profile_photo) {
            const avatar = document.getElementById('profile-avatar-img');
            avatar.src = '../uploads/profiles/' + user.profile_photo;
            avatar.classList.remove('opacity-80');
            avatar.classList.add('object-cover', 'w-full', 'h-full');

            // Update sidebar juga
            const sbAvatar = document.querySelector('.profile-avatar-small');
            if (sbAvatar) sbAvatar.src = '../uploads/profiles/' + user.profile_photo;
        }

        // Update UI Sidebar - Salam menggunakan Username sesuai permintaan
        document.getElementById('sb-name').textContent = 'Hello, ' + user.username;
        document.getElementById('sb-role').textContent = user.role;

        // Update Link Dashboard
        const dbLink = document.getElementById('sb-dashboard');
        const dbText = document.getElementById('sb-dashboard-text');
        const titleLink = document.getElementById('sidebar-title-link');

        let dashboardLink = '#';
        let dashboardText = 'Dashboard';

        if (user.role === 'corporate') {
            dashboardLink = 'corporate.xhtml';
            dashboardText = 'Corporate Dashboard';
        } else if (user.role === 'manager') {
            dashboardLink = 'manager.xhtml';
            dashboardText = 'Manager Dashboard';
        }

        dbLink.href = dashboardLink;
        dbText.textContent = dashboardText;
        titleLink.href = dashboardLink;

    } catch (e) {
        console.error('Failed to load profile:', e);
    }
}

// Simpan Profil
async function saveProfile(e) {
    e.preventDefault();
    const form = document.getElementById('profile-form');
    const formData = new FormData(form);

    try {
        const res = await fetch('../api/profile_api.php', {
            method: 'POST',
            body: formData
        });
        const result = await res.json();

        if (result.success) {
            alert('Profile updated successfully!');
            loadProfile();
        } else {
            alert('Error updating profile: ' + (result.error || 'Unknown error'));
        }
    } catch (e) {
        console.error('Save failed', e);
        alert('Failed to save changes.');
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);

// Password Modal Functions
function openPasswordModal() {
    const modal = document.getElementById('password-modal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closePasswordModal() {
    const modal = document.getElementById('password-modal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
    document.getElementById('password-form').reset();
}

async function changePassword(e) {
    e.preventDefault();
    
    const form = document.getElementById('password-form');
    const formData = new FormData(form);
    
    const newPass = formData.get('new_password');
    const confirmPass = formData.get('confirm_password');
    
    if (newPass !== confirmPass) {
        alert('New passwords do not match!');
        return;
    }
    
    try {
        const res = await fetch('../api/change_password.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await res.json();
        
        if (result.success) {
            alert('Password updated successfully!');
            closePasswordModal();
        } else {
            alert('Error: ' + (result.error || 'Failed to update password'));
        }
    } catch (e) {
        console.error('Password change failed:', e);
        alert('An error occurred while updating the password.');
    }
}