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

// Ajax setup với xử lý đặc biệt cho iOS WebKit
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
        'X-Requested-With': 'XMLHttpRequest'
    },
    beforeSend: function (xhr, settings) {
        // Đảm bảo CSRF token được gửi đúng cách cho iOS WebKit
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        if (csrfToken) {
            xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
        }

        // Thêm X-XSRF-TOKEN header cho iOS WebKit
        const xsrfToken = $('meta[name="csrf-token"]').attr('content');
        if (xsrfToken) {
            xhr.setRequestHeader('X-XSRF-TOKEN', xsrfToken);
        }
    }
});

// Refresh CSRF token
function refreshCsrfToken() {
    $.get('/refresh-csrf-token').done(function (data) {
        $('meta[name="csrf-token"]').attr('content', data.csrf_token);
        // Cập nhật tất cả form có _token input
        $('input[name="_token"]').val(data.csrf_token);
    });
}

// Handle ajax errors
$(document).ajaxError(function (event, jqxhr, settings, thrownError) {
    hideLoading();
    if (jqxhr.status === 401) {
        alert('Phiên làm việc đã hết hạn. Vui lòng đăng nhập lại.');
        window.location.href = '/login';
    } else if (jqxhr.status === 403) {
        alert('Bạn không có quyền thực hiện thao tác này.');
    } else if (jqxhr.status === 419) {
        // Xử lý lỗi CSRF token mismatch (đặc biệt cho iOS WebKit)
        alert('Phiên làm việc đã hết hạn. Vui lòng tải lại trang và thử lại.');
        // Tự động reload trang để lấy CSRF token mới
        setTimeout(function () {
            window.location.reload();
        }, 2000);
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