// Modern Industrial Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('mainNavbar');
    
    // Navbar scroll effect
    if (navbar) {
        let lastScrollTop = 0;
        let isScrolling = false;
        
        window.addEventListener('scroll', function() {
            if (!isScrolling) {
                window.requestAnimationFrame(function() {
                    const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                    
                    // Add scrolled class when scrolling down
                    if (scrollTop > 100) {
                        navbar.classList.add('scrolled');
                    } else {
                        navbar.classList.remove('scrolled');
                    }
                    
                    lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
                    isScrolling = false;
                });
                isScrolling = true;
            }
        });
    }
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                const navbarHeight = navbar ? navbar.offsetHeight : 0;
                const targetPosition = target.offsetTop - navbarHeight - 20;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Mobile menu auto-close on link click
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (navbarCollapse && navbarCollapse.classList.contains('show')) {
                const bsCollapse = bootstrap.Collapse.getInstance(navbarCollapse);
                if (bsCollapse) {
                    bsCollapse.hide();
                }
            }
        });
    });
    
    // Enhanced dropdown interactions
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Add hover effects for desktop
            if (window.innerWidth > 991) {
                dropdown.addEventListener('mouseenter', () => {
                    toggle.classList.add('show');
                    menu.classList.add('show');
                });
                
                dropdown.addEventListener('mouseleave', () => {
                    toggle.classList.remove('show');
                    menu.classList.remove('show');
                });
            }
        }
    });
    
    // Add loading animation to navigation links
    const mainNavLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    mainNavLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only add loading if it's not the current page
            if (!this.classList.contains('active') && this.href && !this.href.includes('#')) {
                this.style.opacity = '0.6';
                this.style.pointerEvents = 'none';
                
                // Create loading indicator
                const loader = document.createElement('span');
                loader.className = 'spinner-border spinner-border-sm ms-2';
                loader.setAttribute('role', 'status');
                loader.setAttribute('aria-hidden', 'true');
                this.appendChild(loader);
            }
        });
    });
});
