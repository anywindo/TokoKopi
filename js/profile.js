async function loadProfile() {
    try {
        const res = await fetch('../api/profile_api.php');
        const user = await res.json();

        if (user.error) {
            window.location.href = '../index.xhtml';
            return;
        }

        // Populate Form
        document.getElementById('input-username').value = user.username;
        document.getElementById('input-fullname').value = user.full_name || '';
        document.getElementById('input-phone').value = user.telp || '';
        document.getElementById('input-role').value = user.role;
        document.getElementById('input-branch').value = user.branch_name || '-';
        document.getElementById('display-id').textContent = '#' + user.id_user;

        // Populate Top Section
        document.getElementById('display-fullname').textContent = user.full_name || user.username;
        document.getElementById('display-role').textContent = user.role;

        if (user.profile_photo) {
            const avatar = document.getElementById('profile-avatar-img');
            avatar.src = '../uploads/profiles/' + user.profile_photo;
            avatar.classList.remove('opacity-80');
            avatar.classList.add('object-cover', 'w-full', 'h-full');

            // Also update sidebar
            const sbAvatar = document.querySelector('.profile-avatar-small');
            if (sbAvatar) sbAvatar.src = '../uploads/profiles/' + user.profile_photo;
        }

        // Update Sidebar UI - Greeting uses Username as requested
        document.getElementById('sb-name').textContent = 'Hello, ' + user.username;
        document.getElementById('sb-role').textContent = user.role;

        // Update Dashboard Link
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
            loadProfile(); // Reload to refresh data and UI
        } else {
            alert('Error updating profile: ' + (result.error || 'Unknown error'));
        }
    } catch (e) {
        console.error('Save failed', e);
        alert('Failed to save changes.');
    }
}

document.addEventListener('DOMContentLoaded', loadProfile);