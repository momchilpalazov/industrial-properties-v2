/* global pannellum */
import 'pannellum';

document.addEventListener('DOMContentLoaded', function() {
    const viewers360 = document.querySelectorAll('.property-360-viewer');

    viewers360.forEach(container => {
        const imageUrl = container.dataset.image;
        
        const viewer = pannellum.viewer(container, {
            type: 'equirectangular',
            panorama: imageUrl,
            autoLoad: true,
            autoRotate: -2,
            compass: true,
            showZoomCtrl: true,
            showFullscreenCtrl: true,
            showControls: true,
            keyboardZoom: true,
            mouseZoom: true,
            draggable: true,
            friction: 0.15,
            touchPanSpeedCoeffFactor: 1,
            minHfov: 50,
            maxHfov: 120,
            hfov: 100,
            yaw: 0,
            pitch: 0,
            minPitch: -90,
            maxPitch: 90,
            minYaw: -180,
            maxYaw: 180,
            autoRotateInactivityDelay: 3000,
            autoRotateStopDelay: 3000,
            sceneFadeDuration: 1000,
            compass: true,
            northOffset: 0,
            orientationOnByDefault: false,
            hotSpots: [],
            backgroundColor: [245, 245, 245],
            strings: {
                loadButtonLabel: "Зареди",
                loadingLabel: "Зареждане...",
                bylineLabel: "",
                noPanoramaError: "Не е предоставено 360° изображение.",
                fileAccessError: "Грешка при зареждане на изображението.",
                malformedURLError: "Грешка в URL адреса на изображението.",
                iOS8WebGLError: "Поради проблем с WebGL имплементацията в iOS 8, вашето устройство не поддържа тази функционалност.",
                genericWebGLError: "Вашият браузър не поддържа WebGL.",
                textureSizeError: "Изображението е твърде голямо за вашето устройство.",
                unknownError: "Възникна неочаквана грешка."
            }
        });

        container.classList.add('pannellum-viewer');

        const rotateControl = document.createElement('div');
        rotateControl.className = 'pnlm-rotate-control';
        rotateControl.style.cssText = `
            position: absolute;
            top: 10px;
            right: 60px;
            width: 44px;
            height: 44px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
            z-index: 1000;
        `;

        const rotateIcon = document.createElement('i');
        rotateIcon.className = 'bi bi-arrow-repeat';
        rotateIcon.style.cssText = `
            font-size: 1.2rem;
            color: #333;
            transition: all 0.3s ease;
        `;

        rotateControl.appendChild(rotateIcon);
        container.appendChild(rotateControl);

        let isRotating = true;
        rotateControl.addEventListener('click', () => {
            isRotating = !isRotating;
            const config = viewer.getConfig();
            config.autoRotate = isRotating ? -2 : 0;
            viewer.destroy();
            pannellum.viewer(container, config);
            
            rotateControl.style.backgroundColor = isRotating ? 'rgba(255, 255, 255, 0.9)' : 'rgba(13, 110, 253, 0.9)';
            rotateIcon.style.color = isRotating ? '#333' : '#fff';
        });

        rotateControl.addEventListener('mouseenter', () => {
            rotateControl.style.transform = 'translateY(-2px)';
            rotateControl.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.2)';
        });

        rotateControl.addEventListener('mouseleave', () => {
            rotateControl.style.transform = 'translateY(0)';
            rotateControl.style.boxShadow = '0 2px 6px rgba(0, 0, 0, 0.15)';
        });
    });
}); 