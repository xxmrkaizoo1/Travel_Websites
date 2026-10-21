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

const revealTargets = document.querySelectorAll([
    '.welcome-section h2', '.welcome-subtitle', '.value-card', '.services-section h2', '.service-card', '.footer-inner > *',
    '.island-intro-copy', '.island-photo-stack', '.island-section-heading', '.island-highlight', '.destination-carousel',
    '.aceh-itinerary-heading', '.aceh-day', '.bali-gallery', '.aceh-details-card', '.aceh-hotel-card', '.island-contact > *',
    '.series-head', '.series-advantage', '.series-flag', '.series-photo',
].join(', '));

if (revealTargets.length && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-revealed');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    revealTargets.forEach((target) => {
        const siblings = [...target.parentElement.children].filter((child) => child.className === target.className);

        target.style.setProperty('--reveal-delay', `${Math.max(siblings.indexOf(target), 0) * 110}ms`);
        target.classList.add('js-reveal');
        revealObserver.observe(target);
    });
}

const campaignTrack = document.querySelector('.homepage-campaign-grid');

if (campaignTrack && campaignTrack.children.length > 1) {
    const cards = [...campaignTrack.children];
    const campaignNav = document.createElement('div');

    campaignNav.className = 'repeater-slider-nav campaign-slider-nav';
    campaignNav.innerHTML = `<button type="button" class="repeater-slider-arrow" data-campaign-step="-1" aria-label="Previous campaign">‹</button>
        <div class="campaign-slider-dots">${cards.map((_, index) => `<button type="button" class="campaign-slider-dot" data-campaign-go="${index}" aria-label="Campaign ${index + 1}"></button>`).join('')}</div>
        <button type="button" class="repeater-slider-arrow" data-campaign-step="1" aria-label="Next campaign">›</button>`;
    campaignTrack.before(campaignNav);

    const stepSize = () => cards[0].offsetWidth + 18;
    const activeIndex = () => Math.min(Math.round(campaignTrack.scrollLeft / stepSize()), cards.length - 1);

    const syncCampaignNav = () => {
        const index = activeIndex();
        const maxScroll = campaignTrack.scrollWidth - campaignTrack.clientWidth;

        campaignNav.querySelectorAll('[data-campaign-go]').forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
        campaignNav.querySelector('[data-campaign-step="-1"]').disabled = campaignTrack.scrollLeft <= 1;
        campaignNav.querySelector('[data-campaign-step="1"]').disabled = campaignTrack.scrollLeft >= maxScroll - 1;
    };

    campaignNav.addEventListener('click', (event) => {
        const control = event.target.closest('[data-campaign-go], [data-campaign-step]');

        if (!control) {
            return;
        }

        const target = control.dataset.campaignGo !== undefined ? Number(control.dataset.campaignGo) : activeIndex() + Number(control.dataset.campaignStep);

        campaignTrack.scrollTo({ left: Math.max(target, 0) * stepSize() });
    });

    campaignTrack.addEventListener('scroll', syncCampaignNav, { passive: true });
    window.addEventListener('resize', syncCampaignNav);
    syncCampaignNav();
}

const slideshowForm = document.querySelector('form[data-form-slideshow]');

if (slideshowForm) {
    const panels = [...slideshowForm.querySelectorAll(':scope > .homepage-form-panel')];
    const viewport = document.createElement('div');
    const track = document.createElement('div');
    const nav = document.createElement('div');
    let current = 0;

    viewport.className = 'form-slideshow-viewport';
    track.className = 'form-slideshow-track';
    nav.className = 'repeater-slider-nav form-slideshow-nav';
    panels[0].before(nav, viewport);
    viewport.append(track);
    panels.forEach((panel) => track.append(panel));

    const firstNumber = Number.parseInt(panels[0].querySelector('.homepage-form-icon')?.textContent ?? '', 10) || 0;

    panels.forEach((panel, index) => {
        const icon = panel.querySelector('.homepage-form-icon');

        if (icon) {
            icon.textContent = String(firstNumber + index).padStart(2, '0');
        }
    });

    const titles = panels.map((panel) => panel.querySelector('.homepage-form-icon')?.textContent.trim() ?? '');
    const heading = (panel) => panel.querySelector('h2')?.textContent.trim() ?? '';

    nav.innerHTML = `<button type="button" class="repeater-slider-arrow" data-slide-step="-1" aria-label="Previous section">‹</button>
        <div class="repeater-slider-tabs">${panels.map((panel, index) => `<button type="button" class="repeater-slider-tab" data-slide-go="${index}" title="${heading(panel)}" aria-label="${heading(panel)}">${titles[index]}</button>`).join('')}</div>
        <span class="form-slideshow-count" data-slide-count></span>
        <button type="button" class="repeater-slider-arrow" data-slide-step="1" aria-label="Next section">›</button>`;

    const resizeViewport = () => {
        viewport.style.height = `${panels[current].offsetHeight}px`;
    };

    const showSlide = (index) => {
        current = Math.min(Math.max(index, 0), panels.length - 1);
        track.style.transform = `translateX(-${current * 100}%)`;
        panels.forEach((panel, panelIndex) => {
            panel.classList.toggle('is-current', panelIndex === current);
            panel.toggleAttribute('inert', panelIndex !== current);
        });
        nav.querySelectorAll('[data-slide-go]').forEach((tab, tabIndex) => {
            tab.classList.toggle('is-active', tabIndex === current);
            tab.setAttribute('aria-current', String(tabIndex === current));
        });
        nav.querySelector('[data-slide-step="-1"]').disabled = current === 0;
        nav.querySelector('[data-slide-step="1"]').disabled = current === panels.length - 1;
        nav.querySelector('[data-slide-count]').textContent = `${current + 1} / ${panels.length} · ${heading(panels[current])}`;
        resizeViewport();
    };

    nav.addEventListener('click', (event) => {
        const control = event.target.closest('[data-slide-go], [data-slide-step]');

        if (!control) {
            return;
        }

        showSlide(control.dataset.slideGo !== undefined ? Number(control.dataset.slideGo) : current + Number(control.dataset.slideStep));
    });

    const resizeObserver = new ResizeObserver(resizeViewport);

    panels.forEach((panel) => resizeObserver.observe(panel));

    slideshowForm.addEventListener('invalid', (event) => {
        const panelIndex = panels.findIndex((panel) => panel.contains(event.target));

        if (panelIndex !== -1 && panelIndex !== current) {
            panels[panelIndex].removeAttribute('inert');
            showSlide(panelIndex);
        }
    }, true);

    const firstErrorPanel = panels.findIndex((panel) => panel.querySelector('[aria-invalid="true"], .is-invalid'));

    showSlide(firstErrorPanel === -1 ? 0 : firstErrorPanel);
}

document.querySelectorAll('input[name="kind"]').forEach((kindInput) => {
    kindInput.addEventListener('change', () => {
        document.querySelectorAll('[data-template-group]').forEach((group) => {
            const isActive = group.dataset.templateGroup === kindInput.value && kindInput.checked;

            group.hidden = !isActive;
            group.querySelectorAll('input').forEach((input) => {
                input.disabled = !isActive;
            });
        });
    });
});

const previewForm = document.querySelector('form[data-form-slideshow]');

document.addEventListener('click', (event) => {
    const clearButton = event.target.closest('[data-clear-destination]');

    if (!clearButton) {
        return;
    }

    if (!window.confirm('Clear this destination? It will be removed from the page when you save.')) {
        return;
    }

    clearButton.closest('.homepage-form-panel').querySelectorAll('input:not([type="file"]):not([type="hidden"]), textarea').forEach((field) => {
        field.value = '';
    });
    clearButton.remove();
});

document.addEventListener('click', (event) => {
    const option = event.target.closest('.emoji-picker-option');

    if (!option) {
        return;
    }

    const field = option.closest('.emoji-field');
    const input = field.querySelector('.emoji-field-input');

    input.value = option.dataset.emoji;
    field.querySelectorAll('.emoji-picker-option').forEach((button) => button.classList.toggle('is-selected', button === option));
    input.dispatchEvent(new Event('input', { bubbles: true }));
});

if (previewForm) {
    const isPackagePage = previewForm.querySelector('[name^="package["]') !== null;
    const isSeriesPage = previewForm.querySelector('[name^="series["]') !== null;
    const sections = [
        ['hero', 'Hero banner', 'Top of the page: headline, intro text, button and background image'],
        ['intro', 'Introduction', 'Welcome text with a photo and caption'],
        ['highlights', 'Highlights', 'Three highlight cards'],
        ...(isPackagePage
            ? [
                ['itineraries', 'Itinerary cards', 'Package cards with the day-by-day plan'],
                ['gallery', 'Photo gallery', 'Slideshow of photos'],
                ['details', 'Includes / excludes', 'Price includes and does not include lists'],
                ['accommodation', 'Accommodation', 'Hotel cards'],
            ]
            : isSeriesPage
                ? [['series', 'Series destinations', 'Destination tabs with advantages, flag and five photos']]
                : [['destinations', 'Destination slideshow', 'Sliding destination cards']]),
        ['contact', 'Contact section', 'Enquiry text and WhatsApp button'],
    ];
    const rules = [
        [/^(name|parent_slug)$/, 'navbar'],
        [/^hero_/, 'hero'],
        [/^intro_/, 'intro'],
        [/^highlights/, 'highlights'],
        [/^series\[/, 'series'],
        [/^(destinations_|slides)/, isSeriesPage ? 'series' : 'destinations'],
        [/^package\[itineraries/, 'itineraries'],
        [/^package\[gallery/, 'gallery'],
        [/^package\[(details_|inclusions|exclusions)/, 'details'],
        [/^package\[(accommodation_|hotels)/, 'accommodation'],
        [/^contact_/, 'contact'],
        [/^meta_/, 'meta'],
    ];
    const preview = document.createElement('aside');

    preview.className = 'field-preview';
    preview.setAttribute('role', 'tooltip');
    preview.hidden = true;
    document.body.append(preview);

    const sectionFor = (field) => rules.find(([pattern]) => pattern.test(field.name))?.[1] ?? null;

    const renderPreview = (key) => {
        if (key === 'navbar' || key === 'meta') {
            const text = key === 'navbar'
                ? ['Website navbar & admin sidebar', 'The page name and its menu position appear in the top navigation.']
                : ['Browser tab & search results', 'The browser title and description shown in the tab and on Google.'];

            const shot = key === 'navbar' ? `<img class="field-preview-shot" src="/images/admin-preview/navbar.jpg" alt="Website navbar" onerror="this.remove()">` : `<div class="field-preview-page"><span class="field-preview-block is-active">Page title — description</span></div>`;

            preview.innerHTML = `<strong class="field-preview-title">Shown in: ${text[0]}</strong>${shot}<p>${text[1]}</p>`;

            return;
        }

        const active = sections.find(([sectionKey]) => sectionKey === key);

        const image = new Image();
        image.className = 'field-preview-shot';
        image.alt = active[1];
        image.src = `/images/admin-preview/${key}.jpg`;

        preview.innerHTML = `<strong class="field-preview-title">Shown in: ${active[1]}</strong><p>${active[2]}</p>`;
        preview.insertBefore(image, preview.querySelector('p'));
        image.onerror = () => image.remove();
    };

    const showPreview = (event) => {
        const field = event.target instanceof Element ? event.target.closest('input, textarea, select') : null;
        const key = field && field.name ? sectionFor(field) : null;

        if (!key) {
            preview.hidden = true;

            return;
        }

        renderPreview(key);
        preview.hidden = false;
    };

    previewForm.addEventListener('focusin', showPreview);
    previewForm.addEventListener('mouseover', showPreview);
    previewForm.addEventListener('focusout', () => {
        preview.hidden = true;
    });
    previewForm.addEventListener('mouseleave', () => {
        if (!previewForm.contains(document.activeElement)) {
            preview.hidden = true;
        }
    });
}

const contextTriggers = document.querySelectorAll('[data-context-menu]');
const contextMenu = contextTriggers.length ? document.getElementById(contextTriggers[0].dataset.contextMenu) : null;

if (contextMenu) {
    const pageTitle = contextMenu.querySelector('[data-context-page-title]');
    const pageActions = contextMenu.querySelector('[data-context-page-actions]');
    const moveActions = contextMenu.querySelector('[data-navigation-move-actions]');
    const moveParentSelect = contextMenu.querySelector('[data-navigation-parent-select]');
    const moveButton = contextMenu.querySelector('[data-navigation-move]');
    const moveStatus = contextMenu.querySelector('[data-navigation-status]');
    const deleteForm = contextMenu.querySelector('[data-context-delete]');
    const navigationTargets = [...document.querySelectorAll('[data-navigation-parent-target]')];
    let activeTrigger = null;

    const closeMenu = () => {
        contextMenu.hidden = true;
    };

    const populateParentOptions = (trigger) => {
        moveParentSelect.replaceChildren();

        const excludedSlugs = new Set([trigger.dataset.pageSlug]);
        const pendingSlugs = [trigger.dataset.pageSlug];

        while (pendingSlugs.length > 0) {
            const parentSlug = pendingSlugs.pop();

            navigationTargets.forEach((target) => {
                const childSlug = target.dataset.navigationParentTarget;

                if (target.dataset.navigationParent === parentSlug && childSlug && !excludedSlugs.has(childSlug)) {
                    excludedSlugs.add(childSlug);
                    pendingSlugs.push(childSlug);
                }
            });
        }

        navigationTargets.forEach((target) => {
            const parentSlug = target.dataset.navigationParentTarget;

            if (parentSlug && excludedSlugs.has(parentSlug)) {
                return;
            }

            const option = document.createElement('option');
            option.value = parentSlug;
            option.textContent = target.dataset.navigationParentName || target.dataset.pageName || target.textContent.trim();
            moveParentSelect.append(option);
        });

        moveParentSelect.value = trigger.dataset.navigationParent || '';
    };

    const openMenu = (trigger, event, focusMove = false) => {
        activeTrigger = trigger;

        const hasPage = Boolean(trigger.dataset.editUrl);

        pageTitle.hidden = !hasPage;
        pageActions.hidden = !hasPage;
        pageTitle.textContent = trigger.dataset.pageName || '';
        moveActions.hidden = !trigger.dataset.moveUrl;
        moveStatus.textContent = '';

        const parentSlug = trigger.dataset.pageSlug || trigger.dataset.navigationParentTarget || '';
        const parentName = trigger.dataset.pageName || trigger.dataset.navigationParentName;

        contextMenu.querySelector('[data-context-create-title]').textContent = parentSlug
            ? `Create new page inside ${parentName}`
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

        if (trigger.dataset.moveUrl) {
            populateParentOptions(trigger);
        }

        const rect = trigger.getBoundingClientRect();
        const fromKeyboard = event.clientX === 0 && event.clientY === 0;

        contextMenu.hidden = false;
        contextMenu.style.left = `${Math.max(8, Math.min(fromKeyboard ? rect.left + 24 : event.clientX, window.innerWidth - contextMenu.offsetWidth - 8))}px`;
        contextMenu.style.top = `${Math.max(8, Math.min(fromKeyboard ? rect.bottom : event.clientY, window.innerHeight - contextMenu.offsetHeight - 8))}px`;
        (focusMove ? moveParentSelect : contextMenu.querySelector('a:not([hidden])'))?.focus();
    };

    contextTriggers.forEach((trigger) => {
        trigger.addEventListener('contextmenu', (event) => {
            event.preventDefault();
            openMenu(trigger, event);
        });
    });

    document.addEventListener('click', (event) => {
        const moveTrigger = event.target.closest('[data-navigation-select]');

        if (moveTrigger) {
            event.preventDefault();
            const pageTrigger = moveTrigger.closest('.admin-subnav-entry')?.querySelector('[data-context-menu][data-move-url]');

            if (pageTrigger) {
                openMenu(pageTrigger, event, true);
            }

            return;
        }

        if (contextMenu.contains(event.target)) {
            return;
        }

        closeMenu();
    });

    const submitMove = async (trigger, parentSlug) => {
        activeTrigger = trigger;
        moveStatus.textContent = 'Moving page…';
        moveButton.disabled = true;

        try {
            const response = await fetch(trigger.dataset.moveUrl, {
                method: 'PUT',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': contextMenu.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ parent_slug: parentSlug }),
            });
            const payload = await response.json();

            if (!response.ok) {
                moveStatus.textContent = Object.values(payload.errors || {}).flat().join(' ')
                    || payload.message
                    || `Could not move this page (HTTP ${response.status}).`;

                return;
            }

            moveStatus.textContent = 'Page moved. Updating navigation…';
            window.setTimeout(() => window.location.reload(), 500);
        } catch (error) {
            console.error('Unable to move the tour package page.', error);
            moveStatus.textContent = error instanceof Error
                ? `Move failed: ${error.message}`
                : 'Move failed. Please try again.';
        } finally {
            moveButton.disabled = false;
        }
    };

    moveButton.addEventListener('click', () => {
        if (activeTrigger) {
            submitMove(activeTrigger, moveParentSelect.value);
        }
    });

    document.addEventListener('dragstart', (event) => {
        const trigger = event.target.closest('[data-navigation-draggable]');

        if (!trigger || !event.dataTransfer) {
            return;
        }

        event.dataTransfer.effectAllowed = 'move';
        event.dataTransfer.setData('text/plain', trigger.dataset.navigationDraggable);
    });

    document.addEventListener('dragover', (event) => {
        const target = event.target.closest('[data-navigation-parent-target]');

        if (!target) {
            return;
        }

        event.preventDefault();
        target.classList.add('is-drop-target');

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    });

    document.addEventListener('dragleave', (event) => {
        const target = event.target.closest('[data-navigation-parent-target]');

        if (target && !target.contains(event.relatedTarget)) {
            target.classList.remove('is-drop-target');
        }
    });

    document.addEventListener('drop', (event) => {
        const target = event.target.closest('[data-navigation-parent-target]');

        if (!target || !event.dataTransfer) {
            return;
        }

        event.preventDefault();
        navigationTargets.forEach((navigationTarget) => navigationTarget.classList.remove('is-drop-target'));

        const pageSlug = event.dataTransfer.getData('text/plain');
        const source = [...document.querySelectorAll('[data-navigation-draggable]')]
            .find((trigger) => trigger.dataset.navigationDraggable === pageSlug);

        if (!source || source.dataset.navigationDraggable === target.dataset.navigationParentTarget) {
            return;
        }

        openMenu(source, event, true);
        submitMove(source, target.dataset.navigationParentTarget);
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
