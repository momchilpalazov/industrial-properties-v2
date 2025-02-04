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