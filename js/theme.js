/**
 * YEA Theme JavaScript
 * Young Entrepreneurs Albania WordPress Theme
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initHeaderScroll();
        initCounterAnimation();
        // Keep page content steady: decorative tilt and parallax are disabled.
    });

    /**
     * Header scroll effect
     */
    function initHeaderScroll() {
        const header = document.getElementById('header');
        if (!header) return;

        let lastScroll = 0;
        let ticking = false;

        function updateHeader() {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            lastScroll = currentScroll;
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateHeader);
                ticking = true;
            }
        }, { passive: true });
    }

    /**
     * Counter animation
     */
    function initCounterAnimation() {
        const counters = document.querySelectorAll('.counter[data-target]');
        
        if (counters.length === 0) return;
        if (!('IntersectionObserver' in window)) return;

        const observerOptions = {
            threshold: 0.5
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting && !entry.target.classList.contains('counted')) {
                    animateCounter(entry.target);
                    entry.target.classList.add('counted');
                }
            });
        }, observerOptions);

        counters.forEach(function(counter) {
            observer.observe(counter);
        });
    }

    function animateCounter(element) {
        const target = parseInt(element.getAttribute('data-target'), 10);
        if (isNaN(target)) {
            element.textContent = element.getAttribute('data-target');
            return;
        }

        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            element.textContent = target;
            return;
        }
        const duration = 2000;
        const startTime = performance.now();
        const startValue = 0;

        function updateCounter(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            // Easing function (ease-out)
            const easeOut = 1 - Math.pow(1 - progress, 3);
            const currentValue = Math.floor(startValue + (target - startValue) * easeOut);
            
            element.textContent = currentValue;
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target;
            }
        }

        requestAnimationFrame(updateCounter);
    }

    // Anchor navigation uses the browser's native history and focus behavior.
    // CSS controls smooth scrolling and respects reduced-motion preferences.

    /**
     * Parallax effect for hero cards
     */
    function initParallax() {
        const heroVisual = document.querySelector('.hero-visual');
        if (!heroVisual) return;

        // Only enable on non-touch devices
        if (window.matchMedia('(pointer: coarse)').matches) return;

        let ticking = false;

        function updateParallax() {
            const scrolled = window.pageYOffset;
            const parallaxElements = heroVisual.querySelectorAll('.hero-card-lg, .hero-card-sm, .hero-badge, .floating-chip');
            
            parallaxElements.forEach(function(el, index) {
                const speed = 0.5 + (index * 0.1);
                const yPos = scrolled * speed * 0.1;
                el.style.transform = 'translateY(' + yPos + 'px)';
            });
            
            ticking = false;
        }

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(updateParallax);
                ticking = true;
            }
        }, { passive: true });

        // 3D tilt effect on mouse move
        heroVisual.addEventListener('mousemove', function(e) {
            if (window.innerWidth <= 768) return;
            
            const rect = heroVisual.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 50;
            const rotateY = (centerX - x) / 50;
            
            heroVisual.style.transform = 'perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg)';
        });
        
        heroVisual.addEventListener('mouseleave', function() {
            heroVisual.style.transform = 'perspective(1000px) rotateX(0) rotateY(0)';
        });
    }

    /**
     * Program cards tilt effect
     */
    function initProgramCards() {
        const programCards = document.querySelectorAll('.program-card');
        if (programCards.length === 0) return;
        if (window.matchMedia('(pointer: coarse)').matches) return;

        programCards.forEach(function(card) {
            card.addEventListener('mousemove', function(e) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                
                const centerX = rect.width / 2;
                const centerY = rect.height / 2;
                
                const rotateX = (y - centerY) / 20;
                const rotateY = (centerX - x) / 20;
                
                card.style.transform = 'translateY(-10px) perspective(1000px) rotateX(' + rotateX + 'deg) rotateY(' + rotateY + 'deg) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                card.style.transform = '';
            });
        });
    }

})();


(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        const legacySelectors = [
            '#generate-slideout-menu',
            '.slideout-navigation.do-overlay',
            '.slideout-overlay',
        ];

        legacySelectors.forEach(function(selector) {
            document.querySelectorAll(selector).forEach(function(element) {
                element.remove();
            });
        });

        document.documentElement.classList.remove('slide-open');
        document.body.classList.remove('slide-open', 'offside-js--init', 'offside-js--interact');

        const header = document.getElementById('header');
        const navigation = header ? header.querySelector('.header-inner > .nav') : null;
        const openButton = navigation ? (navigation.querySelector('.wp-block-navigation__responsive-container-open') || header.querySelector('#menu-toggle')) : null;
        const responsiveContainer = navigation ? navigation.querySelector('.wp-block-navigation__responsive-container') : null;

        if (!header || !navigation || !openButton) return;
        const labels = window.yeaOptions || {};
        let menuDirty = true;
        let inertRegions = [];
        let scrollLocks = [];
        let returnFocus = null;

        function getMenuCandidates(root) {
            if (!root) {
                return [];
            }

            return Array.from(
                root.querySelectorAll('.wp-block-navigation__container, .wp-block-page-list, .wp-block-pages-list, #primary-menu')
            );
        }

        function getMenuLinks(root) {
            if (!root) {
                return [];
            }

            return Array.from(root.querySelectorAll('a')).filter(function(link) {
                return link.getAttribute('href') &&
                    link.textContent.trim() !== '' &&
                    !link.closest('.wp-block-navigation__responsive-container-open') &&
                    !link.closest('.wp-block-navigation__responsive-container-close');
            });
        }

        function getDesktopMenu() {
            const responsiveCandidates = getMenuCandidates(responsiveContainer);
            const primaryCandidates = getMenuCandidates(navigation).filter(function(candidate) {
                return !responsiveContainer || !responsiveContainer.contains(candidate);
            });
            const orderedCandidates = responsiveCandidates.concat(primaryCandidates);

            return orderedCandidates.find(function(candidate) {
                // Core's page-list fallback can wrap a list inside a container.
                // Clone the list that owns the items, not a wrapper whose only
                // child would otherwise be mistaken for a hidden submenu.
                return getMenuLinks(candidate).length > 0 && Array.from(candidate.children).some(function(child) {
                    return child.tagName === 'LI';
                });
            }) || null;
        }

        function buildFallbackMenuFromLinks() {
            const seen = new Set();
            const links = getMenuLinks(navigation);

            if (links.length === 0) {
                return null;
            }

            const menu = document.createElement('ul');
            menu.className = 'wp-block-navigation__container yea-mobile-nav-list';

            links.forEach(function(link) {
                const parentItem = link.closest('.wp-block-navigation-item, .wp-block-pages-list__item');
                const key = link.getAttribute('href') + '|' + link.textContent.trim();

                if (parentItem && parentItem.closest('.wp-block-navigation__submenu-container, .wp-block-page-list ul, .wp-block-pages-list ul')) {
                    return;
                }

                if (seen.has(key)) {
                    return;
                }

                seen.add(key);

                const item = document.createElement('li');
                item.className = 'wp-block-navigation-item';
                item.appendChild(link.cloneNode(true));
                menu.appendChild(item);
            });

            return menu.children.length > 0 ? menu : null;
        }

        function isMobileViewport() {
            return window.innerWidth <= 1024;
        }

        function ensureMobileDrawer() {
            let drawer = header.querySelector('.yea-mobile-drawer');

            if (!drawer) {
                drawer = document.createElement('div');
                drawer.className = 'yea-mobile-drawer';
                drawer.setAttribute('hidden', 'hidden');
                drawer.setAttribute('aria-hidden', 'true');
                drawer.id = 'yea-mobile-navigation';
                drawer.setAttribute('role', 'dialog');
                drawer.setAttribute('aria-modal', 'true');
                drawer.setAttribute('aria-label', labels.menuLabel || 'Navigation');
                drawer.innerHTML = '<div class="container yea-mobile-drawer__inner"></div>';
                header.appendChild(drawer);
            }

            return drawer;
        }

        function sanitizeClonedMenu(menu) {
            if (!menu) {
                return;
            }

            menu.classList.add('yea-mobile-nav-list');
            menu.querySelectorAll('.sub-menu').forEach(function(submenu) {
                submenu.classList.add('wp-block-navigation__submenu-container');
            });
            menu.removeAttribute('id');
            menu.removeAttribute('style');
            menu.removeAttribute('hidden');
            menu.setAttribute('aria-hidden', 'false');

            menu.querySelectorAll('[id]').forEach(function(element) {
                element.removeAttribute('id');
            });

            [menu].concat(Array.from(menu.querySelectorAll('*'))).forEach(function(element) {
                Array.from(element.attributes).forEach(function(attribute) {
                    if (attribute.name.indexOf('data-wp-') === 0) element.removeAttribute(attribute.name);
                });
            });

            menu.querySelectorAll('[style], [hidden]').forEach(function(element) {
                element.removeAttribute('style');
                element.removeAttribute('hidden');
            });

            menu.querySelectorAll('[aria-controls], [aria-expanded], [aria-haspopup], [data-wp-interactive], [data-wp-context], [data-wp-key], [data-wp-watch], [data-wp-on-async--click], [data-wp-on--click], [data-wp-bind--aria-expanded], [data-wp-bind--aria-label], [data-wp-class--has-child-selected], [data-wp-class--is-menu-open]').forEach(function(element) {
                element.removeAttribute('aria-controls');
                element.removeAttribute('aria-expanded');
                element.removeAttribute('aria-haspopup');
                element.removeAttribute('data-wp-interactive');
                element.removeAttribute('data-wp-context');
                element.removeAttribute('data-wp-key');
                element.removeAttribute('data-wp-watch');
                element.removeAttribute('data-wp-on-async--click');
                element.removeAttribute('data-wp-on--click');
                element.removeAttribute('data-wp-bind--aria-expanded');
                element.removeAttribute('data-wp-bind--aria-label');
                element.removeAttribute('data-wp-class--has-child-selected');
                element.removeAttribute('data-wp-class--is-menu-open');
            });

            menu.querySelectorAll('.wp-block-navigation__submenu-container, ul ul').forEach(function(submenu) {
                submenu.setAttribute('aria-hidden', 'false');
                submenu.classList.remove('is-menu-open');
                submenu.removeAttribute('hidden');
            });

            menu.querySelectorAll('.wp-block-navigation-submenu__toggle').forEach(function(button) { button.remove(); });
            menu.querySelectorAll('li').forEach(function(item) {
                if (Array.from(item.children).some(function(child) { return child.tagName === 'UL'; })) item.classList.add('yea-mobile-group');
            });
        }

        function renderMobileDrawer() {
            if (!menuDirty) return;
            const drawer = ensureMobileDrawer();
            const drawerInner = drawer.querySelector('.yea-mobile-drawer__inner');
            const desktopMenu = getDesktopMenu();

            if (!drawerInner) {
                return;
            }

            drawerInner.innerHTML = '';
            const closeButton = document.createElement('button');
            closeButton.type = 'button';
            closeButton.className = 'yea-menu-close';
            closeButton.textContent = labels.closeMenu || 'Close menu';
            closeButton.addEventListener('click', function() { setMenuOpen(false); });
            drawerInner.appendChild(closeButton);

            if (desktopMenu) {
                const clonedMenu = desktopMenu.cloneNode(true);
                clonedMenu.className = desktopMenu.className;
                sanitizeClonedMenu(clonedMenu);

                if (clonedMenu.querySelectorAll('a').length > 0) {
                    drawerInner.appendChild(clonedMenu);
                }
            }

            if (drawerInner.querySelectorAll('a').length === 0) {
                const fallbackMenu = buildFallbackMenuFromLinks();

                if (fallbackMenu) {
                    sanitizeClonedMenu(fallbackMenu);
                    drawerInner.appendChild(fallbackMenu);
                }
            }

            if (drawerInner.querySelectorAll('a').length === 0) return false;

            menuDirty = false;
        }

        function setMenuOpen(isOpen, restoreFocus) {
            const drawer = ensureMobileDrawer();
            const shouldOpen = isOpen && isMobileViewport();
            const wasOpen = header.classList.contains('menu-open');
            header.style.setProperty('--yea-header-height', header.offsetHeight + 'px');
            if (shouldOpen) renderMobileDrawer();
            header.classList.toggle('menu-open', shouldOpen);
            openButton.setAttribute('aria-expanded', String(shouldOpen));
            openButton.setAttribute('aria-label', shouldOpen ? (labels.closeMenu || 'Close menu') : (labels.openMenu || 'Open menu'));
            drawer.setAttribute('aria-hidden', String(!shouldOpen));
            drawer.hidden = !shouldOpen;
            // Core's desktop container remains intact. CSS suppresses it on mobile only.
            if (shouldOpen && !wasOpen) {
                returnFocus = document.activeElement;
                scrollLocks = [document.documentElement, document.body].map(function(element) {
                    const existed = element.classList.contains('has-modal-open');
                    element.classList.add('has-modal-open');
                    return {element: element, existed: existed};
                });
                inertRegions = Array.from(document.querySelectorAll('main, .site-footer, footer.wp-block-template-part'))
                    .filter(function(element) { return !element.contains(header); })
                    .map(function(element) {
                        const previous = element.inert;
                        element.inert = true;
                        return {element: element, previous: previous};
                    });
                drawer.querySelector('.yea-menu-close').focus();
            } else if (!shouldOpen && wasOpen) {
                inertRegions.forEach(function(region) { region.element.inert = region.previous; });
                scrollLocks.forEach(function(lock) { if (!lock.existed) lock.element.classList.remove('has-modal-open'); });
                inertRegions = [];
                scrollLocks = [];
                if (restoreFocus !== false && returnFocus && returnFocus.isConnected) returnFocus.focus();
                else if (drawer.contains(document.activeElement)) {
                    const desktopLink = getMenuLinks(navigation)[0];
                    if (desktopLink) desktopLink.focus();
                }
            }
        }

        function toggleMenu() {
            setMenuOpen(!header.classList.contains('menu-open'));
        }

        openButton.addEventListener('click', function(event) {
            if (!isMobileViewport() || !header.classList.contains('yea-nav-ready')) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();

            if (typeof event.stopImmediatePropagation === 'function') {
                event.stopImmediatePropagation();
            }

            toggleMenu();
        }, true);

        const drawer = ensureMobileDrawer();

        drawer.addEventListener('click', function(event) {
            const link = event.target.closest('a, .wp-block-button__link');

            if (!link) {
                return;
            }

            if (isMobileViewport()) {
                setMenuOpen(false);
            }
        });

        document.addEventListener('keydown', function(event) {
            if (!header.classList.contains('menu-open')) return;
            if (event.key === 'Escape') {
                event.preventDefault();
                setMenuOpen(false);
            }
            if (event.key === 'Tab') {
                const items = Array.from(drawer.querySelectorAll('a[href], button:not([disabled]), [tabindex="0"]'))
                    .filter(function(element) { return element.getClientRects().length > 0; });
                const first = items[0];
                const last = items[items.length - 1];
                if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
                else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
            }
        });

        window.addEventListener('resize', function() {
            // Do not rebuild an open drawer: rotation/browser chrome must not lose focus.
            header.style.setProperty('--yea-header-height', header.offsetHeight + 'px');
            if (!isMobileViewport()) setMenuOpen(false, false);
        });

        if ('MutationObserver' in window) {
            const observer = new MutationObserver(function() {
                menuDirty = true;
            });

            observer.observe(navigation, {
                childList: true,
                subtree: true,
                attributes: true,
            });

        }

        renderMobileDrawer();
        if (!drawer.querySelector('a[href]')) return;
        openButton.setAttribute('aria-controls', drawer.id);
        openButton.setAttribute('aria-haspopup', 'dialog');
        header.classList.add('yea-nav-ready');
        setMenuOpen(false);
    });
})();
