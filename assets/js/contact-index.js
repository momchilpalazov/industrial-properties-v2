// Importing general styles
import '../styles/components/contact/index.scss';

document.addEventListener('DOMContentLoaded', function() {
    // Fix for Here Maps canvas warning
    // Override the createCanvas method to set the willReadFrequently attribute
    if (window.H && window.H.map && window.H.map.render && window.H.map.render.Canvas) {
        const originalCreateCanvas = window.H.map.render.Canvas.prototype.createCanvas;
        window.H.map.render.Canvas.prototype.createCanvas = function(width, height) {
            const canvas = originalCreateCanvas.call(this, width, height);
            canvas.getContext('2d', { willReadFrequently: true });
            return canvas;
        };
    }

    // Initialize maps when DOM is loaded
    initializeMap();
    
    // Initialize animations for sections
    initializeAnimations();
    
    // Initialize contact form
    initializeContactForm();
});

function initializeMap() {
    const mapContainer = document.getElementById('contact-map');
    
    if (!mapContainer) return;
    
    const mapDataEl = document.getElementById('map-data');
    
    if (!mapDataEl) return;
    
    const mapData = JSON.parse(mapDataEl.dataset.map);
    const apiKey = mapDataEl.dataset.apiKey;
    
    // Initialize Here Map
    const platform = new H.service.Platform({
        'apikey': apiKey
    });
    
    // Get default layers
    const defaultLayers = platform.createDefaultLayers();
    
    // Create map instance
    const map = new H.Map(
        mapContainer,
        defaultLayers.vector.normal.map,
        {
            zoom: 14,
            center: { lat: mapData.latitude, lng: mapData.longitude }
        }
    );
    
    // Add interactive behavior to map
    const behavior = new H.mapevents.Behavior(new H.mapevents.MapEvents(map));
    
    // Add UI components
    const ui = H.ui.UI.createDefault(map, defaultLayers);
    
    // Create marker with company info bubble
    const marker = new H.map.Marker({ lat: mapData.latitude, lng: mapData.longitude });
    
    // Create info bubble for marker
    const contentString = `
        <div class="info-window p-3">
            <h5 class="mb-2">${mapData.companyName}</h5>
            <p class="mb-2"><i class="fas fa-map-marker-alt text-primary me-2"></i>${mapData.address}</p>
            <p class="mb-2"><i class="fas fa-phone text-primary me-2"></i>${mapData.phone}</p>
            <p class="mb-0"><i class="fas fa-envelope text-primary me-2"></i>${mapData.email}</p>
        </div>
    `;
    
    const bubble = new H.ui.InfoBubble(
        { lat: mapData.latitude, lng: mapData.longitude },
        { content: contentString }
    );
    
    // Add marker to map
    map.addObject(marker);
    
    // Open info bubble when marker is clicked
    map.addEventListener('tap', function(evt) {
        const target = evt.target;
        if (target === marker) {
            ui.addBubble(bubble);
        }
    });
    
    // Resize map when window is resized
    window.addEventListener('resize', function() {
        map.getViewPort().resize();
    });
}

function initializeAnimations() {
    const sections = document.querySelectorAll('.animated-section');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.1
    });
    
    sections.forEach(section => {
        observer.observe(section);
    });
}

function initializeContactForm() {
    const form = document.getElementById('contactForm');
    
    if (!form) return;
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            Изпращане...
        `;
        
        // Send form data
        fetch(form.dataset.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset loading state
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            
            // Show success or error message
            showAlert(data.success ? 'success' : 'danger', data.message);
            
            // Reset form on success
            if (data.success) {
                form.reset();
            }
        })
        .catch(error => {
            console.error('Error submitting form:', error);
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
            showAlert('danger', 'Възникна грешка при изпращането. Моля, опитайте отново.');
        });
    });
}

function showAlert(type, message) {
    // Create alert element
    const alertEl = document.createElement('div');
    alertEl.className = `alert alert-${type} alert-float alert-dismissible fade show`;
    alertEl.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    
    // Add to document
    document.body.appendChild(alertEl);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        alertEl.classList.remove('show');
        setTimeout(() => alertEl.remove(), 300);
    }, 5000);
} 