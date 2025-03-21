/**
 * Администраторски интерфейс за редактиране на потребители
 */
document.addEventListener('DOMContentLoaded', function() {
    // Добавяне на валидация на формата
    const form = document.querySelector('.needs-validation');
    
    if (form) {
        // Слушател за събитие при submit
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
                
                // Намиране на първия невалиден input и фокусиране върху него
                const firstInvalid = form.querySelector(':invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    
                    // Прескролване до първия невалиден input с анимация
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
            
            form.classList.add('was-validated');
        });
        
        // Подобрена UX за полето за парола
        const passwordField = document.querySelector('input[type="password"]');
        if (passwordField) {
            // Обвиваме полето за парола в допълнителен div за по-добро позициониране
            const wrapper = document.createElement('div');
            wrapper.className = 'password-field-wrapper';
            
            // Заменяме полето за парола с нашия wrapper
            const parentNode = passwordField.parentNode;
            parentNode.insertBefore(wrapper, passwordField);
            wrapper.appendChild(passwordField);
            
            // Създаване на бутон "Покажи парола"
            const toggleBtn = document.createElement('button');
            toggleBtn.type = 'button';
            toggleBtn.className = 'btn btn-sm password-toggle';
            toggleBtn.innerHTML = '<i class="bi bi-eye"></i>';
            
            // Добавяне на бутона във wrapper елемента
            wrapper.appendChild(toggleBtn);
            
            // Функционалност за показване/скриване на паролата
            toggleBtn.addEventListener('click', function() {
                const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordField.setAttribute('type', type);
                
                // Смяна на иконата
                toggleBtn.innerHTML = type === 'password' ? 
                    '<i class="bi bi-eye"></i>' : 
                    '<i class="bi bi-eye-slash"></i>';
            });
        }
        
        // Създаваме hub контейнер за роли и статус
        const rolesElement = document.querySelector('#user_roles').closest('.mb-3');
        const statusElement = document.querySelector('#user_isActive').closest('.form-check');
        
        if (rolesElement && statusElement) {
            // Създаваме hub контейнер
            const hubContainer = document.createElement('div');
            hubContainer.className = 'user-roles-status-hub';
            
            // Създаваме секция за роли
            const rolesSection = document.createElement('div');
            rolesSection.className = 'role-item';
            
            // Създаваме заглавие за ролите
            const rolesTitle = document.createElement('h5');
            rolesTitle.className = 'hub-section-title';
            rolesTitle.innerHTML = '<i class="bi bi-shield-lock"></i> Роли';
            
            // Преместваме роли в секцията
            rolesSection.appendChild(rolesTitle);
            rolesSection.appendChild(rolesElement);
            
            // Създаваме секция за статус
            const statusSection = document.createElement('div');
            statusSection.className = 'status-item';
            
            // Създаваме заглавие за статуса
            const statusTitle = document.createElement('h5');
            statusTitle.className = 'hub-section-title';
            statusTitle.innerHTML = '<i class="bi bi-toggle-on"></i> Статус';
            
            // Преместваме статус в секцията
            statusSection.appendChild(statusTitle);
            statusSection.appendChild(statusElement);
            
            // Добавяме секциите в hub контейнера
            hubContainer.appendChild(rolesSection);
            hubContainer.appendChild(statusSection);
            
            // Намираме къде да поставим hub контейнера
            const infoBlock = document.querySelector('.user-info-block');
            if (infoBlock && infoBlock.parentNode) {
                infoBlock.parentNode.insertBefore(hubContainer, infoBlock);
            }
        }
    }
    
    // Анимация при зареждане на секциите
    const sections = document.querySelectorAll('.form-section');
    if (sections.length) {
        sections.forEach((section, index) => {
            section.style.animationDelay = `${index * 0.1}s`;
        });
    }
}); 