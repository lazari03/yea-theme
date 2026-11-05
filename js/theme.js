/**
 * YEA Theme JavaScript
 * Young Entrepreneurs Albania WordPress Theme
 */

(function() {
    'use strict';

    // DOM Ready
    document.addEventListener('DOMContentLoaded', function() {
        initHeaderScroll();
        initScrollReveal();
        initCounterAnimation();
        initSmoothScroll();
        initParallax();
        initProgramCards();
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
     * Scroll reveal animations
     */
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
        
        if (revealElements.length === 0) return;
        if (!('IntersectionObserver' in window)) {
            // Fallback for browsers without IntersectionObserver
            revealElements.forEach(function(el) {
                el.classList.add('active');
            });
            return;
        }

        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('active');
                    observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        revealElements.forEach(function(el) {
            observer.observe(el);
        });
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

    /**
     * Smooth scroll for anchor links
     */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;
                
                const target = document.querySelector(href);
                if (!target) return;
                
                e.preventDefault();
                
                const headerOffset = 100;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
                
                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });
            });
        });
    }

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
        const navigation = header ? header.querySelector('.header-inner > .nav.wp-block-navigation') : null;
        const openButton = navigation ? navigation.querySelector('.wp-block-navigation__responsive-container-open') : null;
        const responsiveContainer = navigation ? navigation.querySelector('.wp-block-navigation__responsive-container') : null;
        const desktopActions = header ? header.querySelector('.header-inner > .header-actions') : null;

        if (!header || !navigation || !openButton) return;

        function getMenuCandidates(root) {
            if (!root) {
                return [];
            }

            return Array.from(
                root.querySelectorAll('.wp-block-navigation__container, .wp-block-page-list, .wp-block-pages-list')
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
                return getMenuLinks(candidate).length > 0;
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
                drawer.innerHTML = '<div class="container yea-mobile-drawer__inner"></div>';
                header.appendChild(drawer);
            }

            return drawer;
        }

        let mobileSubmenuId = 0;

        function getDirectChild(element, selector) {
            return Array.from(element.children).find(function(child) {
                return child.matches(selector);
            }) || null;
        }

        function getAccordionItemChildren(item) {
            return {
                link: getDirectChild(item, 'a, .wp-block-navigation-item__content, .wp-block-pages-list__item__link'),
                toggle: getDirectChild(item, '.wp-block-navigation-submenu__toggle'),
                submenu: getDirectChild(item, '.wp-block-navigation__submenu-container, ul'),
            };
        }

        function setAccordionState(item, isOpen) {
            const children = getAccordionItemChildren(item);

            if (!children.submenu || !children.toggle) {
                return;
            }

            item.classList.toggle('is-accordion-open', isOpen);
            children.submenu.hidden = !isOpen;
            children.submenu.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
            children.submenu.classList.toggle('is-menu-open', isOpen);
            children.toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        }

        function closeNestedAccordions(root) {
            if (!root) {
                return;
            }

            root.querySelectorAll('.yea-mobile-has-submenu.is-accordion-open').forEach(function(item) {
                setAccordionState(item, false);
            });
        }

        function closeAccordionSiblings(item) {
            const parentList = item.parentElement;

            if (!parentList) {
                return;
            }

            Array.from(parentList.children).forEach(function(sibling) {
                if (sibling === item || !sibling.classList.contains('yea-mobile-has-submenu')) {
                    return;
                }

                setAccordionState(sibling, false);
                closeNestedAccordions(getAccordionItemChildren(sibling).submenu);
            });
        }

        function setupMobileAccordion(menu) {
            menu.querySelectorAll('li').forEach(function(item) {
                const children = getAccordionItemChildren(item);

                if (!children.submenu) {
                    return;
                }

                item.classList.add('yea-mobile-has-submenu');

                let toggle = children.toggle;

                if (!toggle) {
                    toggle = document.createElement('button');
                    toggle.className = 'wp-block-navigation-submenu__toggle';

                    if (children.link) {
                        children.link.insertAdjacentElement('afterend', toggle);
                    } else {
                        item.insertBefore(toggle, children.submenu);
                    }
                }

                toggle.setAttribute('type', 'button');
                toggle.innerHTML = '';

                if (!children.submenu.id) {
                    mobileSubmenuId += 1;
                    children.submenu.id = 'yea-mobile-submenu-' + mobileSubmenuId;
                }

                const itemLabel = children.link ? children.link.textContent.trim() : 'submenu';

                toggle.setAttribute('aria-controls', children.submenu.id);
                toggle.setAttribute('aria-label', 'Toggle ' + itemLabel + ' submenu');

                const shouldStartOpen = item.classList.contains('current-menu-item') ||
                    item.classList.contains('current-menu-ancestor') ||
                    item.classList.contains('current_page_item') ||
                    item.classList.contains('current_page_ancestor') ||
                    item.classList.contains('current-page-ancestor');

                setAccordionState(item, shouldStartOpen);

                const toggleAccordion = function(event) {
                    if (!isMobileViewport()) {
                        return;
                    }

                    event.preventDefault();
                    event.stopPropagation();

                    const shouldOpen = !item.classList.contains('is-accordion-open');

                    closeAccordionSiblings(item);
                    setAccordionState(item, shouldOpen);

                    if (!shouldOpen) {
                        closeNestedAccordions(children.submenu);
                    }
                };

                toggle.addEventListener('click', toggleAccordion);

                if (children.link) {
                    children.link.addEventListener('click', toggleAccordion);
                }
            });
        }

        function sanitizeClonedMenu(menu) {
            if (!menu) {
                return;
            }

            menu.classList.add('yea-mobile-nav-list');
            menu.removeAttribute('id');
            menu.removeAttribute('style');
            menu.removeAttribute('hidden');
            menu.setAttribute('aria-hidden', 'false');

            menu.querySelectorAll('[id]').forEach(function(element) {
                element.removeAttribute('id');
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
                submenu.setAttribute('aria-hidden', 'true');
                submenu.classList.remove('is-menu-open');
                submenu.setAttribute('hidden', 'hidden');
            });

            setupMobileAccordion(menu);
        }

        function renderMobileDrawer() {
            const drawer = ensureMobileDrawer();
            const drawerInner = drawer.querySelector('.yea-mobile-drawer__inner');
            const desktopMenu = getDesktopMenu();

            if (!drawerInner) {
                return;
            }

            drawerInner.innerHTML = '';

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

            if (desktopActions && desktopActions.innerHTML.trim() !== '') {
                const mobileActions = document.createElement('div');
                mobileActions.className = desktopActions.className;
                mobileActions.classList.add('header-actions--mobile');
                mobileActions.innerHTML = desktopActions.innerHTML;
                drawerInner.appendChild(mobileActions);
            }
        }

        function setMenuOpen(isOpen) {
            const drawer = ensureMobileDrawer();
            const shouldOpen = isOpen && isMobileViewport();

            header.classList.toggle('menu-open', shouldOpen);
            openButton.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
            openButton.setAttribute('aria-label', shouldOpen ? 'Close menu' : 'Open menu');

            drawer.setAttribute('aria-hidden', shouldOpen ? 'false' : 'true');

            if (responsiveContainer) {
                responsiveContainer.classList.remove('is-menu-open');
                responsiveContainer.setAttribute('aria-hidden', 'true');
                responsiveContainer.setAttribute('hidden', 'hidden');
            }

            document.documentElement.classList.toggle('has-modal-open', shouldOpen);
            document.body.classList.toggle('has-modal-open', shouldOpen);

            if (shouldOpen) {
                renderMobileDrawer();
                drawer.removeAttribute('hidden');
            } else {
                drawer.setAttribute('hidden', 'hidden');
            }
        }

        function toggleMenu() {
            setMenuOpen(!header.classList.contains('menu-open'));
        }

        if (responsiveContainer) {
            responsiveContainer.classList.remove('is-menu-open');
            responsiveContainer.setAttribute('aria-hidden', 'true');
            responsiveContainer.setAttribute('hidden', 'hidden');
        }

        openButton.addEventListener('click', function(event) {
            if (!isMobileViewport()) {
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
            if (event.key === 'Escape') {
                setMenuOpen(false);
            }
        });

        window.addEventListener('resize', function() {
            if (isMobileViewport()) {
                renderMobileDrawer();
                return;
            }

            setMenuOpen(false);
        });

        if ('MutationObserver' in window) {
            const observer = new MutationObserver(function() {
                if (isMobileViewport() || header.classList.contains('menu-open')) {
                    renderMobileDrawer();
                }
            });

            observer.observe(navigation, {
                childList: true,
                subtree: true,
                attributes: true,
            });

            if (desktopActions) {
                observer.observe(desktopActions, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                });
            }
        }

        renderMobileDrawer();
        setMenuOpen(false);
    });
})();
