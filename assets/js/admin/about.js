document.addEventListener('DOMContentLoaded', function() {
    // Инициализация на CKEditor, ако е наличен глобално
    if (typeof ClassicEditor !== 'undefined') {
        const editors = ['content_bg', 'content_en', 'content_de', 'content_ru'];
        
        editors.forEach(function(editorId) {
            const element = document.querySelector('#' + editorId);
            if (element) {
                ClassicEditor
                    .create(element, {
                        toolbar: [
                            'heading',
                            '|',
                            'bold',
                            'italic',
                            'link',
                            'bulletedList',
                            'numberedList',
                            'blockQuote',
                            '|',
                            'undo',
                            'redo'
                        ],
                        heading: {
                            options: [
                                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
                                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
                                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' }
                            ]
                        },
                        language: editorId.slice(-2).toLowerCase(),
                        licenseKey: '0', // Лицензионен ключ за безплатна версия на CKEditor
                    })
                    .catch(error => {
                        console.error(`Грешка при инициализиране на CKEditor за ${editorId}:`, error);
                    });
            }
        });
    } else {
        console.warn('CKEditor не е наличен. Моля, добавете го във вашия шаблон.');
    }

    // Функции за изтриване на снимки
    window.deleteCompanyImage = function(button) {
        const preview = button.closest('.image-preview');
        const deleteInput = preview.querySelector('input[name="delete_company_image"]');
        deleteInput.value = "1";
        preview.style.display = 'none';
    };

    window.deleteTeamMemberImage = function(button, key) {
        const preview = button.closest('.image-preview');
        const deleteInput = preview.querySelector(`input[name="delete_team_image[${key}]"]`);
        deleteInput.value = "1";
        preview.style.display = 'none';
    };
}); 