/**
 * Функционалност за управление на характеристиките на имоти
 */

export function initializeFeatures() {
    const featuresCollection = document.querySelector('.features-collection');
    const addFeatureButton = document.querySelector('.add-feature');
    
    if (!featuresCollection || !addFeatureButton) {
        console.error('Features components not found!');
        return;
    }
    
    // Начален индекс за нови характеристики
    let featureIndex = featuresCollection.children.length;

    // Добавяне на нова характеристика
    addFeatureButton.addEventListener('click', function() {
        const prototype = featuresCollection.dataset.prototype;
        const newFeature = prototype.replace(/__name__/g, featureIndex);
        const li = document.createElement('li');
        li.className = 'feature-item';
        li.innerHTML = newFeature + 
            '<i class="bi bi-x-circle remove-feature"></i>';
        featuresCollection.appendChild(li);
        featureIndex++;
    });

    // Премахване на характеристика
    featuresCollection.addEventListener('click', function(e) {
        if (e.target.matches('.remove-feature')) {
            e.target.closest('.feature-item').remove();
        }
    });
} 