document.addEventListener('DOMContentLoaded', function() {
    
    const surveyForm = document.getElementById('formKhaoSat');
    const storageKey = `survey_progress_{{ $dotKhaoSat->id }}`; 

    if (!surveyForm) return;

    function saveProgress() {
        const formData = new FormData(surveyForm);
        const data = {};
        
        formData.forEach((value, key) => {
            if (key === '_token' || key === '_submission_token') {
                return; // Bỏ qua token vì sẽ được làm mới
            }
            if (key.endsWith('[]')) {
                const cleanKey = key.slice(0, -2);
                if (!data[cleanKey]) {
                    data[cleanKey] = [];
                }
                data[cleanKey].push(value);
            } else {
                data[key] = value;
            }
        });
        
        try {
            if (Object.keys(data).length > 0) {
                localStorage.setItem(storageKey, JSON.stringify(data));
                console.log('Survey progress saved.');
            }
        } catch (e) {
            console.error('Could not save progress to LocalStorage.', e);
        }
    }

    // --- HÀM TẢI LẠI DỮ LIỆU ---
    function loadProgress() {
        const savedData = localStorage.getItem(storageKey);
        
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                console.log('Loading saved progress:', data);
                
                // Lặp qua dữ liệu đã lưu và điền vào form
                for (const key in data) {
                    const value = data[key];
                    const elements = surveyForm.elements[key];
                    
                    if (!elements) continue;

                    if (Array.isArray(value)) { 
                        elements.forEach(el => {
                            if (value.includes(el.value)) {
                                el.checked = true;
                            }
                        });
                    } else if (elements.type === 'radio') {
                        elements.forEach(el => {
                            if (el.value === value) {
                                el.checked = true;
                            }
                        });
                    } else {
                        elements.value = value;
                    }
                }
                if (typeof updateProgress === 'function') {
                    updateProgress();
                }
                
            } catch (e) {
                console.error('Could not load progress from LocalStorage.', e);
            }
        }
    }

    // --- HÀM XÓA DỮ LIỆU ---
    function clearProgress() {
        localStorage.removeItem(storageKey);
        console.log('Survey progress cleared.');
    }

    loadProgress();

    surveyForm.addEventListener('input', saveProgress);
    
    surveyForm.addEventListener('submit', function(e) {
        // Ngăn chặn hành vi submit mặc định của form
        e.preventDefault();
        
        const submitBtn = document.getElementById('submitBtn');
        
        if (submitBtn.disabled) {
            return;
        }

        if (!surveyForm.checkValidity()) {
            surveyForm.reportValidity(); 
            
            surveyForm.classList.add('was-validated'); 
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Đang gửi...
        `;
        
        $.ajax({
            url: surveyForm.action, // Lấy URL từ thuộc tính action của form
            method: surveyForm.method, // Lấy method từ thuộc tính method của form (POST)
            data: $(surveyForm).serialize(), // Serialize toàn bộ dữ liệu form thành chuỗi
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    clearProgress(); 
                    
                    window.location.href = response.redirect;
                } else {
                    alert(response.message || 'Có lỗi xảy ra, vui lòng thử lại.');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-send"></i> Gửi khảo sát';
                }
            },
            error: function(xhr) {
                let errorMessage = 'Đã có lỗi không mong muốn xảy ra.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMessage = 'Phiên làm việc đã hết hạn, vui lòng tải lại trang.';
                }
                
                alert(errorMessage);
                
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-send"></i> Gửi khảo sát';
            }
        });
    });
});