// Modern Industrial Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('mainNavbar');    // Wait for Bootstrap to be available, then initialize components
    function initializeBootstrapComponents() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Collapse) {
            // Initialize navbar collapse ONLY (not all collapses to avoid conflicts)
            const navbarCollapseEl = document.querySelector('.navbar-collapse');
            let navbarCollapseInstance = null;
            if (navbarCollapseEl && !bootstrap.Collapse.getInstance(navbarCollapseEl)) {
                navbarCollapseInstance = new bootstrap.Collapse(navbarCollapseEl, {
                    toggle: false
                });
            }
            
            console.log('Bootstrap components initialized:', {
                navbarCollapse: !!navbarCollapseInstance
            });
        } else {
            // Retry after a short delay if Bootstrap is not ready
            setTimeout(initializeBootstrapComponents, 100);
        }
    }
    
    initializeBootstrapComponents();
    
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
    }    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            // Skip empty anchors or just '#'
            if (!href || href === '#') {
                return;
            }
            
            const target = document.querySelector(href);
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
    });    // Mobile menu auto-close on link click (except dropdown)
    const navbarCollapse = document.querySelector('.navbar-collapse');
    const navbarToggler = document.querySelector('.navbar-toggler');
    const navLinks = document.querySelectorAll('.navbar-nav .nav-link:not(.dropdown-toggle)');
    
    // Debug: Check if elements exist
    console.log('Navbar elements found:', {
        collapse: !!navbarCollapse,
        toggler: !!navbarToggler,
        links: navLinks.length
    });
    
    // Fix navbar toggler functionality - remove automatic Bootstrap handling and do it manually
    if (navbarToggler && navbarCollapse) {
        // Remove Bootstrap's automatic behavior
        navbarToggler.removeAttribute('data-bs-toggle');
        navbarToggler.removeAttribute('data-bs-target');
        
        navbarToggler.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isExpanded = this.getAttribute('aria-expanded') === 'true';
            const collapseInstance = bootstrap.Collapse.getInstance(navbarCollapse);
            
            console.log('Navbar toggler clicked - Manual handling:', {
                isExpanded: isExpanded,
                hasCollapseShow: navbarCollapse.classList.contains('show'),
                collapseInstance: !!collapseInstance
            });
            
            if (isExpanded || navbarCollapse.classList.contains('show')) {
                // Close the menu
                this.setAttribute('aria-expanded', 'false');
                this.classList.add('collapsed');
                if (collapseInstance) {
                    collapseInstance.hide();
                } else {
                    navbarCollapse.classList.remove('show');
                }
            } else {
                // Open the menu
                this.setAttribute('aria-expanded', 'true');
                this.classList.remove('collapsed');
                if (collapseInstance) {
                    collapseInstance.show();
                } else {
                    navbarCollapse.classList.add('show');
                }
            }
        });
    }
    
    // Add minimal collapse event listeners (without duplicates)
    if (navbarCollapse) {
        // Remove existing listeners to prevent duplicates
        navbarCollapse.removeEventListener('show.bs.collapse', function() {});
        navbarCollapse.removeEventListener('shown.bs.collapse', function() {});
        navbarCollapse.removeEventListener('hide.bs.collapse', function() {});
        navbarCollapse.removeEventListener('hidden.bs.collapse', function() {});
        
        navbarCollapse.addEventListener('shown.bs.collapse', function () {
            console.log('Navbar opened');
            if (navbarToggler) {
                navbarToggler.setAttribute('aria-expanded', 'true');
                navbarToggler.classList.remove('collapsed');
            }
        });
        
        navbarCollapse.addEventListener('hidden.bs.collapse', function () {
            console.log('Navbar closed');
            if (navbarToggler) {
                navbarToggler.setAttribute('aria-expanded', 'false');
                navbarToggler.classList.add('collapsed');
            }
        });
    }
    
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
    
    // Special handling for mobile dropdown functionality
    if (window.innerWidth <= 991) {
        // Listen for navbar collapse events to handle dropdown state
        if (navbarCollapse) {
            navbarCollapse.addEventListener('hidden.bs.collapse', function () {
                // Close any open dropdowns when navbar collapses
                const openDropdowns = document.querySelectorAll('.dropdown-menu.show');
                openDropdowns.forEach(dropdown => {
                    const toggleButton = dropdown.parentElement.querySelector('.dropdown-toggle');
                    if (toggleButton) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(toggleButton);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                });
            });
        }
    }    // Enhanced dropdown interactions - Fixed for mobile compatibility
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('.dropdown-toggle');
        const menu = dropdown.querySelector('.dropdown-menu');
        
        if (toggle && menu) {
            // Initialize Bootstrap dropdown instance only if not already initialized
            let bsDropdown = bootstrap.Dropdown.getInstance(toggle);
            if (!bsDropdown) {
                bsDropdown = new bootstrap.Dropdown(toggle);
            }
            
            // Desktop hover effects only
            if (window.innerWidth > 991) {
                dropdown.addEventListener('mouseenter', () => {
                    bsDropdown.show();
                });
                
                dropdown.addEventListener('mouseleave', () => {
                    bsDropdown.hide();
                });
            } else {
                // Mobile: Remove conflicting event handlers and let Bootstrap handle click events naturally
                // Ensure the dropdown works correctly in mobile by not preventing the default behavior
                toggle.addEventListener('click', function(e) {
                    // Let Bootstrap handle the click naturally, just add debug logging
                    console.log('Mobile dropdown clicked:', {
                        isShown: menu.classList.contains('show'),
                        toggleText: toggle.textContent.trim()
                    });
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
