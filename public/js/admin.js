// Admin Panel JavaScript

// Toggle sidebar
document.getElementById('sidebarToggle')?.addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('toggled');
});

document.getElementById('sidebarToggleTop')?.addEventListener('click', function () {
    document.querySelector('.sidebar').classList.toggle('toggled');
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});

// Confirm delete
function confirmDelete(message = 'Bạn có chắc chắn muốn xóa?') {
    return confirm(message);
}

// Format number
function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d{3})+(?!\d))/g, '$1,');
}

// Show loading
function showLoading() {
    Swal.fire({
        title: 'Đang xử lý...',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
}

// Hide loading
function hideLoading() {
    Swal.close();
}
