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

const seriesCarousel = document.querySelector('.series-carousel');

if (seriesCarousel) {
    const viewport = seriesCarousel.querySelector('.series-viewport');
    const track = seriesCarousel.querySelector('.series-track');
    const slides = [...track.querySelectorAll('.series-destination')];
    const previousButton = seriesCarousel.querySelector('.series-prev');
    const nextButton = seriesCarousel.querySelector('.series-next');
    const count = seriesCarousel.querySelector('.series-count');
    const tabs = [...document.querySelectorAll('.series-tabs .series-tab')];

    const slideStride = () => slides[0].getBoundingClientRect().width
        + Number.parseFloat(window.getComputedStyle(track).gap);

    const updateSeriesControls = () => {
        const activeIndex = Math.min(slides.length - 1, Math.round(viewport.scrollLeft / slideStride()));

        previousButton.disabled = activeIndex === 0;
        nextButton.disabled = activeIndex === slides.length - 1;
        count.textContent = `Destination ${activeIndex + 1} of ${slides.length}`;
        tabs.forEach((tab, index) => {
            if (index === activeIndex) {
                tab.setAttribute('aria-current', 'true');
            } else {
                tab.removeAttribute('aria-current');
            }
        });
    };

    previousButton.addEventListener('click', () => {
        viewport.scrollBy({ left: -slideStride(), behavior: 'smooth' });
    });

    nextButton.addEventListener('click', () => {
        viewport.scrollBy({ left: slideStride(), behavior: 'smooth' });
    });

    tabs.forEach((tab, index) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();
            viewport.scrollTo({ left: slideStride() * index, behavior: 'smooth' });
        });
    });

    viewport.addEventListener('scroll', updateSeriesControls, { passive: true });
    window.addEventListener('resize', updateSeriesControls);
    updateSeriesControls();
}

document.querySelectorAll('.series-details-dialog').forEach((seriesDetailsDialog) => {
    const openButton = document.querySelector(`[aria-controls="${seriesDetailsDialog.id}"]`);
    const closeButton = seriesDetailsDialog.querySelector('[data-series-details-close]');
    const track = seriesDetailsDialog.querySelector('.series-details-track');
    const slides = [...track.querySelectorAll('.series-details-slide')];
    const previousButton = seriesDetailsDialog.querySelector('.series-details-prev');
    const nextButton = seriesDetailsDialog.querySelector('.series-details-next');
    const count = seriesDetailsDialog.querySelector('.series-details-count');
    let activeIndex = 0;

    const showDetailsSlide = (index) => {
        activeIndex = Math.max(0, Math.min(index, slides.length - 1));
        track.style.transform = `translateX(-${activeIndex * 100}%)`;
        count.textContent = `Step ${activeIndex + 1} of ${slides.length}`;
        previousButton.disabled = activeIndex === 0;
        nextButton.disabled = activeIndex === slides.length - 1;

        slides.forEach((slide, slideIndex) => {
            slide.setAttribute('aria-hidden', String(slideIndex !== activeIndex));
            slide.inert = slideIndex !== activeIndex;
        });
    };

    openButton.addEventListener('click', () => {
        showDetailsSlide(0);
        seriesDetailsDialog.showModal();
        closeButton.focus();
    });

    closeButton.addEventListener('click', () => seriesDetailsDialog.close());
    previousButton.addEventListener('click', () => showDetailsSlide(activeIndex - 1));
    nextButton.addEventListener('click', () => showDetailsSlide(activeIndex + 1));
    showDetailsSlide(0);
});

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
    '.travel-moments__heading', '.travel-moment-card',
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

const campaignLayoutSelect = document.querySelector('[data-campaign-layout]');

if (campaignLayoutSelect) {
    const syncCampaignLayout = () => {
        const isCollage = campaignLayoutSelect.value === 'collage';

        document.querySelectorAll('[data-collage-only]').forEach((field) => {
            field.hidden = !isCollage;
            field.querySelectorAll('input, textarea').forEach((input) => {
                input.disabled = !isCollage;
            });
        });
        document.querySelectorAll('[data-banner-note]').forEach((note) => {
            note.hidden = isCollage;
        });
    };

    campaignLayoutSelect.addEventListener('change', syncCampaignLayout);
    syncCampaignLayout();
}

const previewForm = document.querySelector('form[data-form-slideshow]');

document.addEventListener('click', (event) => {
    const clearButton = event.target.closest('[data-clear-destination]');

    if (!clearButton) {
        return;
    }

    if (!window.confirm('Clear this destination? It will be removed from the page when you save.')) {
        return;
    }

    const panel = clearButton.closest('.homepage-form-panel');

    panel.querySelectorAll('input, textarea').forEach((field) => {
        if (field.type === 'checkbox' || field.type === 'radio') {
            field.checked = false;
        } else {
            field.value = '';
        }
    });
    panel.querySelector('[data-series-package-editor]')?.removeAttribute('open');
    panel.querySelector('[data-series-package-editor]')?.setAttribute('hidden', '');
    clearButton.remove();
});

document.addEventListener('input', (event) => {
    const nameField = event.target.closest('[data-series-name]');

    if (!nameField) {
        return;
    }

    const editor = nameField.closest('.homepage-form-panel').querySelector('[data-series-package-editor]');
    const destinationName = nameField.value.trim().toLocaleLowerCase();
    const hasEditablePackageDetails = ['seoul', 'kunming', 'da nang'].includes(destinationName);

    editor.toggleAttribute('hidden', !hasEditablePackageDetails);

    if (hasEditablePackageDetails) {
        editor.setAttribute('open', '');
    } else {
        editor.removeAttribute('open');
    }
});

document.querySelectorAll('[data-series-repeater]').forEach((repeater) => {
    const rowsContainer = repeater.querySelector('[data-series-repeater-rows]');
    const addButton = repeater.querySelector('[data-series-add-row]');
    const rowCount = repeater.querySelector('[data-series-row-count]');
    const previousButton = repeater.querySelector('[data-series-previous]');
    const nextButton = repeater.querySelector('[data-series-next]');
    const template = repeater.querySelector('template[data-series-row-template]');
    const namePrefix = repeater.dataset.namePrefix;
    const rowLabel = repeater.dataset.rowLabel;
    const minimumRows = Number(repeater.dataset.minRows);
    const maximumRows = Number(repeater.dataset.maxRows);

    if (!rowsContainer || !addButton || !rowCount || !template || !namePrefix || !rowLabel) {
        return;
    }

    let activeIndex = 0;

    const showRow = (index) => {
        const rows = [...rowsContainer.querySelectorAll('[data-series-repeat-row]')];
        activeIndex = rows.length ? Math.min(Math.max(index, 0), rows.length - 1) : 0;
        rowsContainer.style.transform = `translateX(-${activeIndex * 100}%)`;

        rows.forEach((row, rowIndex) => {
            const isActive = rowIndex === activeIndex;

            row.inert = !isActive;
            row.setAttribute('aria-hidden', String(!isActive));
        });

        if (previousButton) {
            previousButton.disabled = activeIndex === 0;
        }

        if (nextButton) {
            nextButton.disabled = rows.length === 0 || activeIndex >= rows.length - 1;
        }

        rowCount.textContent = rows.length
            ? `${rowLabel} ${activeIndex + 1} of ${rows.length}`
            : `0 of ${maximumRows} ${rowLabel.toLocaleLowerCase()}${maximumRows === 1 ? '' : 's'}`;
    };

    const updateRows = () => {
        const rows = [...rowsContainer.querySelectorAll('[data-series-repeat-row]')];

        rows.forEach((row, index) => {
            row.querySelector('[data-series-row-label]').textContent = `${rowLabel} ${index + 1}`;
            row.querySelectorAll('[data-series-field]').forEach((field) => {
                field.name = `${namePrefix}[${index}][${field.dataset.seriesField}]`;
            });
            row.querySelector('[data-series-remove-row]').disabled = rows.length <= minimumRows;
        });

        addButton.disabled = rows.length >= maximumRows;
        showRow(activeIndex);
    };

    previousButton?.addEventListener('click', () => showRow(activeIndex - 1));
    nextButton?.addEventListener('click', () => showRow(activeIndex + 1));
    rowsContainer.addEventListener('invalid', (event) => {
        const row = event.target.closest('[data-series-repeat-row]');
        const invalidIndex = [...rowsContainer.querySelectorAll('[data-series-repeat-row]')].indexOf(row);

        if (invalidIndex !== -1 && invalidIndex !== activeIndex) {
            showRow(invalidIndex);
        }
    }, true);

    addButton.addEventListener('click', () => {
        if (rowsContainer.querySelectorAll('[data-series-repeat-row]').length >= maximumRows) {
            return;
        }

        rowsContainer.append(template.content.cloneNode(true));
        activeIndex = rowsContainer.querySelectorAll('[data-series-repeat-row]').length - 1;
        updateRows();
        rowsContainer.querySelector('[data-series-repeat-row]:last-child [data-series-field]:not([type="file"])')?.focus();
    });

    rowsContainer.addEventListener('click', (event) => {
        if (!(event.target instanceof Element)) {
            return;
        }

        const removeButton = event.target.closest('[data-series-remove-row]');

        if (!removeButton) {
            return;
        }

        const row = removeButton.closest('[data-series-repeat-row]');
        const removedIndex = [...rowsContainer.querySelectorAll('[data-series-repeat-row]')].indexOf(row);
        const nextRow = row.nextElementSibling ?? row.previousElementSibling;
        row.remove();
        if (removedIndex < activeIndex) {
            activeIndex -= 1;
        }
        updateRows();

        if (nextRow) {
            nextRow.querySelector('[data-series-field]')?.focus();
        } else {
            addButton.focus();
        }
    });

    updateRows();
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
    document.body.append(contextMenu);
    const pageTitle = contextMenu.querySelector('[data-context-page-title]');
    const pageActions = contextMenu.querySelector('[data-context-page-actions]');
    const editLink = contextMenu.querySelector('[data-context-edit]');
    const viewLink = contextMenu.querySelector('[data-context-view]');
    const deleteButton = contextMenu.querySelector('[data-context-delete] button');
    const createTitle = contextMenu.querySelector('[data-context-create-title]');
    const createActions = contextMenu.querySelector('[data-context-create-actions]');
    const createLinks = [...contextMenu.querySelectorAll('[data-context-create]')];
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

        const hasPageActions = Boolean(
            trigger.dataset.editUrl
            || trigger.dataset.viewUrl
            || trigger.dataset.deleteUrl
        );

        pageTitle.hidden = !hasPageActions;
        pageActions.hidden = !hasPageActions;
        pageTitle.textContent = trigger.dataset.pageName || trigger.dataset.contextTitle || '';
        editLink.hidden = !trigger.dataset.editUrl;
        editLink.textContent = trigger.dataset.editLabel || editLink.dataset.defaultLabel;
        viewLink.hidden = !trigger.dataset.viewUrl;
        viewLink.textContent = trigger.dataset.viewLabel || viewLink.dataset.defaultLabel;
        moveActions.hidden = !trigger.dataset.moveUrl;
        moveStatus.textContent = '';
        deleteForm.hidden = !trigger.dataset.deleteUrl;
        deleteButton.textContent = trigger.dataset.deleteLabel || deleteButton.dataset.defaultLabel;
        deleteForm.dataset.confirmMessage = trigger.dataset.deleteConfirm
            || 'Delete this page permanently? This cannot be undone.';

        const parentSlug = trigger.dataset.pageSlug || trigger.dataset.navigationParentTarget || '';
        const parentName = trigger.dataset.pageName || trigger.dataset.navigationParentName;
        const canCreatePage = trigger.dataset.contextDisableCreate !== 'true';
        const customCreateUrl = trigger.dataset.contextCreateUrl;

        createTitle.hidden = !canCreatePage;
        createActions.hidden = !canCreatePage;
        createTitle.textContent = trigger.dataset.contextCreateTitle || (parentSlug
            ? `Create new page inside ${parentName}`
            : 'Create new page');

        createLinks.forEach((link, index) => {
            if (!link.dataset.defaultHref) {
                link.dataset.defaultHref = link.href;
            }

            if (!canCreatePage) {
                link.hidden = true;

                return;
            }

            link.hidden = Boolean(customCreateUrl && index > 0);
            link.textContent = customCreateUrl && index === 0
                ? trigger.dataset.contextCreateLabel || 'Create new page'
                : link.dataset.defaultLabel;

            if (customCreateUrl && index === 0) {
                link.href = customCreateUrl;

                return;
            }

            const url = new URL(link.dataset.defaultHref, window.location.origin);
            url.searchParams.delete('parent');

            if (parentSlug) {
                url.searchParams.set('parent', parentSlug);
            }

            link.href = url.toString();
        });

        if (trigger.dataset.editUrl) {
            editLink.href = trigger.dataset.editUrl;
        }

        if (trigger.dataset.viewUrl) {
            viewLink.href = trigger.dataset.viewUrl;
        }

        if (trigger.dataset.deleteUrl) {
            deleteForm.action = trigger.dataset.deleteUrl;
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

    if (target instanceof Element && !target.closest('.admin-sidebar, .admin-menu-toggle, .admin-context-menu')) {
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

document.querySelectorAll('[data-china-open]').forEach((opener) => {
    const modal = document.getElementById(opener.dataset.chinaOpen);

    if (!modal) {
        return;
    }

    const track = modal.querySelector('.china-modal-track');
    const slides = [...modal.querySelectorAll('.china-slide')];
    const dotsWrap = modal.querySelector('.china-modal-dots');
    const count = modal.querySelector('[data-china-count]');
    const prev = modal.querySelector('[data-china-prev]');
    const next = modal.querySelector('[data-china-next]');
    let index = 0;

    const goTo = (target) => {
        index = Math.max(0, Math.min(slides.length - 1, target));
        track.style.transform = `translateX(-${index * 100}%)`;
        slides.forEach((slide, slideIndex) => slide.classList.toggle('is-active', slideIndex === index));
        [...dotsWrap.children].forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
        count.textContent = `${index + 1} / ${slides.length}`;
        prev.disabled = index === 0;
        next.disabled = index === slides.length - 1;
    };

    slides.forEach((_, slideIndex) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'china-modal-dot';
        dot.setAttribute('aria-label', `Go to slide ${slideIndex + 1}`);
        dot.addEventListener('click', () => goTo(slideIndex));
        dotsWrap.append(dot);
    });

    opener.addEventListener('click', (event) => {
        event.preventDefault();
        goTo(0);
        modal.showModal();
    });

    modal.querySelector('[data-china-close]').addEventListener('click', () => modal.close());
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.close();
        }
    });
    prev.addEventListener('click', () => goTo(index - 1));
    next.addEventListener('click', () => goTo(index + 1));
    modal.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            goTo(index + 1);
        } else if (event.key === 'ArrowLeft') {
            goTo(index - 1);
        }
    });

    let touchStartX = 0;
    track.addEventListener('touchstart', (event) => {
        touchStartX = event.touches[0].clientX;
    }, { passive: true });
    track.addEventListener('touchend', (event) => {
        const distance = event.changedTouches[0].clientX - touchStartX;

        if (Math.abs(distance) > 50) {
            goTo(index + (distance < 0 ? 1 : -1));
        }
    });
});

document.querySelectorAll('[data-about-open]').forEach((opener) => {
    const modal = document.getElementById(opener.dataset.aboutOpen);

    if (!modal) {
        return;
    }

    opener.addEventListener('click', () => modal.showModal());
});

document.querySelectorAll('.china-about-modal').forEach((modal) => {
    const main = modal.querySelector('[data-about-main]');

    modal.querySelectorAll('[data-about-close]').forEach((button) => button.addEventListener('click', () => modal.close()));
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.close();
        }
    });

    modal.querySelectorAll('[data-about-thumb]').forEach((thumb) => {
        thumb.addEventListener('click', () => {
            main.src = thumb.dataset.aboutThumb;
            main.alt = thumb.dataset.aboutAlt;
            modal.querySelectorAll('[data-about-thumb]').forEach((other) => other.classList.toggle('is-active', other === thumb));
        });
    });
});

document.querySelectorAll('[data-china-slides]').forEach((editor) => {
    const list = editor.querySelector('[data-china-slide-list]');
    const template = editor.querySelector('[data-china-slide-template]');
    let nextIndex = list.querySelectorAll('[data-china-slide]').length;

    const renumber = () => {
        list.querySelectorAll('[data-china-slide]').forEach((slide, position) => {
            slide.querySelector('[data-china-slide-number]').textContent = String(position + 1).padStart(2, '0');
        });
    };

    editor.querySelector('[data-china-add-slide]').addEventListener('click', () => {
        const html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
        list.insertAdjacentHTML('beforeend', html);
        renumber();
        const added = list.lastElementChild;
        added.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        added.querySelector('input')?.focus({ preventScroll: true });
    });

    list.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-china-remove-slide]');

        if (remove) {
            remove.closest('[data-china-slide]').remove();
            renumber();
        }
    });

    renumber();
});

document.querySelectorAll('[data-china-search]').forEach((toolbar) => {
    const items = [...document.querySelectorAll('[data-china-item]')];
    const input = toolbar.querySelector('[data-china-search-input]');
    const clear = toolbar.querySelector('[data-china-search-clear]');
    const filters = [...toolbar.querySelectorAll('[data-china-filter]')];
    const visibleCount = toolbar.querySelector('[data-china-visible]');
    const emptyState = document.querySelector('[data-china-no-results]');
    let activeFilter = 'all';

    const apply = () => {
        const terms = input.value.toLowerCase().split(/\s+/).filter(Boolean);
        let visible = 0;

        items.forEach((item) => {
            const matchesText = terms.every((term) => item.dataset.search.includes(term));
            const matchesFilter = activeFilter === 'all'
                || (activeFilter === 'hot' && item.dataset.hot === '1')
                || (activeFilter === 'details' && item.dataset.details === '1');
            const show = matchesText && matchesFilter;

            item.hidden = !show;
            visible += show ? 1 : 0;
        });

        visibleCount.textContent = String(visible);
        clear.hidden = input.value === '';
        emptyState.hidden = visible > 0;
    };

    input.addEventListener('input', apply);
    clear.addEventListener('click', () => {
        input.value = '';
        input.focus();
        apply();
    });
    filters.forEach((button) => button.addEventListener('click', () => {
        activeFilter = button.dataset.chinaFilter;
        filters.forEach((other) => other.classList.toggle('is-active', other === button));
        apply();
    }));
});

document.querySelectorAll('[data-china-slider]').forEach((slider) => {
    const track = slider.querySelector('.china-edit-track');
    const prev = slider.querySelector('[data-china-slider-prev]');
    const next = slider.querySelector('[data-china-slider-next]');

    const update = () => {
        prev.disabled = track.scrollLeft <= 4;
        next.disabled = track.scrollLeft + track.clientWidth >= track.scrollWidth - 4;
    };

    prev.addEventListener('click', () => track.scrollBy({ left: -track.clientWidth * 0.8 }));
    next.addEventListener('click', () => track.scrollBy({ left: track.clientWidth * 0.8 }));
    track.addEventListener('scroll', update, { passive: true });
    new MutationObserver(update).observe(track, { childList: true });
    window.addEventListener('resize', update);
    update();
});

// Empties every field of the About this place editor so new content can be typed from scratch.
document.querySelectorAll('.aceh-itinerary-grid').forEach((grid) => {
    const cards = [...grid.children];
    const nav = document.createElement('div');

    nav.className = 'itinerary-slider-nav';
    nav.innerHTML = `<button type="button" class="itinerary-slider-arrow" data-dir="-1" aria-label="Previous itinerary">‹</button>
        <div class="itinerary-slider-dots">${cards.map((_, index) => `<button type="button" class="itinerary-slider-dot" data-go="${index}" aria-label="Show itinerary ${index + 1}"></button>`).join('')}</div>
        <button type="button" class="itinerary-slider-arrow" data-dir="1" aria-label="Next itinerary">›</button>`;
    grid.after(nav);

    const prev = nav.querySelector('[data-dir="-1"]');
    const next = nav.querySelector('[data-dir="1"]');
    const dots = [...nav.querySelectorAll('.itinerary-slider-dot')];
    const maxScroll = () => grid.scrollWidth - grid.clientWidth;
    const currentIndex = () => {
        const step = cards[1] ? cards[1].offsetLeft - cards[0].offsetLeft : grid.clientWidth;

        return Math.min(cards.length - 1, Math.round(grid.scrollLeft / step));
    };
    const sync = () => {
        const index = grid.scrollLeft >= maxScroll() - 2 ? cards.length - 1 : currentIndex();

        nav.hidden = maxScroll() <= 2;
        prev.disabled = grid.scrollLeft <= 2;
        next.disabled = grid.scrollLeft >= maxScroll() - 2;
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === index));
    };

    nav.addEventListener('click', (event) => {
        const control = event.target.closest('button');

        if (!control) {
            return;
        }

        const target = control.dataset.go !== undefined ? Number(control.dataset.go) : currentIndex() + Number(control.dataset.dir);
        const card = cards[Math.min(Math.max(target, 0), cards.length - 1)];

        grid.scrollTo({ left: card.offsetLeft - cards[0].offsetLeft, behavior: 'smooth' });
    });

    grid.addEventListener('scroll', sync, { passive: true });
    window.addEventListener('resize', sync);
    sync();
});

document.querySelectorAll('[data-about-clear]').forEach((button) => {
    button.addEventListener('click', () => {
        const panel = button.closest('.china-about-editor');

        if (!panel || !window.confirm('Clear all About this place fields?')) {
            return;
        }

        panel.querySelectorAll('input[type=text], input:not([type]), textarea').forEach((field) => {
            field.value = '';
        });
        panel.querySelectorAll('input[type=checkbox]').forEach((box) => {
            box.checked = true;
        });
        panel.querySelectorAll('input[type=file]').forEach((file) => {
            file.value = '';
        });
        panel.querySelector('input')?.focus();
    });
});
