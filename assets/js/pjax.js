/**
 * Raquel Pawnshop HRIS - PJAX Navigation & Sidebar Persistence
 * This script handles AJAX-based content loading to provide an SPA-like experience
 * and ensures the sidebar maintains its scroll position.
 */

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const pageTitle = document.querySelector('.page-title');

    // Restore sidebar scroll position from localStorage
    const savedScrollTop = localStorage.getItem('sidebar_scroll_top');
    if (savedScrollTop && sidebar) {
        sidebar.scrollTop = parseInt(savedScrollTop, 10);
    }

    // Save sidebar scroll position on scroll
    if (sidebar) {
        sidebar.addEventListener('scroll', function () {
            localStorage.setItem('sidebar_scroll_top', sidebar.scrollTop);
        });
    }

    // Intercept clicks on internal links
    document.addEventListener('click', function (e) {
        const link = e.target.closest('.sidebar-nav a, .nav-link, a[data-pjax]');

        if (link && !link.classList.contains('coming-soon') && !link.hasAttribute('data-no-pjax')) {
            const url = link.getAttribute('href');

            // Check if it's an internal link (not logout, not external)
            if (url && url.startsWith(window.location.origin) || url.startsWith('/') || url.startsWith('..')) {
                // If it's a relative path starting with '..', it's likely fine to intercept
                // But let's check against logout.php explicitly
                if (url.includes('logout.php')) return;

                e.preventDefault();
                loadPage(url);
            }
        }
    });

    // Handle browser back/forward buttons
    window.addEventListener('popstate', function () {
        loadPage(window.location.pathname + window.location.search, false);
    });

    /**
     * Load page content via AJAX
     */
    function loadPage(url, pushState = true) {
        // Show loading state (optional)
        mainContent.style.opacity = '0.5';
        mainContent.style.transition = 'opacity 0.2s';

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                // 1. Update Title and URL
                const newTitle = doc.title;
                document.title = newTitle;
                if (pushState) {
                    history.pushState(null, newTitle, url);
                }

                // 2. Update Main Content
                const newMainContent = doc.querySelector('.main-content');
                if (newMainContent) {
                    // Preserve any flash messages or global containers if necessary
                    mainContent.innerHTML = newMainContent.innerHTML;
                }

                // 3. Update Page Title in Navbar
                const newPageTitle = doc.querySelector('.page-title');
                if (newPageTitle && pageTitle) {
                    pageTitle.textContent = newPageTitle.textContent;
                }

                // 4. Update Sidebar Active Link
                updateSidebarActiveLink(url);

                // 5. Re-initialize Scripts and UI components
                reinitializeComponents(doc);

                // Restore opacity
                mainContent.style.opacity = '1';

                // Scroll main content to top
                window.scrollTo(0, 0);
            })
            .catch(error => {
                console.error('PJAX Error:', error);
                // Fallback to full page reload on error
                window.location.href = url;
            });
    }

    /**
     * Update active class in sidebar
     */
    function updateSidebarActiveLink(url) {
        const links = document.querySelectorAll('.sidebar-nav a');
        const currentPath = new URL(url, window.location.origin).pathname;
        const currentPage = currentPath.split('/').pop();

        links.forEach(link => {
            const linkPath = new URL(link.getAttribute('href'), window.location.origin).pathname;
            const linkPage = linkPath.split('/').pop();

            if (linkPage === currentPage && currentPage !== '') {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });
    }

    /**
     * Re-initialize scripts and UI components after dynamic load
     */
    function reinitializeComponents(doc) {
        // 1. Re-initialize Bootstrap Tooltips/Popovers
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // 2. Execute scripts found in the new content
        const scripts = doc.querySelectorAll('.main-content script');
        scripts.forEach(oldScript => {
            const newScript = document.createElement('script');
            Array.from(oldScript.attributes).forEach(attr => {
                newScript.setAttribute(attr.name, attr.value);
            });
            newScript.appendChild(document.createTextNode(oldScript.innerHTML));
            mainContent.appendChild(newScript);
        });

        // 3. Re-initialize charts if chart contexts exist
        if (typeof Chart !== 'undefined') {
            // This assumes the page has logic to find and init charts
            // Most pages in this app have inline Chart.js code which step 2 handles.
        }

        // 4. Re-bind custom filterTable functions or other main.js logic
        if (typeof window.initDynamicComponents === 'function') {
            window.initDynamicComponents();
        }
    }
});
