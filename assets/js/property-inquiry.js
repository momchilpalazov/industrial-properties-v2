// Конфигурация на Toastr нотификациите
const toastrConfig = {
    closeButton: true,
    progressBar: true,
    positionClass: "toast-top-right",
    timeOut: 5000,
    extendedTimeOut: 2000,
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut"
};

// Инициализация на формата за запитвания
const initInquiryForm = () => {
    const form = $('#inquiryForm');
    
    if (!form.length) return;

    // Задаваме конфигурацията на Toastr
    toastr.options = toastrConfig;

    // Обработка на формата
    form.on('submit', function(e) {
        e.preventDefault();
        
        // Добавяме loading състояние
        form.addClass('loading');
        
        // Изпращаме заявката
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: new FormData(this),
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(
                        'Вашето запитване беше изпратено успешно! Ще се свържем с вас възможно най-скоро.',
                        'Успешно изпратено!'
                    );
                    form[0].reset();
                    $('html, body').animate({ scrollTop: 0 }, 'slow');
                } else {
                    toastr.error(
                        response.message || 'Възникна грешка при изпращане на запитването',
                        'Грешка!'
                    );
                }
            },
            error: function(xhr) {
                let errorMessage = 'Възникна грешка при изпращане на запитването';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                toastr.error(errorMessage, 'Грешка!');
            },
            complete: function() {
                form.removeClass('loading');
            }
        });
    });
};

// Инициализация при зареждане на документа
$(document).ready(function() {
    initInquiryForm();
}); 