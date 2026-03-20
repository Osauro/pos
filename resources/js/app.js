import './bootstrap';
import Swal from 'sweetalert2';

// Make Swal available globally
window.Swal = Swal;

// Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-hidden');
            overlay.classList.toggle('active');
        });
    }

    // Profile Toggle
    const profileToggle = document.getElementById('profileToggle');
    const profilebar = document.getElementById('profilebar');
    const profileClose = document.getElementById('profileClose');

    if (profileToggle && profilebar) {
        profileToggle.addEventListener('click', function() {
            profilebar.classList.toggle('profilebar-hidden');
            overlay.classList.toggle('active');
        });
    }

    if (profileClose) {
        profileClose.addEventListener('click', function() {
            profilebar.classList.add('profilebar-hidden');
            overlay.classList.remove('active');
        });
    }

    // Overlay Click
    if (overlay) {
        overlay.addEventListener('click', function() {
            if (sidebar) sidebar.classList.add('sidebar-hidden');
            if (profilebar) profilebar.classList.add('profilebar-hidden');
            overlay.classList.remove('active');
        });
    }

    // Escape key to close sidebars
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (sidebar) sidebar.classList.add('sidebar-hidden');
            if (profilebar) profilebar.classList.add('profilebar-hidden');
            if (overlay) overlay.classList.remove('active');
        }
    });
});

// SweetAlert2 Livewire Integration
document.addEventListener('livewire:init', () => {
    // Escuchar evento swal
    Livewire.on('swal', (data) => {
        Swal.fire(data[0]);
    });

    // Escuchar evento swal:confirm
    Livewire.on('swal:confirm', (data) => {
        const config = data[0];
        const eventName = config.eventName;
        delete config.eventName;

        Swal.fire(config).then((result) => {
            if (result.isConfirmed) {
                Livewire.dispatch(eventName);
            }
        });
    });

    // Escuchar evento swal:loading
    Livewire.on('swal:loading', (data) => {
        Swal.fire({
            ...data[0],
            didOpen: () => {
                Swal.showLoading();
            }
        });
    });

    // Escuchar evento swal:close
    Livewire.on('swal:close', () => {
        Swal.close();
});

// Pagination Functions
window.previousPage = function() {
    const currentPageInput = document.getElementById('currentPageInput');
    if (currentPageInput) {
        const currentPage = parseInt(currentPageInput.value);
        if (currentPage > 1) {
            goToPage(currentPage - 1);
        }
    }
}

window.nextPage = function() {
    const currentPageInput = document.getElementById('currentPageInput');
    const totalPagesInput = document.getElementById('totalPagesInput');
    if (currentPageInput && totalPagesInput) {
        const currentPage = parseInt(currentPageInput.value);
        const totalPages = parseInt(totalPagesInput.value);
        if (currentPage < totalPages) {
            goToPage(currentPage + 1);
        }
    }
}

window.goToPage = function(page) {
    const url = new URL(window.location.href);
    url.searchParams.set('page', page);
    window.location.href = url.toString();
}

// Modal Functions
window.openModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

window.closeModal = function(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on backdrop click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal') && e.target.classList.contains('active')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Form Validation Helper
window.validateForm = function(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;

    let isValid = true;
    const inputs = form.querySelectorAll('[required]');

    inputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('error');
            isValid = false;
        } else {
            input.classList.remove('error');
        }
    });

    return isValid;
}

// Remove error class on input
document.addEventListener('input', function(e) {
    if (e.target.classList.contains('error')) {
        e.target.classList.remove('error');
    }
});
