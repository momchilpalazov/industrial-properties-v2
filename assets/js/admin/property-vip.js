document.addEventListener('DOMContentLoaded', function() {
    const vipCheckbox = document.querySelector('[data-toggle="vip-options"]');
    const vipOptionsGroup = document.querySelector('.vip-options-group');

    if (vipCheckbox && vipOptionsGroup) {
        // Първоначално скриване/показване
        vipOptionsGroup.style.display = vipCheckbox.checked ? 'block' : 'none';

        // Слушател за промяна
        vipCheckbox.addEventListener('change', function() {
            vipOptionsGroup.style.display = this.checked ? 'block' : 'none';
        });
    }
}); 