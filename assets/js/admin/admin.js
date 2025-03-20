// Import styles
import '../../styles/admin/admin.scss';

// Admin panel functionality
document.addEventListener('DOMContentLoaded', function() {
    // Активиране на подменюто, ако текущият път съвпада с някой от елементите в подменюто
    const currentPath = window.location.pathname;
    const subMenuItems = document.querySelectorAll('.sidebar .nav-item ul .nav-link');
    
    subMenuItems.forEach(item => {
        if (item.getAttribute('href') === currentPath) {
            const parentItem = item.closest('.nav-item');
            if (parentItem) {
                parentItem.classList.add('active');
            }
        }
    });
    
    // Показване на подменюто при клик върху родителския елемент
    const menuItems = document.querySelectorAll('.sidebar .nav-item > .nav-link');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            const hasSubmenu = this.nextElementSibling && this.nextElementSibling.tagName === 'UL';
            
            if (hasSubmenu) {
                e.preventDefault();
                const parentItem = this.parentElement;
                parentItem.classList.toggle('active');
            }
        });
    });

    // Автоматично затваряне на известията след определено време
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const closeButton = alert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.click();
            }
        }, 5000);
    });
}); 