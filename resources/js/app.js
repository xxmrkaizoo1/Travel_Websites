const menuToggle = document.querySelector('.menu-toggle');
const primaryNav = document.querySelector('#primary-nav');
const dropdownItems = [...(primaryNav?.querySelectorAll('.has-dropdown, .has-submenu') ?? [])];

const closeDropdowns = (except = null) => {
    dropdownItems.forEach((item) => {
        if (item === except || item.contains(except)) {
            return;
        }

        item.classList.remove('is-open');
        item.classList.add('is-dismissed');
        item.querySelector('.nav-trigger, .submenu-trigger')?.setAttribute('aria-expanded', 'false');
    });
};

const closeMobileNav = () => {
    menuToggle?.setAttribute('aria-expanded', 'false');
    menuToggle?.setAttribute('aria-label', 'Open navigation');
    primaryNav?.classList.remove('is-open');
    closeDropdowns();
};

const NAV_VISIBLE_ITEMS = 5;

// Long menus show their first five links; the rest sit behind a "See more" toggle.
primaryNav?.querySelectorAll('.dropdown-menu, .submenu-menu').forEach((menu, menuIndex) => {
    if (menu.querySelector(':scope > [data-series-nav-more]')) {
        return;
    }

    const menuItems = [...menu.children].filter((child) => child.matches('a, .nav-subitem'));

    if (menuItems.length <= NAV_VISIBLE_ITEMS) {
        return;
    }

    const extraItems = menuItems.slice(NAV_VISIBLE_ITEMS);
    const showsCurrentPage = extraItems.some((item) => item.matches('.is-current, [aria-current]') || item.querySelector('.is-current, [aria-current]'));
    const moreButton = document.createElement('button');

    menu.id ||= `nav-menu-${menuIndex}`;
    moreButton.type = 'button';
    moreButton.className = 'series-nav-more';
    moreButton.dataset.navMore = '';
    moreButton.setAttribute('aria-controls', menu.id);
    moreButton.innerHTML = '<span data-series-nav-more-label></span><span aria-hidden="true"></span>';
    menu.append(moreButton);

    const setExpanded = (isExpanded) => {
        extraItems.forEach((item) => {
            item.hidden = !isExpanded;
        });
        moreButton.setAttribute('aria-expanded', String(isExpanded));
        moreButton.querySelector('[data-series-nav-more-label]').textContent = isExpanded ? 'See less' : `See more (${extraItems.length})`;
        moreButton.querySelector('[aria-hidden="true"]').textContent = isExpanded ? '−' : '＋';
    };

    moreButton.addEventListener('click', () => setExpanded(moreButton.getAttribute('aria-expanded') !== 'true'));
    setExpanded(showsCurrentPage);
});

dropdownItems.forEach((item) => {
    item.addEventListener('pointerenter', (event) => {
        if (event.pointerType !== 'touch') {
            item.classList.remove('is-dismissed');
        }
    });

    item.addEventListener('focusin', () => item.classList.remove('is-dismissed'));
});

primaryNav?.addEventListener('click', (event) => {
    const target = event.target;
    const moreButton = target instanceof Element ? target.closest('[data-series-nav-more]') : null;

    if (moreButton instanceof HTMLButtonElement) {
        const extraDestinations = document.getElementById(moreButton.getAttribute('aria-controls'));
        const isExpanded = moreButton.getAttribute('aria-expanded') === 'true';

        if (extraDestinations) {
            extraDestinations.hidden = isExpanded;
        }

        moreButton.setAttribute('aria-expanded', String(!isExpanded));
        moreButton.querySelector('[data-series-nav-more-label]').textContent = isExpanded ? 'See more' : 'See less';
        moreButton.querySelector('[aria-hidden="true"]').textContent = isExpanded ? '＋' : '−';

        return;
    }

    const trigger = target instanceof Element ? target.closest('.nav-trigger, .submenu-trigger') : null;

    if (!trigger || !primaryNav.contains(trigger)) {
        return;
    }

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

    // Swipe left or right to change slides; phones hide the arrows.
    let swipeStart = null;

    carousel.addEventListener('touchstart', (event) => {
        swipeStart = { x: event.touches[0].clientX, y: event.touches[0].clientY };
    }, { passive: true });

    carousel.addEventListener('touchend', (event) => {
        if (!swipeStart) {
            return;
        }

        const deltaX = event.changedTouches[0].clientX - swipeStart.x;
        const deltaY = event.changedTouches[0].clientY - swipeStart.y;
        swipeStart = null;

        if (Math.abs(deltaX) > 50 && Math.abs(deltaX) > Math.abs(deltaY)) {
            showSlide(activeIndex + (deltaX < 0 ? 1 : -1));
        }
    });

    document.querySelectorAll('[data-slide-to]').forEach((link) => {
        link.addEventListener('click', () => {
            showSlide(Number(link.dataset.slideTo));
        });
    });
}

document.querySelectorAll('[data-sh-journey-carousel]').forEach((carousel) => {
    const viewport = carousel.querySelector('.sh-journey-viewport');
    const track = carousel.querySelector('.sh-journey-track');
    const slides = [...carousel.querySelectorAll('.sh-journey-slide')];
    const selectors = [...carousel.querySelectorAll('[data-sh-journey-select]')];
    const previousButton = carousel.querySelector('[data-sh-journey-previous]');
    const nextButton = carousel.querySelector('[data-sh-journey-next]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let currentIndex = 0;
    let pointerStartX = null;
    let pointerStartY = null;
    let dragStartOffset = 0;
    let dragMoved = false;
    let suppressClick = false;

    const showSlide = (index) => {
        currentIndex = Math.max(0, Math.min(index, slides.length - 1));
        track.style.transform = `translateX(-${currentIndex * viewport.clientWidth}px)`;
        previousButton.disabled = currentIndex === 0;
        nextButton.disabled = currentIndex === slides.length - 1;
        slides.forEach((slide, slideIndex) => {
            slide.setAttribute('aria-hidden', String(slideIndex !== currentIndex));
            slide.inert = slideIndex !== currentIndex;
            slide.classList.toggle('is-active', slideIndex === currentIndex);

            const video = slide.querySelector('[data-sh-journey-video]');

            if (video && slideIndex === currentIndex && !prefersReducedMotion) {
                video.preload = 'auto';
                video.play().catch(() => {});
            } else {
                video?.pause();
            }
        });
        selectors.forEach((selector) => {
            if (Number(selector.dataset.shJourneyIndex) === currentIndex) {
                selector.setAttribute('aria-current', 'true');
            } else {
                selector.removeAttribute('aria-current');
            }
        });
    };

    previousButton.addEventListener('click', () => showSlide(currentIndex - 1));
    nextButton.addEventListener('click', () => showSlide(currentIndex + 1));
    selectors.forEach((selector) => selector.addEventListener('click', () => showSlide(Number(selector.dataset.shJourneyIndex))));
    viewport.addEventListener('pointerdown', (event) => {
        if (event.target instanceof Element && event.target.closest('.sh-journey-controls')) {
            return;
        }

        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        pointerStartX = event.clientX;
        pointerStartY = event.clientY;
        dragStartOffset = -currentIndex * viewport.clientWidth;
        dragMoved = false;
    });
    viewport.addEventListener('pointermove', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distanceX = event.clientX - pointerStartX;
        const distanceY = event.clientY - pointerStartY;

        if (!dragMoved && (Math.abs(distanceX) < 6 || Math.abs(distanceX) <= Math.abs(distanceY))) {
            return;
        }

        // Capture only once a swipe starts: capturing on press sends the click to the viewport, so links never open.
        if (!dragMoved) {
            viewport.classList.add('is-dragging');
            track.style.transition = 'none';
            viewport.setPointerCapture(event.pointerId);
        }

        dragMoved = true;
        event.preventDefault();
        const offset = Math.max(-viewport.clientWidth * (slides.length - 1), Math.min(0, dragStartOffset + distanceX));
        track.style.transform = `translateX(${offset}px)`;
    });
    viewport.addEventListener('pointerup', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distance = event.clientX - pointerStartX;
        const wasDragged = dragMoved;
        pointerStartX = null;
        pointerStartY = null;
        dragMoved = false;
        viewport.classList.remove('is-dragging');
        track.style.removeProperty('transition');

        if (wasDragged) {
            showSlide(currentIndex + (Math.abs(distance) > 48 ? (distance < 0 ? 1 : -1) : 0));
            suppressClick = true;
            window.setTimeout(() => { suppressClick = false; }, 0);
        } else {
            showSlide(currentIndex);
        }
    });
    viewport.addEventListener('pointercancel', () => {
        pointerStartX = null;
        pointerStartY = null;
        dragMoved = false;
        viewport.classList.remove('is-dragging');
        track.style.removeProperty('transition');
        showSlide(currentIndex);
    });
    viewport.addEventListener('click', (event) => {
        if (suppressClick) {
            event.preventDefault();
            event.stopImmediatePropagation();
            suppressClick = false;
        }
    }, true);
    window.addEventListener('resize', () => showSlide(currentIndex));
    showSlide(0);
});

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

document.querySelectorAll('[data-popular-tour-carousel]').forEach((carousel) => {
    const viewport = carousel.querySelector('.popular-tour-viewport');
    const track = carousel.querySelector('.popular-tour-track');
    const cards = [...carousel.querySelectorAll('[data-popular-tour-card]')];
    const dots = [...carousel.querySelectorAll('[data-popular-tour-dot]')];
    let activeIndex = Math.max(0, Math.min(Number(carousel.dataset.activeIndex) || 0, cards.length - 1));
    let pointerStartX = null;
    let pointerStartY = null;
    let dragStartOffset = 0;
    let dragMoved = false;
    let suppressClick = false;

    const cardStride = () => cards[0].offsetWidth
        + Number.parseFloat(window.getComputedStyle(track).gap);

    const cardOffset = (index) => viewport.clientWidth / 2
        - (index * cardStride() + cards[index].offsetWidth / 2);

    const showCard = (index) => {
        activeIndex = Math.max(0, Math.min(index, cards.length - 1));
        track.style.transform = `translateX(${cardOffset(activeIndex)}px)`;
        cards.forEach((card, cardIndex) => card.classList.toggle('is-active', cardIndex === activeIndex));
        dots.forEach((dot, dotIndex) => {
            if (dotIndex === activeIndex) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    };

    dots.forEach((dot, index) => dot.addEventListener('click', () => showCard(index)));
    viewport.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        pointerStartX = event.clientX;
        pointerStartY = event.clientY;
        dragStartOffset = cardOffset(activeIndex);
        dragMoved = false;
    });
    viewport.addEventListener('pointermove', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distanceX = event.clientX - pointerStartX;
        const distanceY = event.clientY - pointerStartY;

        if (!dragMoved && (Math.abs(distanceX) < 6 || Math.abs(distanceX) <= Math.abs(distanceY))) {
            return;
        }

        // Capture only once a swipe starts: capturing on press sends the click to the viewport, so links never open.
        if (!dragMoved) {
            viewport.classList.add('is-dragging');
            track.style.transition = 'none';
            viewport.setPointerCapture(event.pointerId);
        }

        dragMoved = true;
        event.preventDefault();
        const minOffset = cardOffset(cards.length - 1);
        const maxOffset = cardOffset(0);
        const offset = Math.max(minOffset, Math.min(maxOffset, dragStartOffset + distanceX));
        track.style.transform = `translateX(${offset}px)`;
    });
    viewport.addEventListener('pointerup', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distance = event.clientX - pointerStartX;
        const wasDragged = dragMoved;
        pointerStartX = null;
        pointerStartY = null;
        dragMoved = false;
        viewport.classList.remove('is-dragging');
        track.style.removeProperty('transition');

        if (wasDragged) {
            const steps = Math.max(1, Math.round(Math.abs(distance) / cardStride()));
            showCard(activeIndex + (distance < 0 ? steps : -steps));
            suppressClick = true;
            window.setTimeout(() => { suppressClick = false; }, 0);
        } else {
            showCard(activeIndex);
        }
    });
    viewport.addEventListener('pointercancel', () => {
        pointerStartX = null;
        pointerStartY = null;
        dragMoved = false;
        viewport.classList.remove('is-dragging');
        track.style.removeProperty('transition');
        showCard(activeIndex);
    });
    viewport.addEventListener('click', (event) => {
        if (suppressClick) {
            event.preventDefault();
            event.stopImmediatePropagation();
            suppressClick = false;
        }
    }, true);
    window.addEventListener('resize', () => showCard(activeIndex));
    showCard(activeIndex);
});

document.querySelectorAll('[data-series-page-slider]').forEach((slider) => {
    const panels = [...slider.querySelectorAll('[data-series-page-slide]')];
    const pageLinks = [...document.querySelectorAll('[data-series-page-target]')];
    const previousButton = slider.querySelector('[data-series-page-previous]');
    const nextButton = slider.querySelector('[data-series-page-next]');
    const count = slider.querySelector('[data-series-page-count]');
    const hashSlug = window.location.hash.startsWith('#page-') ? window.location.hash.slice(6) : panels[0]?.dataset.seriesPageSlide;
    let activeIndex = Math.max(0, panels.findIndex((panel) => panel.dataset.seriesPageSlide === hashSlug));

    const showPage = (index, updateUrl = false) => {
        activeIndex = Math.max(0, Math.min(index, panels.length - 1));
        const activeSlug = panels[activeIndex].dataset.seriesPageSlide;

        panels.forEach((panel, panelIndex) => {
            const isActive = panelIndex === activeIndex;
            panel.hidden = !isActive;
            panel.inert = !isActive;
            panel.setAttribute('aria-hidden', String(!isActive));
        });

        previousButton.disabled = activeIndex === 0;
        nextButton.disabled = activeIndex === panels.length - 1;
        count.textContent = `Page ${activeIndex + 1} of ${panels.length}`;

        pageLinks.forEach((link) => {
            const isActive = link.dataset.seriesPageTarget === activeSlug;
            link.classList.toggle('is-current', isActive);

            if (isActive) {
                link.setAttribute('aria-current', 'page');
            } else {
                link.removeAttribute('aria-current');
            }
        });

        if (updateUrl) {
            window.history.pushState(null, '', `#page-${activeSlug}`);
        }
    };

    pageLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            const targetIndex = panels.findIndex((panel) => panel.dataset.seriesPageSlide === link.dataset.seriesPageTarget);

            if (targetIndex === -1) {
                return;
            }

            event.preventDefault();
            showPage(targetIndex, true);
        });
    });

    previousButton.addEventListener('click', () => showPage(activeIndex - 1));
    nextButton.addEventListener('click', () => showPage(activeIndex + 1));
    window.addEventListener('popstate', () => {
        const poppedSlug = window.location.hash.startsWith('#page-') ? window.location.hash.slice(6) : panels[0].dataset.seriesPageSlide;
        const poppedIndex = panels.findIndex((panel) => panel.dataset.seriesPageSlide === poppedSlug);

        if (poppedIndex !== -1) {
            showPage(poppedIndex);
        }
    });
    showPage(activeIndex);
});

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
        slides[activeIndex].scrollTop = 0;
        seriesDetailsDialog.style.setProperty('--series-progress', `${((activeIndex + 1) / slides.length) * 100}%`);

        slides.forEach((slide, slideIndex) => {
            slide.setAttribute('aria-hidden', String(slideIndex !== activeIndex));
            slide.inert = slideIndex !== activeIndex;
            slide.classList.toggle('is-active', slideIndex === activeIndex);
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

const galleryAlbum = document.querySelector('#gallery-album');

/**
 * Builds popup albums from the admin gallery editor's current (possibly unsaved) form values.
 */
const collectEditorAlbums = (editor) => {
    const resolveImage = (value, fileInput) => {
        const file = fileInput?.files?.[0];

        if (file) {
            return URL.createObjectURL(file);
        }

        const path = (value ?? '').trim();

        if (path === '' || /^https?:\/\//.test(path)) {
            return path;
        }

        return (path.startsWith('images/') ? editor.dataset.assetBase : editor.dataset.storageBase) + path;
    };
    const field = (slide, name) => slide.querySelector(`[data-gallery-field="${name}"]`);

    return [...editor.querySelectorAll('[data-gallery-slides] [data-gallery-slide]')].map((slide) => {
        const photos = [...slide.querySelectorAll('.gallery-album-slot')].map((slot, slotIndex) => ({
            src: resolveImage(field(slot, `photos][${slotIndex}][image`)?.value, field(slot, `photos][${slotIndex}][image_file`)),
            caption: field(slot, `photos][${slotIndex}][caption`)?.value.trim() ?? '',
        })).filter((photo) => photo.src !== '');
        const postcard = resolveImage(field(slide, 'photo').value, field(slide, 'photo_file'));

        return {
            country: field(slide, 'country').value.trim() || slide.querySelector('[data-slide-legend]').textContent,
            flag: resolveImage(field(slide, 'flag').value, field(slide, 'flag_file')),
            photos: photos.length ? photos : [{ src: postcard, caption: field(slide, 'photo_alt').value.trim() }].filter((photo) => photo.src !== ''),
        };
    });
};

if (galleryAlbum) {
    let albums = [];
    const stage = galleryAlbum.querySelector('[data-album-stage]');
    const image = galleryAlbum.querySelector('[data-album-image]');
    const caption = galleryAlbum.querySelector('[data-album-caption]');
    const counter = galleryAlbum.querySelector('[data-album-counter]');
    const thumbs = galleryAlbum.querySelector('[data-album-thumbs]');
    const flag = galleryAlbum.querySelector('[data-album-flag]');
    const country = galleryAlbum.querySelector('[data-album-country]');
    const destinationButtons = [...galleryAlbum.querySelectorAll('[data-album-destination]')];
    let albumIndex = 0;
    let photoIndex = 0;
    let lastOpener = null;
    let swipeStartX = null;

    const wrap = (index, length) => (index + length) % length;
    const photos = () => albums[albumIndex].photos;

    const showPhoto = (index) => {
        photoIndex = wrap(index, photos().length);
        const photo = photos()[photoIndex];

        galleryAlbum.classList.add('is-changing');
        window.setTimeout(() => {
            image.src = photo.src;
            image.alt = photo.caption || albums[albumIndex].country;
            caption.textContent = photo.caption;
            caption.hidden = photo.caption === '';
            galleryAlbum.classList.remove('is-changing');
        }, 160);

        counter.textContent = `${photoIndex + 1} / ${photos().length}`;
        thumbs.querySelectorAll('button').forEach((thumb, thumbIndex) => {
            thumb.classList.toggle('is-active', thumbIndex === photoIndex);
            thumb.setAttribute('aria-current', String(thumbIndex === photoIndex));
        });
        thumbs.querySelector('.is-active')?.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });

        [photoIndex - 1, photoIndex + 1].forEach((neighbour) => {
            new Image().src = photos()[wrap(neighbour, photos().length)].src;
        });
    };

    const showAlbum = (index) => {
        albumIndex = wrap(index, albums.length);
        const album = albums[albumIndex];

        flag.src = album.flag;
        country.textContent = album.country;
        thumbs.replaceChildren(...album.photos.map((photo, thumbIndex) => {
            const thumb = document.createElement('button');
            const thumbImage = document.createElement('img');

            thumb.type = 'button';
            thumb.dataset.albumPhoto = String(thumbIndex);
            thumb.setAttribute('aria-label', `Photo ${thumbIndex + 1}${photo.caption ? `: ${photo.caption}` : ''}`);
            thumbImage.src = photo.src;
            thumbImage.alt = '';
            thumbImage.loading = 'lazy';
            thumb.append(thumbImage);

            return thumb;
        }));

        const hasManyPhotos = album.photos.length > 1;
        galleryAlbum.querySelectorAll('[data-album-step]').forEach((arrow) => {
            arrow.hidden = !hasManyPhotos;
        });
        thumbs.hidden = !hasManyPhotos;

        destinationButtons.forEach((button) => {
            const target = albums[wrap(albumIndex + Number(button.dataset.albumDestination), albums.length)];

            button.hidden = albums.length < 2;
            button.querySelector('[data-album-destination-label]').textContent = target.country;
            button.setAttribute('aria-label', `View ${target.country} photo gallery`);
        });

        showPhoto(0);
    };

    const openAlbum = (albumList, index, opener) => {
        albums = albumList;
        lastOpener = opener;
        showAlbum(index);
        galleryAlbum.showModal();
        document.documentElement.classList.add('has-gallery-album');
    };

    const pageAlbumData = document.querySelector('#gallery-album-data');

    if (pageAlbumData) {
        const pageAlbums = JSON.parse(pageAlbumData.textContent);

        document.querySelectorAll('[data-gallery-album-open]').forEach((opener) => {
            opener.addEventListener('click', () => openAlbum(pageAlbums, Number(opener.dataset.galleryAlbumOpen), opener));
        });
    }

    document.addEventListener('click', (event) => {
        const preview = event.target.closest('[data-gallery-album-preview]');

        if (!preview) {
            return;
        }

        const editor = preview.closest('[data-gallery-editor]');
        const slides = [...editor.querySelectorAll('[data-gallery-slides] [data-gallery-slide]')];
        const editorAlbums = collectEditorAlbums(editor);
        const previewIndex = slides.indexOf(preview.closest('[data-gallery-slide]'));
        const previewable = editorAlbums.filter((album) => album.photos.length > 0);

        if (editorAlbums[previewIndex].photos.length === 0) {
            window.alert('Add a postcard photo or at least one popup photo to preview this destination.');

            return;
        }

        openAlbum(previewable, previewable.indexOf(editorAlbums[previewIndex]), preview);
    });

    galleryAlbum.addEventListener('click', (event) => {
        if (event.target === galleryAlbum || event.target.closest('[data-album-close]')) {
            galleryAlbum.close();

            return;
        }

        const step = event.target.closest('[data-album-step]');
        const thumb = event.target.closest('[data-album-photo]');
        const destination = event.target.closest('[data-album-destination]');

        if (step) {
            showPhoto(photoIndex + Number(step.dataset.albumStep));
        } else if (thumb) {
            showPhoto(Number(thumb.dataset.albumPhoto));
        } else if (destination) {
            showAlbum(albumIndex + Number(destination.dataset.albumDestination));
        }
    });

    galleryAlbum.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            showPhoto(photoIndex + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });

    stage.addEventListener('pointerdown', (event) => {
        swipeStartX = event.clientX;
    });

    stage.addEventListener('pointerup', (event) => {
        if (swipeStartX === null) {
            return;
        }

        const distance = event.clientX - swipeStartX;
        swipeStartX = null;

        if (Math.abs(distance) > 40 && photos().length > 1) {
            showPhoto(photoIndex + (distance < 0 ? 1 : -1));
        }
    });

    galleryAlbum.addEventListener('close', () => {
        document.documentElement.classList.remove('has-gallery-album');
        lastOpener?.focus();
    });
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
    '.welcome-section h2', '.welcome-subtitle', '.value-card', '.services-section h2', '.service-card', '.service-tile', '.footer-inner > *',
    '.island-intro-copy', '.island-photo-stack', '.island-section-heading', '.island-highlight', '.destination-carousel',
    '.aceh-itinerary-heading', '.aceh-day', '.bali-gallery', '.aceh-details-card', '.aceh-hotel-card', '.island-contact > *',
    '.series-head', '.series-advantage', '.series-flag', '.series-photo',
    '.travel-moments__heading', '.travel-moment-card',
    '.focus-tour-card', '.focus-tours-cta__inner', '.booking-toc__card', '.booking-card', '.destination-state',
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

        target.style.setProperty('--reveal-delay', `${Math.min(Math.max(siblings.indexOf(target), 0), 5) * 110}ms`);
    });

    revealTargets.forEach((target) => {
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

const campaignLayoutPicker = document.querySelector('[data-campaign-layout]');

if (campaignLayoutPicker) {
    const syncCampaignLayout = () => {
        const isCollage = campaignLayoutPicker.querySelector('input[name="layout"]:checked')?.value !== 'banner';

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

    campaignLayoutPicker.addEventListener('change', syncCampaignLayout);
    syncCampaignLayout();
}

// Forms whose fields are not page sections opt out of the "Shown in" preview.
const previewForm = document.querySelector('form[data-form-slideshow]:not([data-no-field-preview])');

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
    const hasEditablePackageDetails = destinationName !== '';

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
    const escapeHtml = (value) => String(value).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);
    const shorten = (value, length) => (value.length > length ? `${value.slice(0, length - 1)}…` : value);
    const fieldValue = (name) => previewForm.querySelector(`[name="${name}"]`)?.value.trim() ?? '';
    const isNavField = (field) => /^(name|parent_slug)$/.test(field.name);
    const isMetaField = (field) => /^meta_/.test(field.name);

    // Each form panel is one section of the public page; its heading decides how the section is drawn.
    const panelKinds = [
        [/banner|hero|campaign|slide/, 'hero'],
        [/contact|closing|call to action|enquir|plan your/, 'contact'],
        [/gallery|photo|album|moment/, 'gallery'],
        [/include|exclude|condition|detail/, 'list'],
        [/card|destination|highlight|itinerar|tour|package|service|reason|state|region|perk|client|hotel|accommodation|series/, 'cards'],
        [/intro|about|welcome|story/, 'intro'],
    ];
    const sectionHeights = { hero: 62, intro: 46, cards: 48, gallery: 40, list: 40, contact: 34, text: 30 };

    const panelInfo = (panel) => {
        const title = panel.querySelector('h2')?.textContent.trim() ?? 'This section';
        const description = panel.querySelector('.homepage-form-panel-heading p')?.textContent.trim() ?? '';
        const kindOf = (text) => panelKinds.find(([pattern]) => pattern.test(text.toLowerCase()))?.[1];
        const kind = kindOf(title) ?? kindOf(description) ?? 'text';

        return { panel, title, description, kind };
    };

    // Panels holding only the page name, menu position or browser details are not page sections.
    const sectionPanels = () => [...previewForm.querySelectorAll('.homepage-form-panel')]
        .filter((panel) => [...panel.querySelectorAll('input[name], textarea[name], select[name]')].some((field) => !isNavField(field) && !isMetaField(field) && field.name !== 'theme' && field.type !== 'hidden'))
        .map(panelInfo)
        // Pages always open with the banner and end with the contact box, whatever order the form uses.
        .sort((first, second) => (first.kind === 'hero' ? -1 : first.kind === 'contact' ? 1 : 0) - (second.kind === 'hero' ? -1 : second.kind === 'contact' ? 1 : 0));

    const drawSection = (kind, y, height, isActive) => {
        const fill = isActive ? '#e3eeff' : '#f3f6fa';
        const ink = isActive ? '#2f6fe0' : '#d3dbe6';
        const soft = isActive ? '#9cc0fa' : '#e3e9f1';
        const outline = isActive ? `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="none" stroke="#2f6fe0" stroke-width="2"/><rect x="232" y="${y - 7}" width="52" height="14" rx="7" fill="#2f6fe0"/><text x="258" y="${y + 3}" text-anchor="middle" font-size="8" font-weight="700" fill="#fff">EDITING</text>` : '';
        let shapes = '';

        if (kind === 'hero') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${isActive ? '#2f6fe0' : '#cfd8e4'}"/>
                <path d="M10 ${y + height - 14} l60 -22 l40 14 l50 -26 l70 30 l60 -12 v${16} h-280z" fill="${isActive ? '#1d4fae' : '#bcc7d6'}"/>
                <rect x="24" y="${y + 14}" width="40" height="5" rx="2.5" fill="#fff" opacity=".7"/>
                <rect x="24" y="${y + 24}" width="110" height="8" rx="4" fill="#fff"/>
                <rect x="24" y="${y + 36}" width="80" height="8" rx="4" fill="#fff" opacity=".85"/>`;
        } else if (kind === 'intro') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${fill}"/>
                <rect x="22" y="${y + 10}" width="100" height="7" rx="3.5" fill="${ink}"/>
                <rect x="22" y="${y + 22}" width="130" height="4" rx="2" fill="${soft}"/>
                <rect x="22" y="${y + 30}" width="120" height="4" rx="2" fill="${soft}"/>
                <rect x="186" y="${y + 7}" width="92" height="${height - 14}" rx="5" fill="${ink}"/>`;
        } else if (kind === 'cards') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${fill}"/>
                ${[0, 1, 2].map((index) => `<rect x="${22 + index * 88}" y="${y + 8}" width="80" height="${height - 16}" rx="5" fill="#fff" stroke="${soft}"/><rect x="${22 + index * 88}" y="${y + 8}" width="80" height="16" rx="5" fill="${ink}"/><rect x="${29 + index * 88}" y="${y + 29}" width="48" height="4" rx="2" fill="${soft}"/>`).join('')}`;
        } else if (kind === 'gallery') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${fill}"/>
                ${[0, 1, 2, 3].map((index) => `<rect x="${22 + index * 66}" y="${y + 8}" width="58" height="${height - 16}" rx="5" fill="${ink}"/>`).join('')}`;
        } else if (kind === 'list') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${fill}"/>
                ${[0, 1].map((column) => [0, 1, 2].map((row) => `<circle cx="${28 + column * 136}" cy="${y + 11 + row * 9}" r="2.5" fill="${ink}"/><rect x="${35 + column * 136}" y="${y + 9 + row * 9}" width="90" height="4" rx="2" fill="${soft}"/>`).join('')).join('')}`;
        } else if (kind === 'contact') {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${isActive ? '#2f6fe0' : '#cfd8e4'}"/>
                <rect x="24" y="${y + 10}" width="110" height="6" rx="3" fill="#fff"/>
                <rect x="24" y="${y + 20}" width="80" height="4" rx="2" fill="#fff" opacity=".7"/>
                <rect x="216" y="${y + 9}" width="58" height="16" rx="8" fill="#fff"/>`;
        } else {
            shapes = `<rect x="10" y="${y}" width="280" height="${height}" rx="6" fill="${fill}"/>
                <rect x="22" y="${y + 9}" width="90" height="6" rx="3" fill="${ink}"/>
                <rect x="22" y="${y + 19}" width="200" height="4" rx="2" fill="${soft}"/>`;
        }

        return shapes + outline;
    };

    const browserFrame = (height, body) => `<svg class="field-preview-sketch" viewBox="0 0 300 ${height}" role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" font-family="system-ui, sans-serif">
        <rect x="1" y="1" width="298" height="${height - 2}" rx="10" fill="#fff" stroke="#c9d5e5" stroke-width="1.5"/>
        <path d="M1 11 a10 10 0 0 1 10 -10 h278 a10 10 0 0 1 10 10 v11 h-298z" fill="#eef2f8"/>
        <circle cx="13" cy="11" r="3" fill="#f2a1a1"/><circle cx="23" cy="11" r="3" fill="#f5d38a"/><circle cx="33" cy="11" r="3" fill="#9fd9a5"/>
        <rect x="48" y="6" width="200" height="10" rx="5" fill="#fff" stroke="#d8e1ed"/>
        ${body}
    </svg>`;

    const siteNavbar = (y, isActive) => `<rect x="10" y="${y}" width="280" height="18" rx="5" fill="${isActive ? '#e3eeff' : '#f3f6fa'}" ${isActive ? 'stroke="#2f6fe0" stroke-width="2"' : ''}/>
        <rect x="18" y="${y + 5}" width="22" height="8" rx="2" fill="${isActive ? '#2f6fe0' : '#d3dbe6'}"/>
        ${[0, 1, 2, 3].map((index) => `<rect x="${110 + index * 34}" y="${y + 7}" width="26" height="4" rx="2" fill="${isActive ? '#9cc0fa' : '#d3dbe6'}"/>`).join('')}
        <rect x="252" y="${y + 4}" width="30" height="10" rx="5" fill="${isActive ? '#2f6fe0' : '#d3dbe6'}"/>`;

    const pageSketch = (activePanel, wholePage = false) => {
        const sections = sectionPanels();
        let y = 54;
        const body = sections.map((section) => {
            const height = sectionHeights[section.kind];
            const drawing = drawSection(section.kind, y, height, wholePage || section.panel === activePanel);
            y += height + 7;

            return drawing;
        }).join('');

        return browserFrame(y + 4, siteNavbar(30, false) + body);
    };

    const navbarSketch = () => {
        const pageName = escapeHtml(shorten(fieldValue('name') || 'This page', 24));

        return browserFrame(150, `${siteNavbar(30, true)}
            <rect x="120" y="52" width="150" height="86" rx="8" fill="#fff" stroke="#c9d5e5"/>
            <rect x="130" y="62" width="90" height="5" rx="2.5" fill="#d3dbe6"/>
            <rect x="126" y="74" width="138" height="20" rx="6" fill="#e3eeff" stroke="#2f6fe0" stroke-width="1.5"/>
            <text x="134" y="88" font-size="10" font-weight="700" fill="#1d4fae">${pageName}</text>
            <rect x="130" y="104" width="76" height="5" rx="2.5" fill="#d3dbe6"/>
            <rect x="130" y="118" width="100" height="5" rx="2.5" fill="#d3dbe6"/>
            <path d="M178 44 v8" stroke="#2f6fe0" stroke-width="2" stroke-dasharray="2 2"/>`);
    };

    const searchSketch = () => {
        const title = escapeHtml(shorten(fieldValue('meta_title') || 'Page title', 44));
        const description = escapeHtml(shorten(fieldValue('meta_description') || 'Page description shown under the title.', 60));
        const tabTitle = escapeHtml(shorten(fieldValue('meta_title') || 'Page title', 22));

        return `<svg class="field-preview-sketch" viewBox="0 0 300 150" role="img" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" font-family="system-ui, sans-serif">
            <rect x="1" y="1" width="298" height="148" rx="10" fill="#fff" stroke="#c9d5e5" stroke-width="1.5"/>
            <path d="M1 11 a10 10 0 0 1 10 -10 h278 a10 10 0 0 1 10 10 v17 h-298z" fill="#eef2f8"/>
            <path d="M12 28 v-14 a5 5 0 0 1 5 -5 h120 a5 5 0 0 1 5 5 v14z" fill="#fff" stroke="#2f6fe0" stroke-width="1.5"/>
            <circle cx="22" cy="19" r="4" fill="#2f6fe0"/>
            <text x="31" y="22" font-size="9" font-weight="700" fill="#1f3550">${tabTitle}</text>
            <rect x="14" y="40" width="200" height="16" rx="8" fill="#f3f6fa" stroke="#d8e1ed"/>
            <circle cx="26" cy="48" r="3.5" fill="none" stroke="#9aa9bd" stroke-width="1.5"/>
            <rect x="14" y="66" width="272" height="72" rx="8" fill="#f8fbff" stroke="#2f6fe0" stroke-width="1.5"/>
            <rect x="24" y="76" width="90" height="4" rx="2" fill="#9cc0a8"/>
            <text x="24" y="98" font-size="11" font-weight="700" fill="#1a4fd0">${title}</text>
            <text x="24" y="114" font-size="9" fill="#5d6f8a">${description}</text>
            <rect x="24" y="122" width="150" height="4" rx="2" fill="#e3e9f1"/>
        </svg>`;
    };

    const preview = document.createElement('aside');
    const hintsToggle = document.createElement('button');
    const hintsStorageKey = 'admin-field-hints';
    let hintsEnabled = true;

    try {
        hintsEnabled = window.localStorage.getItem(hintsStorageKey) !== 'off';
    } catch {
        // Storage can be blocked; hints then stay on for this visit.
    }

    preview.className = 'field-preview';
    preview.setAttribute('role', 'tooltip');
    preview.hidden = true;
    document.body.append(preview);

    // One switch on every editor to hide or show the "Shown in" pop-up; the choice is remembered.
    const syncHintsToggle = () => {
        hintsToggle.setAttribute('aria-pressed', String(hintsEnabled));
        hintsToggle.innerHTML = `<span aria-hidden="true">💡</span> Hints ${hintsEnabled ? 'on' : 'off'}`;
        hintsToggle.title = hintsEnabled ? 'Hide the "Shown in" pop-up' : 'Show the "Shown in" pop-up';
    };

    hintsToggle.type = 'button';
    hintsToggle.className = 'field-preview-toggle';
    hintsToggle.addEventListener('click', () => {
        hintsEnabled = !hintsEnabled;
        preview.hidden = true;
        syncHintsToggle();

        try {
            window.localStorage.setItem(hintsStorageKey, hintsEnabled ? 'on' : 'off');
        } catch {
            // The choice then lasts only for this visit.
        }
    });
    syncHintsToggle();
    document.body.append(hintsToggle);

    const renderPreview = (field) => {
        let title;
        let description;
        let sketch;

        if (isNavField(field)) {
            title = 'Website navbar & admin sidebar';
            description = 'The page name appears in the top menu and in this sidebar.';
            sketch = navbarSketch();
        } else if (isMetaField(field)) {
            title = 'Browser tab & search results';
            description = 'The browser title and description shown in the tab and on Google.';
            sketch = searchSketch();
        } else if (field.name === 'theme') {
            title = 'Whole page';
            description = 'The theme colour is used across the banner, labels, buttons and contact box.';
            sketch = pageSketch(null, true);
        } else {
            const section = panelInfo(field.closest('.homepage-form-panel'));

            title = section.title;
            description = shorten(section.description, 150);
            sketch = pageSketch(section.panel);
        }

        preview.innerHTML = `<strong class="field-preview-title">Shown in: ${escapeHtml(title)}</strong>${sketch}${description ? `<p>${escapeHtml(description)}</p>` : ''}`;
    };

    const showPreview = (event) => {
        const field = event.target instanceof Element ? event.target.closest('input, textarea, select') : null;

        if (!hintsEnabled || !field || !field.name || field.type === 'hidden' || !field.closest('.homepage-form-panel')) {
            preview.hidden = true;

            return;
        }

        renderPreview(field);
        preview.hidden = false;
    };

    previewForm.addEventListener('focusin', showPreview);
    previewForm.addEventListener('mouseover', showPreview);
    previewForm.addEventListener('input', (event) => {
        if (!preview.hidden && event.target instanceof Element && (isNavField(event.target) || isMetaField(event.target))) {
            renderPreview(event.target);
        }
    });
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
            const entry = moveTrigger.closest('.admin-subnav-entry');
            const pageTrigger = entry?.querySelector('[data-context-menu][data-move-url]')
                || entry?.querySelector('[data-context-menu]');

            if (pageTrigger) {
                openMenu(pageTrigger, event, Boolean(pageTrigger.dataset.moveUrl));
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

// Each repeatable list uses the template in its own form panel, so a form can hold several lists.
document.querySelectorAll('[data-gallery-editor]').forEach((galleryEditor) => {
    const slidesContainer = galleryEditor.querySelector('[data-gallery-slides]');
    const addSlideButton = galleryEditor.querySelector('[data-add-slide]');
    const slideCount = galleryEditor.querySelector('[data-gallery-count]');
    const slideTemplate = galleryEditor.closest('.homepage-form-panel')?.querySelector('template[data-gallery-template]')
        ?? document.querySelector('template[data-gallery-template]');
    const maximumSlides = Number(galleryEditor.dataset.maxSlides);
    const fieldPrefix = galleryEditor.dataset.fieldPrefix ?? 'gallery';
    const itemLabel = galleryEditor.dataset.itemLabel ?? 'Slide';
    const itemLabelPlural = galleryEditor.dataset.itemLabelPlural ?? 'slides';
    const tabLabelField = galleryEditor.dataset.galleryPaged;
    const isPaged = tabLabelField !== undefined;
    const pageNav = document.createElement('div');
    let activePage = 0;

    const renderPages = (slides) => {
        activePage = Math.min(Math.max(activePage, 0), slides.length - 1);

        slides.forEach((slide, index) => {
            slide.hidden = index !== activePage;
        });

        const tabs = slides.map((slide, index) => {
            const label = slide.querySelector(`[data-gallery-field="${tabLabelField}"]`)?.value.trim() || `${itemLabel} ${index + 1}`;
            const button = document.createElement('button');

            button.type = 'button';
            button.className = `repeater-slider-tab${index === activePage ? ' is-active' : ''}`;
            button.dataset.galleryPage = String(index);
            button.setAttribute('aria-current', String(index === activePage));
            button.textContent = label;

            return button;
        });

        pageNav.querySelector('.repeater-slider-tabs').replaceChildren(...tabs);
        pageNav.querySelector('[data-gallery-page-step="-1"]').disabled = activePage === 0;
        pageNav.querySelector('[data-gallery-page-step="1"]').disabled = activePage === slides.length - 1;
    };

    if (isPaged) {
        pageNav.className = 'repeater-slider-nav gallery-page-nav';
        pageNav.innerHTML = `<button type="button" class="repeater-slider-arrow" data-gallery-page-step="-1" aria-label="Previous ${itemLabel.toLowerCase()}">‹</button>
            <div class="repeater-slider-tabs"></div>
            <button type="button" class="repeater-slider-arrow" data-gallery-page-step="1" aria-label="Next ${itemLabel.toLowerCase()}">›</button>`;
        slidesContainer.before(pageNav);

        pageNav.addEventListener('click', (event) => {
            const control = event.target.closest('[data-gallery-page], [data-gallery-page-step]');

            if (!control) {
                return;
            }

            activePage = control.dataset.galleryPage !== undefined
                ? Number(control.dataset.galleryPage)
                : activePage + Number(control.dataset.galleryPageStep);
            renderPages([...slidesContainer.querySelectorAll('[data-gallery-slide]')]);
        });

        slidesContainer.addEventListener('input', (event) => {
            if (event.target.matches(`[data-gallery-field="${tabLabelField}"]`)) {
                renderPages([...slidesContainer.querySelectorAll('[data-gallery-slide]')]);
            }
        });

        galleryEditor.closest('form')?.addEventListener('invalid', (event) => {
            const slides = [...slidesContainer.querySelectorAll('[data-gallery-slide]')];
            const invalidIndex = slides.findIndex((slide) => slide.contains(event.target));

            if (invalidIndex !== -1 && invalidIndex !== activePage) {
                activePage = invalidIndex;
                renderPages(slides);
            }
        }, true);
    }

    const updateSlides = () => {
        const slides = [...slidesContainer.querySelectorAll('[data-gallery-slide]')];

        if (isPaged) {
            renderPages(slides);
        }

        slides.forEach((slide, index) => {
            slide.querySelector('[data-slide-legend]').textContent = `${itemLabel} ${index + 1}`;
            slide.querySelector('[data-remove-slide]').setAttribute('aria-label', `Remove ${itemLabel.toLowerCase()} ${index + 1}`);

            slide.querySelectorAll('[data-gallery-field]').forEach((field) => {
                field.name = `${fieldPrefix}[${index}][${field.dataset.galleryField}]`;
                field.required = index === 0 && !('galleryOptional' in field.dataset);
            });
        });

        slideCount.textContent = `${slides.length} of ${maximumSlides} ${itemLabelPlural}`;
        addSlideButton.disabled = slides.length >= maximumSlides;
    };

    addSlideButton.addEventListener('click', () => {
        if (!slideTemplate || slidesContainer.querySelectorAll('[data-gallery-slide]').length >= maximumSlides) {
            return;
        }

        slidesContainer.append(slideTemplate.content.cloneNode(true));
        activePage = slidesContainer.querySelectorAll('[data-gallery-slide]').length - 1;
        updateSlides();
        slidesContainer.querySelector('[data-gallery-slide]:last-child [data-gallery-field]').focus();
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
        const removedIndex = [...slidesContainer.querySelectorAll('[data-gallery-slide]')].indexOf(slide);
        const wasLastSlide = slide.nextElementSibling === null;
        slide.remove();

        if (removedIndex < activePage || (removedIndex === activePage && wasLastSlide)) {
            activePage -= 1;
        }

        updateSlides();

        if (nextSlide) {
            nextSlide.querySelector('[data-gallery-field]').focus();
        } else {
            addSlideButton.focus();
        }
    });

    updateSlides();
});

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

// Filters such as the revenue period apply as soon as a new option is picked.
document.querySelectorAll('[data-auto-submit]').forEach((field) => {
    field.addEventListener('change', () => field.form?.requestSubmit());
});

// Row action menus in admin tables close when another opens or on a click outside.
document.addEventListener('click', (event) => {
    document.querySelectorAll('.admin-row-menu[open]').forEach((menu) => {
        if (!menu.contains(event.target)) {
            menu.removeAttribute('open');
        }
    });
});

// Destructive admin actions ask for confirmation first.
document.addEventListener('submit', (event) => {
    const message = event.target.dataset?.confirm;

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});

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

    opener.addEventListener('click', () => {
        const slot = modal.querySelector('[data-about-video]');
        const { aboutVideoType: type, aboutVideoSrc: src, aboutVideoTitle: title } = opener.dataset;

        // Each card brings its own video; the popup itself is shared.
        if (slot) {
            slot.replaceChildren();
            slot.hidden = !src;

            if (src && type === 'file') {
                const video = document.createElement('video');
                Object.assign(video, { src, controls: true, playsInline: true, preload: 'metadata' });
                video.setAttribute('aria-label', `Video: ${title ?? ''}`);
                slot.append(video);
            } else if (src) {
                const frame = document.createElement('iframe');
                Object.assign(frame, { src, title: `Video: ${title ?? ''}`, allowFullscreen: true });
                frame.setAttribute('allow', 'encrypted-media; picture-in-picture; fullscreen');
                frame.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
                slot.append(frame);
            }
        }

        modal.showModal();
    });
});

document.querySelectorAll('.china-about-modal').forEach((modal) => {
    const main = modal.querySelector('[data-about-main]');
    const videoSlot = modal.querySelector('[data-about-video]');

    // Removing the player stops playback when the popup closes.
    modal.addEventListener('close', () => {
        videoSlot?.replaceChildren();
    });

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

if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('[data-background-video], [data-sh-journey-video]').forEach((video) => {
        video.removeAttribute('autoplay');
        video.pause();
    });
}

document.querySelectorAll('[data-video-field]').forEach((videoField) => {
    const urlInput = videoField.querySelector('[data-video-url]');
    const fileInput = videoField.querySelector('[data-video-file]');
    const preview = videoField.querySelector('[data-video-preview]');
    const emptyState = videoField.querySelector('[data-video-empty]');
    const clearButton = videoField.querySelector('[data-video-clear]');
    let objectUrl = null;

    const showPreview = (source) => {
        preview.hidden = !source;
        emptyState.hidden = Boolean(source);
        clearButton.hidden = !source;

        if (!source) {
            preview.pause();
            preview.removeAttribute('src');
            preview.load();

            return;
        }

        if (preview.getAttribute('src') !== source) {
            preview.src = source;
            preview.play().catch(() => {});
        }
    };

    const sourceForUrl = (value) => {
        if (value === videoField.dataset.savedVideo) {
            return videoField.dataset.savedVideoSrc;
        }

        return /^https?:\/\//i.test(value) ? value : '';
    };

    const releaseObjectUrl = () => {
        if (objectUrl) {
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }
    };

    urlInput.addEventListener('input', () => {
        if (!fileInput.files.length) {
            showPreview(sourceForUrl(urlInput.value.trim()));
        }
    });

    fileInput.addEventListener('change', () => {
        releaseObjectUrl();

        if (fileInput.files.length) {
            objectUrl = URL.createObjectURL(fileInput.files[0]);
            showPreview(objectUrl);
        } else {
            showPreview(sourceForUrl(urlInput.value.trim()));
        }
    });

    clearButton.addEventListener('click', () => {
        releaseObjectUrl();
        urlInput.value = '';
        fileInput.value = '';
        showPreview('');
        urlInput.focus();
    });
});

/**
 * Arrows and page dots for a horizontally scrolling card row. Returns a function that refreshes them.
 */
const setUpCardSlider = (slider, dotLabel) => {
    const track = slider.querySelector('[data-slider-track]');
    const previousButton = slider.querySelector('[data-slider-prev]');
    const nextButton = slider.querySelector('[data-slider-next]');
    const dots = slider.parentElement.querySelector('[data-slider-dots]');

    // A partly filled last page still counts, e.g. 5 cards at 4 per view is 2 pages.
    const pageCount = () => 1 + Math.max(0, Math.ceil((track.scrollWidth - track.clientWidth - 2) / track.clientWidth));
    const currentPage = () => Math.round(track.scrollLeft / track.clientWidth);

    const updateSliderControls = () => {
        const isScrollable = track.scrollWidth > track.clientWidth + 2;
        const atStart = track.scrollLeft <= 2;
        const atEnd = track.scrollLeft + track.clientWidth >= track.scrollWidth - 2;

        previousButton.hidden = !isScrollable;
        nextButton.hidden = !isScrollable;
        previousButton.disabled = atStart;
        nextButton.disabled = atEnd;
        dots.hidden = !isScrollable;

        if (!isScrollable) {
            return;
        }

        const pages = pageCount();
        const activePage = atEnd ? pages - 1 : currentPage();

        if (dots.children.length !== pages) {
            dots.replaceChildren(...Array.from({ length: pages }, (_, page) => {
                const dot = document.createElement('button');
                dot.type = 'button';
                dot.setAttribute('aria-label', `${dotLabel} ${page + 1}`);
                dot.addEventListener('click', () => track.scrollTo({ left: page * track.clientWidth, behavior: 'smooth' }));

                return dot;
            }));
        }

        [...dots.children].forEach((dot, page) => {
            if (page === activePage) {
                dot.setAttribute('aria-current', 'true');
            } else {
                dot.removeAttribute('aria-current');
            }
        });
    };

    previousButton.addEventListener('click', () => track.scrollBy({ left: -track.clientWidth, behavior: 'smooth' }));
    nextButton.addEventListener('click', () => track.scrollBy({ left: track.clientWidth, behavior: 'smooth' }));
    track.addEventListener('scroll', updateSliderControls, { passive: true });
    window.addEventListener('resize', updateSliderControls);
    updateSliderControls();

    // Cards starting beyond the visible row would otherwise play their scroll-in animation only when slid to.
    const visibleRight = track.getBoundingClientRect().right - Number.parseFloat(getComputedStyle(track).paddingRight);
    [...track.children].filter((card) => card.getBoundingClientRect().right > visibleRight + 1).forEach((card) => card.classList.add('is-revealed'));

    return updateSliderControls;
};

document.querySelectorAll('[data-tour-finder]').forEach((finder) => {
    const section = finder.closest('section');
    const track = section.querySelector('[data-slider-track]');
    const cards = [...track.querySelectorAll('[data-tour-card]')];
    const searchInput = finder.querySelector('[data-tour-search]');
    const filterButtons = [...finder.querySelectorAll('[data-tour-filter]')];
    const countLabel = finder.querySelector('[data-tour-count]');
    const emptyMessage = section.querySelector('[data-tour-empty]');
    const [singular, plural] = [finder.dataset.itemSingular ?? 'destination', finder.dataset.itemPlural ?? 'destinations'];
    const updateSliderControls = setUpCardSlider(section.querySelector('[data-card-slider]'), `Show ${plural} page`);
    let activeCategory = '';

    const applyFilters = () => {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        cards.forEach((card) => {
            // A card may carry several tags separated by "|".
            const matchesCategory = activeCategory === '' || card.dataset.category.split('|').includes(activeCategory);
            const matchesQuery = query === '' || card.dataset.search.includes(query);
            card.hidden = !(matchesCategory && matchesQuery);
            visibleCount += card.hidden ? 0 : 1;
        });

        countLabel.textContent = `${visibleCount} ${visibleCount === 1 ? singular : plural}`;
        emptyMessage.hidden = visibleCount > 0;
        track.scrollTo({ left: 0 });
        updateSliderControls();
    };

    searchInput.addEventListener('input', applyFilters);

    filterButtons.forEach((button) => button.addEventListener('click', () => {
        activeCategory = button.dataset.tourFilter;
        filterButtons.forEach((other) => other.setAttribute('aria-pressed', String(other === button)));
        applyFilters();
    }));

    section.querySelector('[data-tour-reset]')?.addEventListener('click', () => {
        searchInput.value = '';
        filterButtons[0]?.click();
        applyFilters();
        searchInput.focus();
    });
});

document.querySelectorAll('[data-state-finder]').forEach((finder) => {
    const section = finder.closest('section');
    const searchInput = finder.querySelector('[data-state-search]');
    const regionSelect = finder.querySelector('[data-state-region]');
    const countLabel = finder.querySelector('[data-state-count]');
    const emptyMessage = section.querySelector('[data-state-empty]');
    const states = [...section.querySelectorAll('[data-state-card]')];
    const regionSections = [...section.querySelectorAll('[data-region-section]')];
    const slides = [...section.querySelectorAll('[data-region-slide]')];
    const slider = section.querySelector('[data-card-slider]');

    if (slider) {
        setUpCardSlider(slider, 'Show regions page');
    }

    const applyFilters = () => {
        const query = searchInput.value.trim().toLowerCase();
        const region = regionSelect?.value ?? '';
        let visibleCount = 0;

        states.forEach((state) => {
            const matchesRegion = region === '' || state.dataset.region === region;
            const matchesQuery = query === '' || state.dataset.search.includes(query);
            state.hidden = !(matchesRegion && matchesQuery);
            visibleCount += state.hidden ? 0 : 1;
        });

        regionSections.forEach((regionSection) => {
            regionSection.hidden = !regionSection.querySelector('[data-state-card]:not([hidden])');
        });

        slides.forEach((slide) => {
            if (slide.dataset.regionSlide === region) {
                slide.setAttribute('aria-current', 'true');
            } else {
                slide.removeAttribute('aria-current');
            }
        });

        countLabel.textContent = `${visibleCount} ${visibleCount === 1 ? (finder.dataset.itemSingular ?? 'state') : (finder.dataset.itemPlural ?? 'states')}`;
        emptyMessage.hidden = visibleCount > 0;
    };

    searchInput.addEventListener('input', applyFilters);
    regionSelect?.addEventListener('change', applyFilters);

    // A region slide filters the states to that region; choosing it again shows every region.
    slides.forEach((slide) => slide.addEventListener('click', (event) => {
        if (!regionSelect) {
            return;
        }

        event.preventDefault();
        const isActive = regionSelect.value === slide.dataset.regionSlide;
        regionSelect.value = isActive ? '' : slide.dataset.regionSlide;
        applyFilters();

        if (!isActive) {
            document.getElementById(`region-${slide.dataset.regionSlide}`)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }));

    section.querySelector('[data-state-reset]')?.addEventListener('click', () => {
        searchInput.value = '';

        if (regionSelect) {
            regionSelect.value = '';
        }

        applyFilters();
        searchInput.focus();
    });
});

document.querySelectorAll('form[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

/**
 * Clearable image fields: the preview follows the URL or chosen file, and Clear returns the default look.
 * Delegated so fields in slides added later work too.
 */
const imageFieldPreviewUrls = new WeakMap();

const showImageFieldPreview = (field, source) => {
    const preview = field.querySelector('[data-image-preview]');

    preview.hidden = !source;
    field.querySelector('[data-image-default]').hidden = Boolean(source);
    field.querySelector('[data-image-clear]').hidden = !source;

    if (source) {
        preview.src = source;
    } else {
        preview.removeAttribute('src');
    }
};

const releaseImageFieldPreview = (field) => {
    if (imageFieldPreviewUrls.has(field)) {
        URL.revokeObjectURL(imageFieldPreviewUrls.get(field));
        imageFieldPreviewUrls.delete(field);
    }
};

document.addEventListener('input', (event) => {
    const field = event.target.closest?.('[data-image-field]');

    if (!field || !event.target.matches('[data-image-url]') || field.querySelector('[data-image-file]').files.length) {
        return;
    }

    const value = event.target.value.trim();
    const source = value === field.dataset.savedImage ? field.dataset.savedImageSrc : (/^https?:\/\//i.test(value) ? value : '');

    showImageFieldPreview(field, source);

    if (value !== '' && !source) {
        field.querySelector('[data-image-clear]').hidden = false;
    }
});

document.addEventListener('change', (event) => {
    const field = event.target.closest?.('[data-image-field]');

    if (!field || !event.target.matches('[data-image-file]')) {
        return;
    }

    releaseImageFieldPreview(field);
    const file = event.target.files[0];

    if (file) {
        imageFieldPreviewUrls.set(field, URL.createObjectURL(file));
        showImageFieldPreview(field, imageFieldPreviewUrls.get(field));
    }
});

document.addEventListener('click', (event) => {
    const clearButton = event.target.closest('[data-image-clear]');
    const field = clearButton?.closest('[data-image-field]');

    if (!field) {
        return;
    }

    const urlInput = field.querySelector('[data-image-url]');

    urlInput.value = '';
    field.querySelector('[data-image-file]').value = '';
    releaseImageFieldPreview(field);
    showImageFieldPreview(field, '');
    urlInput.focus();
});

document.querySelectorAll('[data-print-page]').forEach((button) => {
    button.addEventListener('click', () => window.print());
});

// Booking conditions: highlight the contents link of the condition being read.
document.querySelectorAll('[data-booking-conditions]').forEach((section) => {
    const links = [...section.querySelectorAll('[data-booking-toc-link]')];
    const cards = links.map((link) => document.getElementById(link.hash.slice(1))).filter(Boolean);

    if (!cards.length || !('IntersectionObserver' in window)) {
        return;
    }

    const visible = new Set();
    const setCurrent = () => {
        const current = cards.find((card) => visible.has(card)) ?? null;

        links.forEach((link) => {
            if (current && link.hash === `#${current.id}`) {
                link.setAttribute('aria-current', 'true');
                // On phones the contents are a sideways chip row; keep the current chip in view without moving the page.
                const list = link.closest('ol');

                if (list.scrollWidth > list.clientWidth) {
                    list.scrollTo({ left: link.offsetLeft - 18, behavior: 'smooth' });
                }
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => (entry.isIntersecting ? visible.add(entry.target) : visible.delete(entry.target)));
        setCurrent();
    }, { rootMargin: '-110px 0px -55% 0px' });

    cards.forEach((card) => observer.observe(card));
});

// Ticketing: the enquiry form becomes a WhatsApp message to the ticketing team.
document.querySelectorAll('[data-ticket-enquiry]').forEach((form) => {
    const returnField = form.querySelector('[data-return-field]');
    const returnInput = returnField?.querySelector('input');

    const syncTripType = () => {
        const isOneWay = form.querySelector('[data-trip-type]:checked')?.value === 'One way';

        returnField?.toggleAttribute('hidden', isOneWay);

        if (returnInput) {
            returnInput.required = !isOneWay;
            returnInput.disabled = isOneWay;
        }
    };

    form.addEventListener('change', (event) => {
        if (event.target.matches('[data-trip-type]')) {
            syncTripType();
        }

        if (event.target.name === 'depart' && returnInput) {
            returnInput.min = event.target.value;
        }
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();

        const data = new FormData(form);
        const value = (name) => String(data.get(name) ?? '').trim();
        const lines = [
            "Hi, I'd like to enquire about a flight ticket.",
            `Name: ${value('name')}`,
            `Phone: ${value('phone')}`,
            `Trip: ${value('trip')}`,
            `From: ${value('from')}`,
            `To: ${value('to')}`,
            `Departure: ${value('depart')}`,
            ...(value('trip') === 'One way' ? [] : [`Return: ${value('return')}`]),
            `Passengers: ${value('passengers')}`,
            `Class: ${value('class')}`,
            ...(value('airline') ? [`Preferred airline: ${value('airline')}`] : []),
            ...(value('notes') ? [`Notes: ${value('notes')}`] : []),
        ];

        // Also log the enquiry in the admin inbox. WhatsApp opens in the same click so the
        // browser does not block it, and a failed log never stops the visitor.
        if (form.dataset.enquiryUrl) {
            fetch(form.dataset.enquiryUrl, {
                method: 'POST',
                keepalive: true,
                headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': value('_token') },
                body: JSON.stringify({
                    name: value('name'),
                    phone: value('phone'),
                    destination: `Flight ticket: ${value('from')} → ${value('to')}`,
                    message: lines.slice(1).join('\n'),
                }),
            }).catch(() => {});
        }

        window.open(`https://wa.me/${form.dataset.whatsapp}?text=${encodeURIComponent(lines.join('\n'))}`, '_blank', 'noopener');
    });

    syncTripType();
});

document.querySelectorAll('[data-privacy-toc]').forEach((toc) => {
    const links = [...toc.querySelectorAll('a[href^="#"]')];
    const sections = links.map((link) => document.querySelector(link.getAttribute('href'))).filter(Boolean);

    const setActive = (id) => {
        links.forEach((link) => {
            const isActive = link.getAttribute('href') === `#${id}`;

            link.classList.toggle('is-active', isActive);

            if (isActive) {
                link.setAttribute('aria-current', 'true');
                const list = link.closest('ol');

                if (list.scrollWidth > list.clientWidth) {
                    list.scrollTo({ left: link.offsetLeft - list.offsetLeft - 16, behavior: 'smooth' });
                }
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    // The current section is the last one whose top has scrolled just past its jump-link landing spot.
    let frame = null;
    const update = () => {
        frame = null;

        const atBottom = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 4;
        const passed = sections.filter((section) => {
            const landing = parseFloat(getComputedStyle(section).scrollMarginTop) || 0;

            return section.getBoundingClientRect().top <= landing + 24;
        });
        const current = atBottom ? sections[sections.length - 1] : passed[passed.length - 1] ?? sections[0];

        setActive(current.id);
    };

    window.addEventListener('scroll', () => {
        frame ??= requestAnimationFrame(update);
    }, { passive: true });
    update();
});

// Package booking form: a fixed departure sets the travel date, and the estimate follows the traveller count.
document.querySelectorAll('[data-package-booking]').forEach((form) => {
    const option = form.querySelector('[data-booking-option]');
    const date = form.querySelector('[data-booking-date]');
    const dateHint = form.querySelector('[data-booking-date-hint]');
    const adults = form.querySelector('[data-booking-adults]');
    const children = form.querySelector('[data-booking-children]');
    const estimate = document.querySelector('[data-booking-estimate]');
    const estimateNote = document.querySelector('[data-booking-estimate-note]');
    const money = (value) => `RM ${Math.round(value).toLocaleString('en-MY')}`;

    const updateDate = () => {
        const fixedDate = option.selectedOptions[0]?.dataset.date ?? '';

        if (fixedDate) {
            date.value = fixedDate;
        } else if (date.readOnly) {
            date.value = '';
        }

        date.readOnly = fixedDate !== '';
        date.required = fixedDate === '';
        dateHint.textContent = fixedDate ? 'Set by the departure you chose' : 'Choose the day you would like to leave';
    };

    const updateEstimate = () => {
        const price = Number(option.selectedOptions[0]?.dataset.price || 0);
        const travellers = Math.max(1, (Number(adults.value) || 0) + (Number(children.value) || 0));

        if (!estimate) {
            return;
        }

        estimate.textContent = price ? money(price * travellers) : estimate.dataset.empty;
        estimateNote.textContent = price
            ? `${money(price)} per person × ${travellers} ${travellers === 1 ? 'traveller' : 'travellers'}`
            : 'Our team will quote the price for your dates.';
    };

    option.addEventListener('change', () => {
        updateDate();
        updateEstimate();
    });
    adults.addEventListener('input', updateEstimate);
    children.addEventListener('input', updateEstimate);
});

// Admin online booking: label each switch and open or close a whole section at once.
document.querySelectorAll('[data-online-booking]').forEach((form) => {
    const label = (input) => {
        const state = input.closest('label').querySelector('.admin-booking-state');

        state.textContent = input.checked ? state.dataset.openLabel : state.dataset.closedLabel;
    };

    form.addEventListener('change', (event) => {
        if (event.target.matches('.admin-booking-toggle input')) {
            label(event.target);
        }
    });

    form.querySelectorAll('[data-booking-bulk]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('section').querySelectorAll('.admin-booking-toggle input').forEach((input) => {
                input.checked = button.dataset.bookingBulk === 'open';
                label(input);
            });
        });
    });
});

// Admin: a package's online booking switch saves as soon as it is flipped.
document.querySelectorAll('[data-booking-switch]').forEach((form) => {
    form.querySelector('input[type="checkbox"]').addEventListener('change', () => {
        form.querySelector('.admin-booking-state').textContent = 'Saving…';
        form.requestSubmit();
    });
});

// Client guided tour: starts by itself on the homepage after a client's first sign-in, then walks through My Account.
document.querySelectorAll('[data-portal-tour]').forEach((root) => {
    const pages = JSON.parse(root.dataset.tourPages);
    const currentPage = root.dataset.tourPage;
    const name = root.dataset.tourName;
    const storageKey = 'cp-tour-step';
    const steps = [
        { page: 'home', title: `Welcome to Smart Holidays, ${name}! 👋`, text: 'Let us show you around in about a minute: where to find our tours, your profile and account, and how to book a trip.', next: 'Start the tour' },
        { page: 'home', target: ['nav', 'nav-toggle'], title: 'Explore our tours', text: 'Browse Group Tours, Ground Destinations, Domestic tours and Smart Muslim packages from this menu. Every package page has a “Book now” button.' },
        { page: 'home', target: 'whatsapp', title: 'Talk to our team', text: 'Have a question before you book? Message us on WhatsApp and our team will help.' },
        { page: 'home', target: 'account', title: 'Your profile', text: 'Tap this icon any time to see your profile, your latest bookings and “Open my account”. Press Next and we will open My Account for you.' },
        { page: 'dashboard', target: 'menu', title: 'This is My Account', text: 'Your own space for trips. Use this menu to move between your overview, bookings, trip requests and profile.' },
        { page: 'profile', target: 'profile-details', title: 'First, check your details', text: 'Make sure your name and phone number are correct. Our team uses them to confirm your bookings with you.' },
        { page: 'profile', target: 'profile-password', title: 'Keep your account safe', text: 'You can change your password here at any time. You will need your current password to do it.' },
        { page: 'bookings', target: 'bookings-tabs', title: 'Your bookings', text: 'Switch between upcoming trips, past trips and cancelled bookings.' },
        { page: 'bookings', target: 'bookings-list', title: 'Follow every trip', text: 'Open a booking to see its progress (Requested → Confirmed → Completed), dates, travellers and price.' },
        { page: 'requests', target: 'request-form', title: 'Plan a custom trip', text: 'Not sure which package suits you? Tell us where and when you would like to go, and our team will plan it for you.' },
        { page: 'requests', target: 'request-history', title: 'Track your requests', text: 'Every request you send appears here with its status: Received, Being planned or Booked.' },
        { page: 'dashboard', target: 'actions', title: 'Book a package online', text: 'Browse our Smart Muslim packages and press “Book now” on any departure or itinerary. Your request then appears in My bookings.' },
        { page: 'dashboard', title: 'You are all set! 🎉', text: 'You can replay this tour whenever you like with “Take the tour” in My Account. Have a wonderful trip!', next: 'Finish' },
    ];
    const storage = {
        get: () => { try { return sessionStorage.getItem(storageKey); } catch { return null; } },
        set: (value) => { try { sessionStorage.setItem(storageKey, String(value)); } catch { /* the tour simply restarts */ } },
        clear: () => { try { sessionStorage.removeItem(storageKey); } catch { /* nothing to clear */ } },
    };

    let index = -1;
    let target = null;
    let frame = null;
    let ui = null;

    const build = () => {
        const backdrop = document.createElement('div');
        backdrop.className = 'cp-tour-backdrop';
        const spotlight = document.createElement('div');
        spotlight.className = 'cp-tour-spotlight';
        const card = document.createElement('section');
        card.className = 'cp-tour-card';
        card.setAttribute('role', 'dialog');
        card.setAttribute('aria-modal', 'true');
        card.setAttribute('aria-labelledby', 'cp-tour-title');
        card.innerHTML = `
            <div class="cp-tour-progress"><span></span></div>
            <p class="cp-tour-count"></p>
            <h2 id="cp-tour-title"></h2>
            <p class="cp-tour-text"></p>
            <div class="cp-tour-actions">
                <button type="button" class="cp-tour-skip" data-tour-skip>Skip tour</button>
                <span>
                    <button type="button" class="cp-tour-back" data-tour-back>Back</button>
                    <button type="button" class="cp-tour-next" data-tour-next></button>
                </span>
            </div>`;
        document.body.append(backdrop, spotlight, card);
        card.querySelector('[data-tour-skip]').addEventListener('click', () => finish());
        card.querySelector('[data-tour-back]').addEventListener('click', () => go(index - 1));
        card.querySelector('[data-tour-next]').addEventListener('click', () => go(index + 1));

        return { backdrop, spotlight, card };
    };

    const isVisible = (element) => element !== null && element.getClientRects().length > 0;
    const headerBottom = () => Math.max(0, document.querySelector('.site-header')?.getBoundingClientRect().bottom ?? 0);

    // Phones show the card as a bottom sheet, so the target goes just under the header; larger screens centre it.
    const bringIntoView = (element) => {
        const rect = element.getBoundingClientRect();
        const top = innerWidth < 600
            ? headerBottom() + 16
            : Math.max(headerBottom() + 16, (innerHeight - rect.height) / 2);

        window.scrollTo({ top: window.scrollY + rect.top - top, behavior: 'smooth' });
    };

    const place = () => {
        frame = null;

        if (!ui) {
            return;
        }

        const { spotlight, card, backdrop } = ui;
        const centered = !isVisible(target);

        document.body.classList.toggle('cp-tour-centered', centered);
        backdrop.hidden = !centered;
        spotlight.hidden = centered;

        if (centered) {
            card.style.top = '';
            card.style.left = '';

            return;
        }

        const rect = target.getBoundingClientRect();
        const pad = 8;
        Object.assign(spotlight.style, {
            top: `${rect.top - pad}px`,
            left: `${rect.left - pad}px`,
            width: `${rect.width + pad * 2}px`,
            height: `${rect.height + pad * 2}px`,
        });

        if (innerWidth < 600) {
            card.style.top = '';
            card.style.left = '';

            return;
        }

        const gap = 16;
        const minTop = headerBottom() + 12;
        const cardBox = card.getBoundingClientRect();
        const below = rect.bottom + pad + gap;
        const above = rect.top - pad - gap - cardBox.height;
        let top = below + cardBox.height <= innerHeight - 12 ? below : above >= minTop ? above : innerHeight - cardBox.height - 12;
        let left = rect.left;

        // A target with no room above or below (such as the sidebar menu) gets the card beside it.
        if (top !== below && top !== above) {
            if (rect.right + gap + cardBox.width < innerWidth - 12) {
                left = rect.right + pad + gap;
            } else if (rect.left - gap - cardBox.width > 12) {
                left = rect.left - pad - gap - cardBox.width;
            }

            top = Math.min(Math.max(minTop, rect.top), innerHeight - cardBox.height - 12);
        }

        card.style.top = `${Math.max(minTop, top)}px`;
        card.style.left = `${Math.min(Math.max(12, left), innerWidth - cardBox.width - 12)}px`;
    };

    const schedulePlace = () => {
        frame ??= requestAnimationFrame(place);
    };

    const show = (stepIndex) => {
        index = stepIndex;
        storage.set(index);
        ui ??= build();
        document.body.classList.add('cp-tour-running');

        const step = steps[index];
        const { card } = ui;
        target = [step.target ?? []].flat().map((key) => document.querySelector(`[data-tour="${key}"]`)).find(isVisible) ?? null;

        card.querySelector('.cp-tour-count').textContent = `Step ${index + 1} of ${steps.length}`;
        card.querySelector('.cp-tour-progress span').style.width = `${((index + 1) / steps.length) * 100}%`;
        card.querySelector('#cp-tour-title').textContent = step.title;
        card.querySelector('.cp-tour-text').textContent = step.text;
        card.querySelector('[data-tour-back]').hidden = index === 0;
        card.querySelector('[data-tour-skip]').hidden = index === steps.length - 1;
        card.querySelector('[data-tour-next]').textContent = step.next ?? 'Next →';

        if (isVisible(target)) {
            bringIntoView(target);
        }

        place();
        setTimeout(place, 450);
        setTimeout(place, 950);
        card.querySelector('[data-tour-next]').focus({ preventScroll: true });
    };

    const finish = () => {
        storage.clear();
        document.body.classList.remove('cp-tour-running', 'cp-tour-centered');
        ui?.backdrop.remove();
        ui?.spotlight.remove();
        ui?.card.remove();
        ui = null;
        target = null;

        fetch(root.dataset.tourCompleteUrl, {
            method: 'POST',
            keepalive: true,
            headers: {
                'X-CSRF-TOKEN': root.dataset.tourToken,
                Accept: 'application/json',
            },
        }).catch(() => {});
    };

    // Steps on another page are reached by going to that page; the step number travels in sessionStorage.
    const go = (stepIndex) => {
        if (stepIndex < 0) {
            return;
        }

        if (stepIndex >= steps.length) {
            finish();

            return;
        }

        if (steps[stepIndex].page !== currentPage) {
            storage.set(stepIndex);
            window.location.href = pages[steps[stepIndex].page];

            return;
        }

        show(stepIndex);
    };

    window.addEventListener('resize', schedulePlace);
    window.addEventListener('scroll', schedulePlace, { passive: true });
    document.addEventListener('keydown', (event) => {
        if (!ui) {
            return;
        }

        if (event.key === 'Escape') {
            finish();
        } else if (event.key === 'ArrowRight') {
            go(index + 1);
        } else if (event.key === 'ArrowLeft') {
            go(index - 1);
        }
    });
    document.querySelectorAll('[data-tour-start]').forEach((button) => button.addEventListener('click', () => go(0)));

    const saved = Number.parseInt(storage.get() ?? '', 10);

    if (Number.isInteger(saved) && steps[saved]?.page === currentPage) {
        show(saved);
    } else {
        storage.clear();

        if (root.dataset.tourAutostart === 'true') {
            show(0);
        }
    }
});

// Client portal: summary numbers count up from zero when the page opens.
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('[data-count-up]').forEach((element) => {
        const target = Number(element.dataset.countUp);

        if (!Number.isFinite(target) || target <= 0) {
            return;
        }

        const duration = 900;
        let start = null;
        const tick = (now) => {
            start ??= now;
            const progress = Math.min((now - start) / duration, 1);

            element.textContent = Math.round(target * (1 - (1 - progress) ** 3)).toLocaleString();

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        element.textContent = '0';
        setTimeout(() => requestAnimationFrame(tick), 250);
    });
}

// China Muslim: traveller moments slideshow with autoplay, thumbnails, swipe, keyboard and full screen.
document.querySelectorAll('[data-moments]').forEach((section) => {
    const stage = section.querySelector('[data-moments-stage]');
    const slides = [...section.querySelectorAll('[data-moment]')];
    const thumbs = [...section.querySelectorAll('[data-moment-thumb]')];
    const progress = section.querySelector('[data-moments-progress]');
    const playButton = section.querySelector('[data-moments-play]');
    const fullscreenButton = section.querySelector('[data-moments-fullscreen]');
    const interval = 5500;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let current = 0;
    let playing = !reducedMotion && slides.length > 1;
    let paused = false;
    let elapsed = 0;
    let lastTick = null;

    const show = (index) => {
        current = (index + slides.length) % slides.length;
        elapsed = 0;

        slides.forEach((slide, slideIndex) => {
            const active = slideIndex === current;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        thumbs.forEach((thumb, thumbIndex) => {
            const active = thumbIndex === current;
            thumb.classList.toggle('is-active', active);
            thumb.setAttribute('aria-selected', active ? 'true' : 'false');

            if (active) {
                const strip = thumb.parentElement;
                strip.scrollTo({ left: thumb.offsetLeft - strip.offsetLeft - (strip.clientWidth - thumb.clientWidth) / 2, behavior: reducedMotion ? 'auto' : 'smooth' });
            }
        });
    };

    const setPlaying = (value) => {
        playing = value;
        playButton.textContent = playing ? '❚❚' : '▶';
        playButton.setAttribute('aria-label', playing ? 'Pause slideshow' : 'Play slideshow');
        playButton.setAttribute('aria-pressed', playing ? 'false' : 'true');
        progress.style.width = '0';
        elapsed = 0;
    };

    const tick = (now) => {
        if (lastTick !== null && playing && !paused && !document.hidden) {
            elapsed += now - lastTick;

            if (elapsed >= interval) {
                show(current + 1);
            }

            progress.style.width = `${Math.min(elapsed / interval, 1) * 100}%`;
        }

        lastTick = now;
        requestAnimationFrame(tick);
    };

    section.querySelector('[data-moments-prev]').addEventListener('click', () => show(current - 1));
    section.querySelector('[data-moments-next]').addEventListener('click', () => show(current + 1));
    playButton.addEventListener('click', () => setPlaying(!playing));
    thumbs.forEach((thumb) => thumb.addEventListener('click', () => show(Number(thumb.dataset.momentThumb))));

    fullscreenButton.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            stage.requestFullscreen?.();
        }
    });
    document.addEventListener('fullscreenchange', () => {
        fullscreenButton.setAttribute('aria-label', document.fullscreenElement === stage ? 'Exit full screen' : 'View full screen');
    });

    // Pause while the visitor is looking closely or using the controls.
    stage.addEventListener('pointerenter', () => { paused = true; });
    stage.addEventListener('pointerleave', () => { paused = false; });
    section.addEventListener('focusin', () => { paused = true; });
    section.addEventListener('focusout', () => { paused = false; });

    stage.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            show(current + 1);
        } else if (event.key === 'ArrowLeft') {
            show(current - 1);
        }
    });

    let touchStartX = null;
    stage.addEventListener('touchstart', (event) => { touchStartX = event.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', (event) => {
        if (touchStartX === null) {
            return;
        }

        const distance = event.changedTouches[0].clientX - touchStartX;

        if (Math.abs(distance) > 40) {
            show(current + (distance < 0 ? 1 : -1));
        }

        touchStartX = null;
    });

    setPlaying(playing);
    requestAnimationFrame(tick);
});

// Smart Muslim packages: love / like buttons. Counts load together; buttons sharing a key stay in sync.
(() => {
    const buttons = [...document.querySelectorAll('[data-like-key]')];

    if (buttons.length === 0) {
        return;
    }

    const xsrfToken = () => decodeURIComponent(document.cookie.split('; ').find((row) => row.startsWith('XSRF-TOKEN='))?.split('=')[1] ?? '');
    const buttonsFor = (key) => buttons.filter((button) => button.dataset.likeKey === key);

    const render = (key, { count, liked }) => {
        buttonsFor(key).forEach((button) => {
            button.setAttribute('aria-pressed', String(liked));
            button.querySelector('[data-like-count]').textContent = count > 0 ? String(count) : '';
            button.title = liked ? 'You love this package' : 'Love this package';
        });
    };

    const keys = [...new Set(buttons.map((button) => button.dataset.likeKey))];
    const query = new URLSearchParams();
    keys.forEach((key) => query.append('keys[]', key));

    fetch(`/package-likes?${query}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
        .then((response) => (response.ok ? response.json() : {}))
        .then((summary) => Object.entries(summary).forEach(([key, state]) => render(key, state)))
        .catch(() => {});

    buttons.forEach((button) => {
        button.addEventListener('click', async (event) => {
            // Some buttons sit over a card link; keep the click on the heart.
            event.preventDefault();
            event.stopPropagation();

            const key = button.dataset.likeKey;
            const wasLiked = button.getAttribute('aria-pressed') === 'true';
            const countLabel = button.querySelector('[data-like-count]');
            const previous = { count: Number(countLabel.textContent) || 0, liked: wasLiked };

            render(key, { count: Math.max(0, previous.count + (wasLiked ? -1 : 1)), liked: !wasLiked });
            buttonsFor(key).forEach((sameKey) => { sameKey.disabled = true; });

            if (!wasLiked) {
                button.classList.remove('is-popping');
                void button.offsetWidth;
                button.classList.add('is-popping');
            }

            try {
                const response = await fetch('/package-likes', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
                    body: JSON.stringify({ key }),
                });

                render(key, response.ok ? await response.json() : previous);
            } catch {
                render(key, previous);
            } finally {
                buttonsFor(key).forEach((sameKey) => { sameKey.disabled = false; });
            }
        });
    });
})();

// Admin: stat numbers count up when the page opens, keeping text around the number ("RM 9,450", "31%", "5 / 17").
if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    document.querySelectorAll('.admin-stat-value').forEach((element) => {
        const match = element.textContent.trim().match(/^(\D*?)(\d[\d,]*)(.*)$/s);
        const target = match ? Number(match[2].replaceAll(',', '')) : NaN;

        if (!Number.isFinite(target) || target <= 0) {
            return;
        }

        const [, before, , after] = match;
        const duration = 800;
        let start = null;
        const tick = (now) => {
            start ??= now;
            const progress = Math.min((now - start) / duration, 1);

            element.textContent = `${before}${Math.round(target * (1 - (1 - progress) ** 3)).toLocaleString('en-US')}${after}`;

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        element.textContent = `${before}0${after}`;
        setTimeout(() => requestAnimationFrame(tick), 200);
    });
}
