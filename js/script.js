// Enable Bootstrap tooltips
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Auto-hide alerts after 5 seconds
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
});

// Add active class to current navigation item
$(document).ready(function() {
    var currentLocation = window.location.pathname;
    $('.nav-link').each(function() {
        var link = $(this).attr('href');
        if (currentLocation === link) {
            $(this).addClass('active');
        }
    });
});

// Form validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    }, false);
})();

// Confirm delete actions
function confirmDelete(event) {
    if (!confirm('Сигурни ли сте, че искате да изтриете този запис?')) {
        event.preventDefault();
    }
}

// Handle AJAX form submissions
function submitForm(formId, successCallback) {
    $(formId).submit(function(e) {
        e.preventDefault();
        $.ajax({
            type: $(this).attr('method'),
            url: $(this).attr('action'),
            data: $(this).serialize(),
            success: function(response) {
                if (typeof successCallback === 'function') {
                    successCallback(response);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                alert('Възникна грешка при обработката на заявката.');
            }
        });
    });
}

// Handle property status toggle
document.addEventListener('DOMContentLoaded', function() {
    // Инициализираме всички toast елементи
    var toastElList = [].slice.call(document.querySelectorAll('.toast'));
    var toastList = toastElList.map(function(toastEl) {
        return new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 3000
        });
    });

    // Обработка на бутона за активиране/деактивиране
    $(document).on('change', '.toggle-active', function(e) {
        const button = $(this);
        const propertyId = button.attr('data-property-id');
        const isActive = button.prop('checked');
        const token = $('meta[name="csrf-token"]').attr('content');

        console.log('Toggle button clicked:', {
            propertyId: propertyId,
            isActive: isActive,
            hasToken: !!token
        });

        if (!propertyId) {
            console.error('Missing property ID');
            button.prop('checked', !isActive);
            alert('Грешка: Липсва ID на имота');
            return;
        }

        $.ajax({
            url: `/admin/properties/${propertyId}/toggle-active`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': token,
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                console.log('Success response:', response);
                if (response.success) {
                    // Показваме съобщение
                    $('#toast .toast-body').text(response.message);
                    var toast = bootstrap.Toast.getInstance($('#toast')[0]);
                    if (!toast) {
                        toast = new bootstrap.Toast($('#toast')[0]);
                    }
                    toast.show();
                } else {
                    console.error('Server returned success: false');
                    button.prop('checked', !isActive);
                    alert('Възникна грешка при промяна на статуса');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', {
                    status: status,
                    error: error,
                    response: xhr.responseText,
                    url: `/admin/properties/${propertyId}/toggle-active`,
                    headers: {
                        'X-CSRF-TOKEN': token ? 'present' : 'missing',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                button.prop('checked', !isActive);
                alert('Възникна грешка при промяна на статуса. Моля, проверете конзолата за повече информация.');
            }
        });
    });
}); 