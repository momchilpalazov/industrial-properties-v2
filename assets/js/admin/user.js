/**
 * Администраторски интерфейс за управление на потребители
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const userCards = document.querySelectorAll('.user-card');
            
            userCards.forEach(card => {
                const name = card.querySelector('.card-title').textContent.toLowerCase();
                const email = card.querySelector('.card-subtitle').textContent.toLowerCase();
                const roles = Array.from(card.querySelectorAll('.user-roles .badge'))
                                  .map(badge => badge.textContent.toLowerCase());
                
                if (name.includes(searchValue) || 
                    email.includes(searchValue) || 
                    roles.some(role => role.includes(searchValue))) {
                    card.closest('.col-md-6').style.display = '';
                } else {
                    card.closest('.col-md-6').style.display = 'none';
                }
            });
        });
    }
}); 