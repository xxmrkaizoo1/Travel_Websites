const menuToggle = document.querySelector('.menu-toggle');
const primaryNav = document.querySelector('#primary-nav');
const dropdownItems = [...(primaryNav?.querySelectorAll('.has-dropdown, .has-submenu') ?? [])];
const dropdownTriggers = dropdownItems.map((item) => item.querySelector('.nav-trigger, .submenu-trigger'));

const closeDropdowns = (except = null) => {
    dropdownItems.forEach((item) => {
        if (item === except || item.contains(except)) {
            return;
        }

        item.classList.remove('is-open');
        item.classList.add('is-dismissed');
        item.querySelector(':scope > .nav-trigger, :scope > .submenu-trigger')?.setAttribute('aria-expanded', 'false');
    });
};

const closeMobileNav = () => {
    menuToggle?.setAttribute('aria-expanded', 'false');
    menuToggle?.setAttribute('aria-label', 'Open navigation');
    primaryNav?.classList.remove('is-open');
    closeDropdowns();
};

dropdownTriggers.forEach((trigger) => {
    trigger?.addEventListener('click', () => {
        if (trigger.dataset.href && window.matchMedia('(min-width: 1101px)').matches) {
            window.location.href = trigger.dataset.href;

            return;
        }

        const item = trigger.closest('.has-dropdown, .has-submenu');
        const isOpen = item?.classList.contains('is-open') ?? false;

        if (isOpen && item?.classList.contains('has-dropdown')) {
            closeDropdowns();
        } else {
            closeDropdowns(item);
        }

        item?.classList.remove('is-dismissed');
        item?.classList.toggle('is-open', !isOpen);
        trigger.setAttribute('aria-expanded', String(!isOpen));

        if (isOpen) {
            item?.classList.add('is-dismissed');
        }
    });

    trigger?.closest('.has-dropdown, .has-submenu')?.addEventListener('pointerenter', (event) => {
        if (event.pointerType !== 'touch') {
            event.currentTarget.classList.remove('is-dismissed');
        }
    });

    trigger?.closest('.has-dropdown, .has-submenu')?.addEventListener('focusin', (event) => {
        event.currentTarget.classList.remove('is-dismissed');
    });
});

menuToggle?.addEventListener('click', () => {
    const isOpen = menuToggle.getAttribute('aria-expanded') === 'true';

    menuToggle.setAttribute('aria-expanded', String(!isOpen));
    menuToggle.setAttribute('aria-label', isOpen ? 'Open navigation' : 'Close navigation');
    primaryNav?.classList.toggle('is-open', !isOpen);

    if (isOpen) {
        closeDropdowns();
    }
});

primaryNav?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', closeMobileNav);
});

document.addEventListener('click', (event) => {
    const target = event.target;

    if (!(target instanceof Element) || target.closest('.has-dropdown')) {
        return;
    }

    closeDropdowns();

    if (!target.closest('#primary-nav') && !target.closest('.menu-toggle')) {
        closeMobileNav();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    const focusedItem = document.activeElement.closest('.has-dropdown, .has-submenu');
    const focusedOpenItem = focusedItem?.classList.contains('is-dismissed') ? null : focusedItem;
    const openItem = [...dropdownItems].reverse().find((item) => item.classList.contains('is-open')) ?? focusedOpenItem;

    if (openItem) {
        openItem.classList.remove('is-open');
        const trigger = openItem.querySelector(':scope > .nav-trigger, :scope > .submenu-trigger');
        trigger?.setAttribute('aria-expanded', 'false');
        trigger?.blur();
        trigger?.focus();
        openItem.classList.add('is-dismissed');
        return;
    }

    if (menuToggle?.getAttribute('aria-expanded') === 'true') {
        closeMobileNav();
        menuToggle.focus();
    }
});

const carousel = document.querySelector('.carousel');

if (carousel) {
    const slides = [...carousel.querySelectorAll('.campaign-slide')];
    const dots = [...document.querySelectorAll('.carousel-dot')];
    const previousButton = carousel.querySelector('.carousel-prev');
    const nextButton = carousel.querySelector('.carousel-next');
    let activeIndex = 0;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;

            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
            slide.inert = !isActive;
        });

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;

            dot.classList.toggle('is-active', isActive);

            if (isActive) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    };

    previousButton?.addEventListener('click', () => showSlide(activeIndex - 1));
    nextButton?.addEventListener('click', () => showSlide(activeIndex + 1));

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });

    document.querySelectorAll('[data-slide-to]').forEach((link) => {
        link.addEventListener('click', () => {
            showSlide(Number(link.dataset.slideTo));
        });
    });
}

const destinationCarousel = document.querySelector('.destination-carousel');

if (destinationCarousel) {
    const viewport = destinationCarousel.querySelector('.destination-viewport');
    const track = destinationCarousel.querySelector('.destination-track');
    const cards = [...track.querySelectorAll('.destination-card')];
    const previousButton = destinationCarousel.querySelector('.destination-prev');
    const nextButton = destinationCarousel.querySelector('.destination-next');
    const count = destinationCarousel.querySelector('.destination-count');

    const updateDestinationControls = () => {
        const cardStride = cards[0].getBoundingClientRect().width
            + Number.parseFloat(window.getComputedStyle(track).gap);
        const firstVisible = Math.round(track.scrollLeft / cardStride);
        const visibleCount = Math.max(1, Math.round(viewport.clientWidth / cardStride));
        const lastVisible = Math.min(cards.length, firstVisible + visibleCount);

        previousButton.disabled = track.scrollLeft <= 1;
        nextButton.disabled = track.scrollLeft + viewport.clientWidth >= track.scrollWidth - 1;
        count.textContent = `${firstVisible + 1}–${lastVisible} of ${cards.length} destinations`;
    };

    previousButton.addEventListener('click', () => {
        track.scrollBy({ left: -cards[0].getBoundingClientRect().width - 20, behavior: 'smooth' });
    });

    nextButton.addEventListener('click', () => {
        track.scrollBy({ left: cards[0].getBoundingClientRect().width + 20, behavior: 'smooth' });
    });

    track.addEventListener('scroll', updateDestinationControls, { passive: true });
    window.addEventListener('resize', updateDestinationControls);
    updateDestinationControls();
}

document.querySelectorAll('.bali-gallery').forEach((baliCarousel) => {
    const slides = [...baliCarousel.querySelectorAll('.bali-slide')];
    const dots = [...baliCarousel.querySelectorAll('.bali-slide-dots button')];
    const previousButton = baliCarousel.querySelector('.bali-carousel-prev');
    const nextButton = baliCarousel.querySelector('.bali-carousel-next');
    const toggleButton = baliCarousel.querySelector('.bali-carousel-toggle');
    const slideCount = baliCarousel.querySelector('.bali-slide-count strong');
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let activeIndex = 0;
    let userPaused = motionPreference.matches;
    let isHovered = false;
    let isFocused = false;
    let timer = null;

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === activeIndex;

            slide.classList.toggle('is-active', isActive);
            slide.setAttribute('aria-hidden', String(!isActive));
            slide.inert = !isActive;
        });

        dots.forEach((dot, dotIndex) => {
            const isActive = dotIndex === activeIndex;

            dot.classList.toggle('is-active', isActive);

            if (isActive) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });

        if (slideCount) {
            slideCount.textContent = String(activeIndex + 1).padStart(2, '0');
        }
    };

    const updateAutoplay = () => {
        window.clearInterval(timer);
        timer = null;

        if (userPaused || isHovered || isFocused || document.hidden || motionPreference.matches) {
            return;
        }

        timer = window.setInterval(() => showSlide(activeIndex + 1), 6000);
    };

    const updateToggleButton = () => {
        const isReducedMotion = motionPreference.matches;
        const isPaused = userPaused || isReducedMotion;

        toggleButton?.setAttribute('aria-pressed', String(isPaused));
        toggleButton?.setAttribute(
            'aria-label',
            isReducedMotion ? 'Slideshow paused because reduced motion is enabled' : isPaused ? 'Play slideshow' : 'Pause slideshow',
        );
        toggleButton?.toggleAttribute('disabled', isReducedMotion);

        const icon = toggleButton?.querySelector('span');

        if (icon) {
            icon.textContent = isPaused ? '▶' : 'Ⅱ';
        }
    };

    previousButton?.addEventListener('click', () => showSlide(activeIndex - 1));
    nextButton?.addEventListener('click', () => showSlide(activeIndex + 1));

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => showSlide(index));
    });

    toggleButton?.addEventListener('click', () => {
        userPaused = !userPaused;
        updateToggleButton();
        updateAutoplay();
    });

    baliCarousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showSlide(activeIndex - 1);
        } else if (event.key === 'ArrowRight') {
            event.preventDefault();
            showSlide(activeIndex + 1);
        }
    });

    baliCarousel.addEventListener('pointerenter', () => {
        isHovered = true;
        updateAutoplay();
    });

    baliCarousel.addEventListener('pointerleave', () => {
        isHovered = false;
        updateAutoplay();
    });

    baliCarousel.addEventListener('focusin', () => {
        isFocused = true;
        updateAutoplay();
    });

    baliCarousel.addEventListener('focusout', (event) => {
        if (!baliCarousel.contains(event.relatedTarget)) {
            isFocused = false;
            updateAutoplay();
        }
    });

    document.addEventListener('visibilitychange', updateAutoplay);
    motionPreference.addEventListener('change', () => {
        if (motionPreference.matches) {
            userPaused = true;
        }

        updateToggleButton();
        updateAutoplay();
    });

    showSlide(activeIndex);
    updateToggleButton();
    updateAutoplay();
});

const refreshRepeater = (repeater) => {
    const items = repeater.querySelectorAll(':scope > [data-repeater-list] > [data-repeater-item]');
    const max = Number(repeater.dataset.max || 0);

    items.forEach((item, index) => {
        item.querySelectorAll(':scope > legend [data-repeater-number]').forEach((number) => {
            number.textContent = String(index + 1);
        });
    });

    const addButton = repeater.querySelector(':scope > [data-repeater-add]');

    if (addButton) {
        addButton.disabled = max > 0 && items.length >= max;
    }
};

document.querySelectorAll('[data-repeater]').forEach(refreshRepeater);

document.addEventListener('click', (event) => {
    const addButton = event.target.closest('[data-repeater-add]');
    const removeButton = event.target.closest('[data-repeater-remove]');

    if (addButton) {
        const repeater = addButton.closest('[data-repeater]');
        const list = repeater.querySelector(':scope > [data-repeater-list]');
        const template = repeater.querySelector(':scope > template[data-repeater-template]');
        const next = Number(repeater.dataset.next || 1000);

        repeater.dataset.next = String(next + 1);
        list.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll(repeater.dataset.token, String(next)));
        refreshRepeater(repeater);
        list.lastElementChild?.querySelector('input:not([type="hidden"]), textarea')?.focus();
    }

    if (removeButton) {
        const item = removeButton.closest('[data-repeater-item]');
        const repeater = item.parentElement.closest('[data-repeater]');

        item.remove();
        refreshRepeater(repeater);
    }
});

const contextTriggers = document.querySelectorAll('[data-context-menu]');
const contextMenu = contextTriggers.length ? document.getElementById(contextTriggers[0].dataset.contextMenu) : null;

if (contextMenu) {
    const pageTitle = contextMenu.querySelector('[data-context-page-title]');
    const pageActions = contextMenu.querySelector('[data-context-page-actions]');
    const deleteForm = contextMenu.querySelector('[data-context-delete]');
    let activeTrigger = null;

    const closeMenu = () => {
        contextMenu.hidden = true;
    };

    contextTriggers.forEach((trigger) => {
        trigger.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            activeTrigger = trigger;

            const hasPage = Boolean(trigger.dataset.editUrl);

            pageTitle.hidden = !hasPage;
            pageActions.hidden = !hasPage;
            pageTitle.textContent = trigger.dataset.pageName || '';

            const parentSlug = trigger.dataset.pageSlug || '';

            contextMenu.querySelector('[data-context-create-title]').textContent = parentSlug
                ? `Create new page inside ${trigger.dataset.pageName}`
                : 'Create new page';

            contextMenu.querySelectorAll('[data-context-create]').forEach((link) => {
                const url = new URL(link.href, window.location.origin);

                url.searchParams.delete('parent');

                if (parentSlug) {
                    url.searchParams.set('parent', parentSlug);
                }

                link.href = url.toString();
            });

            if (hasPage) {
                contextMenu.querySelector('[data-context-edit]').href = trigger.dataset.editUrl;
                contextMenu.querySelector('[data-context-view]').href = trigger.dataset.viewUrl;
                deleteForm.hidden = !trigger.dataset.deleteUrl;
                deleteForm.action = trigger.dataset.deleteUrl || '#';
            }

            const rect = trigger.getBoundingClientRect();
            const fromKeyboard = event.clientX === 0 && event.clientY === 0;

            contextMenu.hidden = false;
            contextMenu.style.left = `${Math.max(8, Math.min(fromKeyboard ? rect.left + 24 : event.clientX, window.innerWidth - contextMenu.offsetWidth - 8))}px`;
            contextMenu.style.top = `${Math.max(8, Math.min(fromKeyboard ? rect.bottom : event.clientY, window.innerHeight - contextMenu.offsetHeight - 8))}px`;
            contextMenu.querySelector('a:not([hidden])')?.focus();
        });
    });

    document.addEventListener('click', (event) => {
        if (!contextMenu.contains(event.target)) {
            closeMenu();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !contextMenu.hidden) {
            closeMenu();
            activeTrigger?.focus();
        }
    });

    window.addEventListener('scroll', closeMenu, true);
    window.addEventListener('resize', closeMenu);
}

const galleryEditor = document.querySelector('[data-gallery-editor]');

if (galleryEditor) {
    const slidesContainer = galleryEditor.querySelector('[data-gallery-slides]');
    const addSlideButton = galleryEditor.querySelector('[data-add-slide]');
    const slideCount = galleryEditor.querySelector('[data-gallery-count]');
    const slideTemplate = document.querySelector('template[data-gallery-template]');
    const maximumSlides = Number(galleryEditor.dataset.maxSlides);
    const fieldPrefix = galleryEditor.dataset.fieldPrefix ?? 'gallery';

    const updateSlides = () => {
        const slides = [...slidesContainer.querySelectorAll('[data-gallery-slide]')];

        slides.forEach((slide, index) => {
            slide.querySelector('[data-slide-legend]').textContent = `Slide ${index + 1}`;
            slide.querySelector('[data-remove-slide]').setAttribute('aria-label', `Remove slide ${index + 1}`);

            slide.querySelectorAll('[data-gallery-field]').forEach((field) => {
                field.name = `${fieldPrefix}[${index}][${field.dataset.galleryField}]`;
                field.required = index === 0 && !('galleryOptional' in field.dataset);
            });
        });

        slideCount.textContent = `${slides.length} of ${maximumSlides} slides`;
        addSlideButton.disabled = slides.length >= maximumSlides;
    };

    addSlideButton.addEventListener('click', () => {
        if (!slideTemplate || slidesContainer.querySelectorAll('[data-gallery-slide]').length >= maximumSlides) {
            return;
        }

        slidesContainer.append(slideTemplate.content.cloneNode(true));
        updateSlides();
        slidesContainer.querySelector('[data-gallery-slide]:last-child [data-gallery-field="image"]').focus();
    });

    slidesContainer.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const removeButton = event.target.closest('[data-remove-slide]');

        if (!removeButton) {
            return;
        }

        const slide = removeButton.closest('[data-gallery-slide]');
        const nextSlide = slide.nextElementSibling ?? slide.previousElementSibling;
        slide.remove();
        updateSlides();

        if (nextSlide) {
            nextSlide.querySelector('[data-gallery-field]').focus();
        } else {
            addSlideButton.focus();
        }
    });

    updateSlides();
}

const adminMenuToggle = document.querySelector('.admin-menu-toggle');
const adminSidebar = document.querySelector('.admin-sidebar');
const adminSidebarBackdrop = document.querySelector('.admin-sidebar-backdrop');
const adminSidebarBreakpoint = window.matchMedia('(max-width: 900px)');

const syncAdminSidebar = () => {
    const isMobile = adminSidebarBreakpoint.matches;
    const isOpen = adminMenuToggle?.getAttribute('aria-expanded') === 'true';

    adminSidebar?.toggleAttribute('inert', isMobile && !isOpen);
    adminSidebarBackdrop?.toggleAttribute('hidden', !isMobile || !isOpen);
    document.body.classList.toggle('admin-sidebar-open', isMobile && isOpen);
};

const closeAdminSidebar = (restoreFocus = false) => {
    adminMenuToggle?.setAttribute('aria-expanded', 'false');
    adminMenuToggle?.setAttribute('aria-label', 'Open admin navigation');
    adminSidebar?.classList.remove('is-open');
    syncAdminSidebar();

    if (restoreFocus && adminMenuToggle) {
        adminMenuToggle.focus({ preventScroll: true });
    }
};

adminMenuToggle?.addEventListener('click', () => {
    const isOpen = adminMenuToggle.getAttribute('aria-expanded') === 'true';

    adminMenuToggle.setAttribute('aria-expanded', String(!isOpen));
    adminMenuToggle.setAttribute('aria-label', isOpen ? 'Open admin navigation' : 'Close admin navigation');
    adminSidebar?.classList.toggle('is-open', !isOpen);
    syncAdminSidebar();

    if (!isOpen) {
        adminSidebar?.querySelector('a, button, summary')?.focus({ preventScroll: true });
    }
});

adminSidebar?.querySelectorAll('a').forEach((link) => {
    link.addEventListener('click', () => closeAdminSidebar(true));
});

adminSidebarBackdrop?.addEventListener('click', () => closeAdminSidebar(true));

document.addEventListener('click', (event) => {
    const target = event.target;

    if (target instanceof Element && !target.closest('.admin-sidebar, .admin-menu-toggle')) {
        closeAdminSidebar();
    }
});

document.addEventListener('keydown', (event) => {
    if (!adminSidebar || adminMenuToggle?.getAttribute('aria-expanded') !== 'true' || !adminSidebarBreakpoint.matches) {
        return;
    }

    if (event.key === 'Escape') {
        closeAdminSidebar(true);
        return;
    }

    if (event.key === 'Tab') {
        const focusableElements = [...adminSidebar.querySelectorAll('a[href], button:not(:disabled), summary, [tabindex]:not([tabindex="-1"])')]
            .filter((element) => !element.closest('[hidden], [inert]'));
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];

        if (event.shiftKey && document.activeElement === firstElement) {
            event.preventDefault();
            lastElement?.focus();
        } else if (!event.shiftKey && document.activeElement === lastElement) {
            event.preventDefault();
            firstElement?.focus();
        }
    }
});

adminSidebarBreakpoint.addEventListener('change', syncAdminSidebar);
window.addEventListener('resize', syncAdminSidebar);
syncAdminSidebar();

const bookingSearch = document.querySelector('.admin-booking-search');

if (bookingSearch) {
    const bookingRows = [...document.querySelectorAll('.admin-bookings-table tbody tr')];
    const visibleCount = document.querySelector('.admin-visible-booking-count');
    const emptyState = document.querySelector('.admin-empty-state');

    bookingSearch.addEventListener('input', () => {
        const query = bookingSearch.value.trim().toLocaleLowerCase();
        let visibleRows = 0;

        bookingRows.forEach((row) => {
            const matches = row.textContent.toLocaleLowerCase().includes(query);

            row.hidden = !matches;

            if (matches) {
                visibleRows += 1;
            }
        });

        if (visibleCount) {
            visibleCount.textContent = String(visibleRows);
        }

        if (emptyState) {
            emptyState.hidden = visibleRows > 0;
        }
    });
}
