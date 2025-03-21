/**
 * Администраторски интерфейс за създаване на нови потребители
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
        
        // Добавяне на индикатори за задължителни полета
        const requiredInputs = form.querySelectorAll('[required]');
        requiredInputs.forEach(input => {
            const label = input.closest('.mb-3').querySelector('label');
            if (label) {
                label.classList.add('required-field-label');
            }
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
            
            // Добавяне на допълнителна подсказка за силна парола
            const passwordHelp = document.createElement('div');
            passwordHelp.className = 'form-text text-muted mt-1';
            passwordHelp.innerHTML = '<small><i class="bi bi-info-circle"></i> Използвайте комбинация от букви, цифри и специални символи за силна парола.</small>';
            wrapper.insertAdjacentElement('afterend', passwordHelp);
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
            const formColumn = rolesElement.closest('.col-md-6');
            if (formColumn) {
                formColumn.insertBefore(hubContainer, formColumn.firstChild);
            }
        }
        
        // Добавяме информативен текст за новия потребител
        const formSection = document.querySelector('.form-section');
        if (formSection) {
            const prompt = document.createElement('div');
            prompt.className = 'create-user-prompt';
            prompt.innerHTML = '<i class="bi bi-info-circle"></i> Създаване на нов потребител в системата. След като попълните необходимите данни, потребителят ще бъде активен и ще може да влиза в системата.';
            
            formSection.insertBefore(prompt, formSection.firstChild);
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