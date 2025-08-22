// Admin Panel JavaScript

// Toggle sidebar
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.sidebar').classList.toggle('toggled');
});

document.getElementById('sidebarToggleTop')?.addEventListener('click', function() {
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

// Ajax setup
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Handle ajax errors
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
    hideLoading();
    if (jqxhr.status === 401) {
        alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
        window.location.href = '/login';
    } else if (jqxhr.status === 403) {
        alert('Bạn không có quyền thực hiện thao tác này.');
    } else if (jqxhr.status === 422) {
        let errors = jqxhr.responseJSON.errors;
        let errorMessage = '';
        for (let field in errors) {
            errorMessage += errors[field].join('\n') + '\n';
        }
        alert(errorMessage);
    } else {
        alert('Có lỗi xảy ra. Vui lòng thử lại.');
    }
});