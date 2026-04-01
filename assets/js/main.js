// ============================================
// Raquel Pawnshop HRIS - Main JavaScript
// ============================================

/**
 * Toggle sidebar visibility/collapse
 */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const isMobile = window.matchMedia('(max-width: 992px)').matches;

    if (isMobile) {
        // Mobile behavior: slide-in/slide-out
        sidebar.classList.toggle('show');
        if (overlay) overlay.classList.toggle('show');
    } else {
        // Desktop behavior: collapse to icon-only
        document.documentElement.classList.toggle('sidebar-collapsed');
        const isCollapsed = document.documentElement.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebar_collapsed', isCollapsed);
    }
}

/**
 * Mark all notifications as read (AJAX)
 */
function markAllRead() {
    fetch(window.location.origin + '/raquel-hris/includes/ajax/mark-notifications-read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove badge
            const badge = document.querySelector('.notification-badge');
            if (badge) badge.remove();
            // Remove unread styling
            document.querySelectorAll('.notification-item.unread').forEach(el => {
                el.classList.remove('unread');
            });
        }
    })
    .catch(err => console.error('Error marking notifications:', err));
}

/**
 * Client-side table search filter
 */
function filterTable(inputId, tableId) {
    const filter = document.getElementById(inputId).value.toLowerCase();
    const table = document.getElementById(tableId);
    const rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        const cells = rows[i].getElementsByTagName('td');
        let match = false;
        for (let j = 0; j < cells.length; j++) {
            if (cells[j].textContent.toLowerCase().includes(filter)) {
                match = true;
                break;
            }
        }
        rows[i].style.display = match ? '' : 'none';
    }
}

/**
 * Confirm delete action
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Close alert after 5 seconds
 */
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const closeBtn = alert.querySelector('.btn-close');
            if (closeBtn) closeBtn.click();
        }, 5000);
    });
});
