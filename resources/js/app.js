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

// The header gains a shadow once the page scrolls under it.
const siteHeader = document.querySelector('.site-header');

if (siteHeader) {
    const markScrolled = () => siteHeader.classList.toggle('is-scrolled', window.scrollY > 8);

    markScrolled();
    window.addEventListener('scroll', markScrolled, { passive: true });
}

// The navbar plays its entrance once per visit, not on every page change.
if (siteHeader?.classList.contains('smart-holidays-header')) {
    let hasPlayedIntro = false;

    try {
        hasPlayedIntro = window.sessionStorage.getItem('navbar-intro-played') === '1';
        window.sessionStorage.setItem('navbar-intro-played', '1');
    } catch {
        // Storage blocked: the entrance plays on every page.
    }

    if (!hasPlayedIntro && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        siteHeader.classList.add('is-introducing');
        window.setTimeout(() => siteHeader.classList.remove('is-introducing'), 1400);
    }
}

// Desktop: a soft pill glides behind the top-level menu items as the pointer or keyboard focus moves across them.
if (primaryNav && siteHeader?.classList.contains('smart-holidays-header')) {
    const desktopNav = window.matchMedia('(min-width: 1101px)');
    const indicator = document.createElement('span');
    indicator.className = 'nav-indicator';
    indicator.setAttribute('aria-hidden', 'true');
    primaryNav.append(indicator);

    const hideIndicator = () => primaryNav.classList.remove('has-indicator');

    const moveIndicatorTo = (item) => {
        if (!desktopNav.matches) {
            hideIndicator();

            return;
        }

        const target = item.querySelector(':scope > .muslim-nav-control') ?? item;
        const navRect = primaryNav.getBoundingClientRect();
        const rect = target.getBoundingClientRect();
        const isAppearing = !primaryNav.classList.contains('has-indicator');

        // Appearing from hidden: jump straight to the item instead of sliding in from the last spot.
        if (isAppearing) {
            indicator.style.transition = 'none';
        }

        indicator.style.setProperty('--indicator-x', `${rect.left - navRect.left}px`);
        indicator.style.setProperty('--indicator-y', `${rect.top - navRect.top}px`);
        indicator.style.setProperty('--indicator-width', `${rect.width}px`);
        indicator.style.setProperty('--indicator-height', `${rect.height}px`);

        if (isAppearing) {
            indicator.getBoundingClientRect();
            indicator.style.transition = '';
        }

        primaryNav.classList.add('has-indicator');
    };

    primaryNav.querySelectorAll(':scope > .nav-item, :scope > a').forEach((item) => {
        item.addEventListener('pointerenter', () => moveIndicatorTo(item));
        item.addEventListener('focusin', () => moveIndicatorTo(item));
    });

    primaryNav.addEventListener('pointerleave', hideIndicator);
    primaryNav.addEventListener('focusout', (event) => {
        if (!primaryNav.contains(event.relatedTarget)) {
            hideIndicator();
        }
    });
    desktopNav.addEventListener('change', hideIndicator);
}

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
    const counter = carousel.querySelector('[data-campaign-current]');
    const interval = Number(carousel.dataset.campaignInterval) || 0;
    const canAutoplay = interval > 0 && slides.length > 1 && !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const pauseReasons = new Set();
    let activeIndex = 0;
    let timer = null;
    let startedAt = 0;
    let remaining = interval;

    carousel.style.setProperty('--cs-interval', `${interval}ms`);
    carousel.classList.toggle('is-static', !canAutoplay);

    // Autoplay keeps its place when paused, so the progress bar and the timer stay in step.
    const schedule = () => {
        window.clearTimeout(timer);

        if (!canAutoplay || pauseReasons.size > 0) {
            return;
        }

        startedAt = performance.now();
        timer = window.setTimeout(() => showSlide(activeIndex + 1), remaining);
    };

    const pause = (reason) => {
        if (pauseReasons.size === 0) {
            window.clearTimeout(timer);
            remaining = Math.max(0, remaining - (performance.now() - startedAt));
        }

        pauseReasons.add(reason);
        carousel.classList.add('is-paused');
    };

    const resume = (reason) => {
        if (!pauseReasons.delete(reason)) {
            return;
        }

        if (pauseReasons.size === 0) {
            carousel.classList.remove('is-paused');
            schedule();
        }
    };

    const showSlide = (index) => {
        activeIndex = (index + slides.length) % slides.length;
        remaining = interval;

        if (counter) {
            counter.textContent = String(activeIndex + 1).padStart(2, '0');
        }

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

        schedule();
    };

    if (canAutoplay) {
        carousel.addEventListener('mouseenter', () => pause('hover'));
        carousel.addEventListener('mouseleave', () => resume('hover'));
        // Keyboard focus pauses; a mouse click on an arrow should not stop the slideshow.
        carousel.addEventListener('focusin', (event) => {
            if (event.target.matches(':focus-visible')) {
                pause('focus');
            }
        });
        carousel.addEventListener('focusout', (event) => {
            if (!carousel.contains(event.relatedTarget)) {
                resume('focus');
            }
        });
        document.addEventListener('visibilitychange', () => (document.hidden ? pause('hidden') : resume('hidden')));
        new IntersectionObserver(([entry]) => (entry.isIntersecting ? resume('offscreen') : pause('offscreen')), { threshold: 0.3 }).observe(carousel);
        schedule();
    }

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

// Featured destinations: stacked full-screen slides that wipe in over each other and advance on their own.
// Autoplay pauses on hover, focus, when scrolled away, when the tab is hidden or when the visitor presses pause.
document.querySelectorAll('[data-sh-journey-carousel]').forEach((carousel) => {
    const stage = carousel.querySelector('.sh-cine-stage');
    const slides = [...carousel.querySelectorAll('[data-sh-journey-slide]')];
    const selectors = [...carousel.querySelectorAll('[data-sh-journey-select]')];
    const toggleButton = carousel.querySelector('[data-sh-journey-toggle]');
    const nextCard = carousel.querySelector('[data-sh-journey-next-card]');
    const nextImage = carousel.querySelector('[data-sh-journey-next-image]');
    const nextTitle = carousel.querySelector('[data-sh-journey-next-title]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const interval = 7000;
    let currentIndex = 0;
    let timer = null;
    let startedAt = 0;
    let elapsed = 0;
    let isStoppedByVisitor = prefersReducedMotion;
    let isHovered = false;
    let isInView = true;
    let pointerStartX = null;
    let pointerStartY = null;
    let suppressClick = false;

    carousel.style.setProperty('--sh-cine-interval', `${interval}ms`);

    const shouldPlay = () => !isStoppedByVisitor && !isHovered && isInView && !document.hidden && slides.length > 1;

    // Runs or pauses the countdown, keeping the time already spent on the current slide.
    const schedule = () => {
        const isPlaying = shouldPlay();

        if (timer !== null) {
            window.clearTimeout(timer);
            timer = null;
            elapsed += performance.now() - startedAt;
        }

        carousel.classList.toggle('is-paused', !isPlaying);

        if (isPlaying) {
            startedAt = performance.now();
            timer = window.setTimeout(() => showSlide(currentIndex + 1, 1), Math.max(0, interval - elapsed));
        }
    };

    const showSlide = (index, direction = index >= currentIndex ? 1 : -1) => {
        const nextIndex = (index + slides.length) % slides.length;
        const previousSlide = slides[currentIndex];
        const isChange = nextIndex !== currentIndex;

        carousel.dataset.direction = direction > 0 ? 'forward' : 'backward';
        currentIndex = nextIndex;

        slides.forEach((slide, slideIndex) => {
            const isActive = slideIndex === currentIndex;

            slide.classList.toggle('is-active', isActive);
            slide.classList.toggle('is-leaving', isChange && slide === previousSlide);
            slide.setAttribute('aria-hidden', String(!isActive));
            slide.inert = !isActive;

            const video = slide.querySelector('[data-sh-journey-video]');

            if (video && isActive && !prefersReducedMotion) {
                video.preload = 'auto';
                video.play().catch(() => {});
            } else if (video && slide !== previousSlide) {
                video.pause();
            }
        });

        if (isChange) {
            window.setTimeout(() => {
                previousSlide.classList.remove('is-leaving');
                previousSlide.querySelector('[data-sh-journey-video]')?.pause();
            }, 1200);
        }

        selectors.forEach((selector) => {
            const selectorIndex = Number(selector.dataset.shJourneyIndex);

            selector.classList.toggle('is-done', selectorIndex < currentIndex);

            if (selectorIndex === currentIndex) {
                selector.setAttribute('aria-current', 'true');
            } else {
                selector.removeAttribute('aria-current');
            }
        });

        const upcoming = slides[(currentIndex + 1) % slides.length];
        nextImage.src = upcoming.querySelector('.sh-cine-media img').currentSrc || upcoming.querySelector('.sh-cine-media img').src;
        nextTitle.textContent = upcoming.querySelector('h3').textContent.trim();

        // Restart the progress fill for the new slide.
        const fill = selectors[currentIndex]?.querySelector('.sh-cine-progress-track i');

        selectors.forEach((selector) => selector.querySelector('.sh-cine-progress-track i').classList.remove('is-running'));

        if (fill) {
            void fill.offsetWidth;
            fill.classList.add('is-running');
        }

        window.clearTimeout(timer);
        timer = null;
        elapsed = 0;
        schedule();
    };

    const setStopped = (isStopped) => {
        isStoppedByVisitor = isStopped;
        carousel.classList.toggle('is-stopped', isStopped);
        toggleButton.setAttribute('aria-pressed', String(isStopped));
        toggleButton.setAttribute('aria-label', isStopped ? 'Play slideshow' : 'Pause slideshow');
        toggleButton.firstElementChild.textContent = isStopped ? '▶' : '❚❚';
    };

    carousel.querySelector('[data-sh-journey-previous]').addEventListener('click', () => showSlide(currentIndex - 1, -1));
    carousel.querySelector('[data-sh-journey-next]').addEventListener('click', () => showSlide(currentIndex + 1, 1));
    nextCard.addEventListener('click', () => showSlide(currentIndex + 1, 1));
    toggleButton.addEventListener('click', () => {
        setStopped(!isStoppedByVisitor);
        showSlide(currentIndex);
    });
    selectors.forEach((selector) => selector.addEventListener('click', () => showSlide(Number(selector.dataset.shJourneyIndex))));

    stage.addEventListener('pointerdown', (event) => {
        if (event.pointerType === 'mouse' && event.button !== 0) {
            return;
        }

        pointerStartX = event.clientX;
        pointerStartY = event.clientY;
    });
    stage.addEventListener('pointerup', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distanceX = event.clientX - pointerStartX;
        const distanceY = event.clientY - pointerStartY;
        pointerStartX = null;
        pointerStartY = null;

        if (Math.abs(distanceX) > 50 && Math.abs(distanceX) > Math.abs(distanceY)) {
            suppressClick = true;
            window.setTimeout(() => { suppressClick = false; }, 0);
            showSlide(currentIndex + (distanceX < 0 ? 1 : -1), distanceX < 0 ? 1 : -1);
        }
    });
    stage.addEventListener('pointercancel', () => {
        pointerStartX = null;
        pointerStartY = null;
    });
    stage.addEventListener('click', (event) => {
        if (suppressClick) {
            event.preventDefault();
            event.stopImmediatePropagation();
            suppressClick = false;
        }
    }, true);

    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            showSlide(currentIndex + (event.key === 'ArrowRight' ? 1 : -1), event.key === 'ArrowRight' ? 1 : -1);
        }
    });

    const setHovered = (value) => {
        isHovered = value;
        schedule();
    };

    stage.addEventListener('mouseenter', () => setHovered(true));
    stage.addEventListener('mouseleave', () => setHovered(false));
    carousel.addEventListener('focusin', () => setHovered(true));
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            setHovered(false);
        }
    });
    document.addEventListener('visibilitychange', schedule);

    new IntersectionObserver(([entry]) => {
        isInView = entry.isIntersecting;
        schedule();
    }, { threshold: 0.4 }).observe(carousel);

    setStopped(isStoppedByVisitor);
    showSlide(0);
});

// Country pages: destination slider. Native scroll-snap handles touch; arrows, keys and mouse drag move it on desktop.
document.querySelectorAll('[data-pk-dest]').forEach((slider) => {
    const track = slider.querySelector('[data-pk-dest-track]');
    const cards = [...slider.querySelectorAll('[data-pk-dest-card]')];
    const previousButton = slider.querySelector('[data-pk-dest-previous]');
    const nextButton = slider.querySelector('[data-pk-dest-next]');
    const count = slider.querySelector('[data-pk-dest-count]');
    const progress = slider.querySelector('[data-pk-dest-progress]');
    const pad = (number) => String(number).padStart(2, '0');
    let dragStartX = null;
    let dragStartScroll = 0;
    let dragged = false;

    const stride = () => cards[0].getBoundingClientRect().width + Number.parseFloat(window.getComputedStyle(track).columnGap || 0);

    const update = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        const firstVisible = Math.min(cards.length - 1, Math.round(track.scrollLeft / stride()));
        const visibleCount = Math.max(1, Math.round(track.clientWidth / stride()));

        previousButton.disabled = track.scrollLeft <= 1;
        nextButton.disabled = track.scrollLeft >= maxScroll - 1;
        count.innerHTML = `<b>${pad(firstVisible + 1)}</b> / ${pad(cards.length)}`;
        progress.style.transform = `scaleX(${maxScroll > 0 ? Math.min(1, (track.scrollLeft + track.clientWidth) / track.scrollWidth) : 1})`;
        cards.forEach((card, index) => card.classList.toggle('is-in-view', index >= firstVisible && index < firstVisible + visibleCount));
    };

    const move = (direction) => track.scrollBy({ left: direction * stride(), behavior: 'smooth' });

    previousButton.addEventListener('click', () => move(-1));
    nextButton.addEventListener('click', () => move(1));
    slider.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            move(event.key === 'ArrowRight' ? 1 : -1);
        }
    });

    track.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse' || event.button !== 0) {
            return;
        }

        dragStartX = event.clientX;
        dragStartScroll = track.scrollLeft;
        dragged = false;
    });
    window.addEventListener('pointermove', (event) => {
        if (dragStartX === null) {
            return;
        }

        const distance = event.clientX - dragStartX;

        if (!dragged && Math.abs(distance) > 6) {
            dragged = true;
            track.classList.add('is-dragging');
        }

        if (dragged) {
            track.scrollLeft = dragStartScroll - distance;
        }
    });
    window.addEventListener('pointerup', () => {
        if (dragStartX === null) {
            return;
        }

        dragStartX = null;
        track.classList.remove('is-dragging');

        if (dragged) {
            const snapped = Math.round(track.scrollLeft / stride()) * stride();
            track.scrollTo({ left: snapped, behavior: 'smooth' });
        }
    });
    // A drag should not also open the card link it started on.
    track.addEventListener('click', (event) => {
        if (dragged) {
            event.preventDefault();
            dragged = false;
        }
    }, true);

    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
});

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

                // On narrow screens the tab bar scrolls; keep the active tab in view without moving the page.
                const tabBar = tab.parentElement;

                if (tabBar.scrollWidth > tabBar.clientWidth) {
                    tabBar.scrollTo({ left: tab.offsetLeft - (tabBar.clientWidth - tab.offsetWidth) / 2, behavior: 'smooth' });
                }
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
    // A full-screen showcase is nearly always under the mouse, so only the pause button or keyboard focus pauses it.
    const isShowcase = baliCarousel.classList.contains('pk-showcase-stage');
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

        baliCarousel.classList.remove('is-playing');

        if (userPaused || isHovered || isFocused || document.hidden || motionPreference.matches || slides.length < 2) {
            return;
        }

        timer = window.setInterval(() => showSlide(activeIndex + 1), 6000);
        // Restart the thumbnail progress bar in step with the timer.
        void baliCarousel.offsetWidth;
        baliCarousel.classList.add('is-playing');
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

    previousButton?.addEventListener('click', () => {
        showSlide(activeIndex - 1);
        updateAutoplay();
    });
    nextButton?.addEventListener('click', () => {
        showSlide(activeIndex + 1);
        updateAutoplay();
    });

    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showSlide(index);
            updateAutoplay();
        });
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
        isHovered = !isShowcase;
        updateAutoplay();
    });

    baliCarousel.addEventListener('pointerleave', () => {
        isHovered = false;
        updateAutoplay();
    });

    baliCarousel.addEventListener('focusin', (event) => {
        isFocused = !isShowcase || event.target.matches(':focus-visible');
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
    '[data-reveal]', '.service-card', '.footer-inner > *',
    '.island-intro-copy', '.island-photo-stack', '.island-section-heading', '.island-highlight',
    '.aceh-itinerary-heading', '.aceh-day', '.bali-gallery', '.aceh-details-card', '.aceh-hotel-card', '.island-contact > *', '.pk-dest',
    '.series-head', '.series-advantage', '.series-flag', '.series-photo',
    '.travel-moments__heading', '.travel-moment-card',
    '.focus-tour-card', '.focus-tours-cta__inner', '.booking-toc__card', '.booking-card', '.destination-state',
    '.brand-tile', '.china-cta', '.china-moments',
    '.honeymoon-islands-heading', '.honeymoon-island-card', '.mice-gallery > h2', '.mice-clientele > h2',
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
} else {
    const backgroundVideos = [...document.querySelectorAll('[data-background-video], [data-sh-journey-video]')];

    // Slide videos only run while their slide is showing; section backgrounds always run.
    const shouldLoop = (video) => !video.matches('[data-sh-journey-video]') || Boolean(video.closest('.is-active'));

    const restart = (video) => {
        video.currentTime = 0;
        video.play().catch(() => {});
    };

    backgroundVideos.forEach((video) => {
        video.loop = true;
        video.muted = true;

        // Hosts that cannot seek (no byte-range support) stop the native loop on the last frame; restart by hand.
        video.addEventListener('ended', () => {
            if (shouldLoop(video)) {
                restart(video);
            }
        });

        // Some browsers stall near the end instead of firing "ended" when seeking is unsupported.
        video.addEventListener('timeupdate', () => {
            if (video.duration && video.duration - video.currentTime < 0.25 && !video.paused && shouldLoop(video)) {
                restart(video);
            }
        });
    });

    // Browsers pause background videos in hidden tabs; resume them when the visitor comes back.
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) {
            backgroundVideos.filter((video) => video.paused && shouldLoop(video) && (video.hasAttribute('autoplay') || video.matches('[data-sh-journey-video]'))).forEach((video) => video.play().catch(() => {}));
        }
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

    // "Open all" and "Close all" only change the packages the filters leave on screen.
    form.querySelectorAll('[data-booking-bulk]').forEach((button) => {
        button.addEventListener('click', () => {
            button.closest('section').querySelectorAll('[data-booking-row]:not([hidden]) .admin-booking-toggle input').forEach((input) => {
                input.checked = button.dataset.bookingBulk === 'open';
                label(input);
            });
        });
    });

    // Filter bar: search, package type and status. Rows are only hidden, so they are still saved with the form.
    const filters = form.querySelector('[data-booking-filters]');
    const search = filters?.querySelector('[data-booking-filter-search]');
    const rows = [...form.querySelectorAll('[data-booking-row]')];
    let type = 'all';
    let state = 'all';

    const matchesState = (row) => {
        const isOpen = row.querySelector('.admin-booking-toggle input').checked;

        return state === 'all'
            || (state === 'open' && isOpen)
            || (state === 'closed' && !isOpen)
            || (state === 'limited' && row.dataset.seatState !== 'none')
            || (state === 'full' && row.dataset.seatState === 'full');
    };

    function applyFilters() {
        if (!filters) {
            return;
        }

        const words = search.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        let shown = 0;

        rows.forEach((row) => {
            const section = row.closest('[data-booking-section]').dataset.bookingSection;
            const visible = (type === 'all' || type === section)
                && words.every((word) => row.dataset.search.includes(word))
                && matchesState(row);

            row.hidden = !visible;
            form.querySelector(`[data-booking-row-detail="${row.dataset.bookingRow}"]`)?.toggleAttribute('hidden', !visible);
            shown += visible ? 1 : 0;
        });

        form.querySelectorAll('[data-booking-section]').forEach((section) => {
            section.hidden = !section.querySelector('[data-booking-row]:not([hidden])');
        });

        const filtered = shown !== rows.length;
        filters.querySelector('[data-booking-filter-result]').textContent = filtered
            ? `Showing ${shown} of ${rows.length} ${rows.length === 1 ? 'package' : 'packages'}`
            : `Showing all ${rows.length} ${rows.length === 1 ? 'package' : 'packages'}`;
        form.querySelector('[data-booking-filter-empty]').hidden = shown > 0;
    }

    const choose = (attribute, value) => {
        filters.querySelectorAll(`[${attribute}]`).forEach((chip) => {
            const isActive = chip.getAttribute(attribute) === value;

            chip.classList.toggle('is-active', isActive);
            chip.setAttribute('aria-pressed', String(isActive));
        });
    };

    if (filters) {
        filters.addEventListener('click', (event) => {
            const chip = event.target.closest('[data-booking-filter-type], [data-booking-filter-state]');

            if (!chip) {
                return;
            }

            if (chip.hasAttribute('data-booking-filter-type')) {
                type = chip.dataset.bookingFilterType;
                choose('data-booking-filter-type', type);
            } else {
                state = chip.dataset.bookingFilterState;
                choose('data-booking-filter-state', state);
            }

            applyFilters();
        });
        search.addEventListener('input', applyFilters);
        // Enter in the search box must not submit (and save) the whole form.
        search.addEventListener('keydown', (event) => {
            if (event.key === 'Enter') {
                event.preventDefault();
            }
        });
        form.querySelector('[data-booking-filter-clear]').addEventListener('click', () => {
            search.value = '';
            type = 'all';
            state = 'all';
            choose('data-booking-filter-type', type);
            choose('data-booking-filter-state', state);
            applyFilters();
            search.focus();
        });
    }
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
// Only client accounts can like; anyone else gets a popup asking them to log in or sign up.
(() => {
    const buttons = [...document.querySelectorAll('[data-like-key]')];

    if (buttons.length === 0) {
        return;
    }

    let signInDialog = null;

    const showSignInDialog = ({ message, login_url: loginUrl, register_url: registerUrl }) => {
        if (!signInDialog) {
            signInDialog = document.createElement('dialog');
            signInDialog.className = 'like-signin-dialog';
            signInDialog.setAttribute('aria-labelledby', 'like-signin-title');
            signInDialog.innerHTML = `
                <button class="like-signin-close" type="button" aria-label="Close">×</button>
                <span class="like-signin-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 20.6s-7.6-4.6-9.4-9.3C1.4 8 3.4 4.4 7 4.4c2 0 3.4 1.1 5 3 1.6-1.9 3-3 5-3 3.6 0 5.6 3.6 4.4 6.9-1.8 4.7-9.4 9.3-9.4 9.3Z"/></svg></span>
                <h2 id="like-signin-title">Love this package?</h2>
                <p data-like-signin-message></p>
                <div class="like-signin-actions">
                    <a class="like-signin-login" data-like-signin-login>Log in</a>
                    <a class="like-signin-register" data-like-signin-register>Sign up</a>
                </div>`;
            signInDialog.querySelector('.like-signin-close').addEventListener('click', () => signInDialog.close());
            signInDialog.addEventListener('click', (event) => {
                if (event.target === signInDialog) {
                    signInDialog.close();
                }
            });
            document.body.append(signInDialog);
        }

        signInDialog.querySelector('[data-like-signin-message]').textContent = message ?? 'Please log in or sign up to love this package.';
        signInDialog.querySelector('.like-signin-actions').hidden = !loginUrl;
        signInDialog.querySelector('[data-like-signin-login]').href = loginUrl ?? '#';
        signInDialog.querySelector('[data-like-signin-register]').href = registerUrl ?? '#';
        signInDialog.showModal();
    };

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

            buttonsFor(key).forEach((sameKey) => { sameKey.disabled = true; });

            try {
                const response = await fetch('/package-likes', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': xsrfToken() },
                    body: JSON.stringify({ key }),
                });

                if (response.status === 401 || response.status === 403) {
                    render(key, previous);
                    showSignInDialog(await response.json());

                    return;
                }

                const state = response.ok ? await response.json() : previous;
                render(key, state);

                if (state.liked && !wasLiked) {
                    button.classList.remove('is-popping');
                    void button.offsetWidth;
                    button.classList.add('is-popping');
                }
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

// Package pages: full-screen button on the photo showcase.
document.querySelectorAll('[data-pk-fullscreen]').forEach((button) => {
    const stage = button.closest('.pk-showcase-stage');

    if (!stage || !document.fullscreenEnabled) {
        button.hidden = true;

        return;
    }

    button.addEventListener('click', () => {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            stage.requestFullscreen().catch(() => {});
        }
    });

    document.addEventListener('fullscreenchange', () => {
        const isFull = document.fullscreenElement === stage;

        button.setAttribute('aria-label', isFull ? 'Exit full screen' : 'View full screen');
        button.querySelector('span').textContent = isFull ? '✕' : '⛶';
    });
});

// Floating language switcher: Google Translate translates the whole page and keeps the choice
// in its "googtrans" cookie, so every page opens in the chosen language. The choice is also kept in
// localStorage and applied through Google's language select, because embedded browsers (such as an
// editor's preview pane) may block the cookie, which would otherwise reload the page in English.
const languageSwitcher = document.querySelector('[data-language-switcher]');

if (languageSwitcher) {
    const languageButton = languageSwitcher.querySelector('.language-switcher-button');
    const languageMenu = languageSwitcher.querySelector('.language-switcher-menu');
    const languageOptions = [...languageMenu.querySelectorAll('[data-language]')];
    const languageStorageKey = 'smart-holidays-language';
    const cookieLanguage = () => document.cookie.match(/(?:^|;\s*)googtrans=\/[^/]*\/([^;]+)/)?.[1] ?? null;

    const storedLanguage = () => {
        try {
            return window.localStorage.getItem(languageStorageKey);
        } catch {
            return null;
        }
    };

    const savedLanguage = () => cookieLanguage() ?? storedLanguage() ?? 'en';

    const storeLanguage = (language) => {
        try {
            window.localStorage.setItem(languageStorageKey, language);
        } catch {
            // Storage blocked: the cookie alone remembers the choice.
        }
    };

    // Google Translate may write its cookie on any parent domain (e.g. ".infinityfreeapp.com"), so every
    // level of the hostname is written or cleared, otherwise a leftover cookie keeps the page translated.
    const setTranslateCookie = (language) => {
        const hostParts = location.hostname.split('.');
        const domains = [''];

        hostParts.forEach((_, index) => {
            const domain = hostParts.slice(index).join('.');

            if (domain.includes('.')) {
                domains.push(`; domain=${domain}`, `; domain=.${domain}`);
            }
        });

        domains.forEach((domain) => {
            document.cookie = `googtrans=; path=/${domain}; expires=Thu, 01 Jan 1970 00:00:00 GMT`;
        });

        if (language !== 'en') {
            document.cookie = `googtrans=/en/${language}; path=/; max-age=31536000`;
        }
    };

    const showLanguage = (language) => {
        const current = languageOptions.find((option) => option.dataset.language === language) ?? languageOptions[0];

        languageOptions.forEach((option) => option.setAttribute('aria-checked', String(option === current)));
        languageSwitcher.querySelector('[data-language-current]').textContent = current.dataset.languageShort;
    };

    const loadTranslator = () => {
        if (document.querySelector('script[data-google-translate]')) {
            return;
        }

        window.googleTranslateElementInit = () => {
            new window.google.translate.TranslateElement({
                pageLanguage: 'en',
                includedLanguages: 'en,zh-CN,ms',
                autoDisplay: false,
            }, 'google-translate-element');
        };

        const script = document.createElement('script');
        script.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
        script.async = true;
        script.dataset.googleTranslate = '';
        document.head.append(script);
    };

    // Google builds its language select a moment after its script loads, so wait for it and then pick the language.
    const translateWith = (language) => {
        const startedAt = Date.now();

        const pickLanguage = () => {
            const translateSelect = document.querySelector('.goog-te-combo');

            if (translateSelect?.querySelector(`option[value="${language}"]`)) {
                translateSelect.value = language;
                translateSelect.dispatchEvent(new Event('change'));

                return;
            }

            if (Date.now() - startedAt < 15000) {
                window.setTimeout(pickLanguage, 250);
            }
        };

        loadTranslator();
        pickLanguage();
    };

    const toggleLanguageMenu = (open) => {
        languageMenu.hidden = !open;
        languageButton.setAttribute('aria-expanded', String(open));
        languageSwitcher.classList.toggle('is-open', open);
    };

    const chooseLanguage = (language) => {
        toggleLanguageMenu(false);

        if (language === savedLanguage()) {
            return;
        }

        setTranslateCookie(language);
        storeLanguage(language);
        showLanguage(language);

        // Going back to English is most reliable with a reload.
        if (language === 'en') {
            location.reload();

            return;
        }

        translateWith(language);
    };

    showLanguage(savedLanguage());

    // Google applies the cookie by itself; when the cookie was blocked, the stored language is picked instead.
    if (cookieLanguage() !== null) {
        loadTranslator();
    } else if (savedLanguage() !== 'en') {
        translateWith(savedLanguage());
    }

    languageButton.addEventListener('click', () => toggleLanguageMenu(languageMenu.hidden));
    languageOptions.forEach((option) => option.addEventListener('click', () => chooseLanguage(option.dataset.language)));

    document.addEventListener('click', (event) => {
        if (!languageSwitcher.contains(event.target)) {
            toggleLanguageMenu(false);
        }
    });

    languageSwitcher.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            toggleLanguageMenu(false);
            languageButton.focus();
        }
    });
}

// Card sliders (Island & Honeymoon packages): native swipe/scroll, arrow buttons and a progress bar.
document.querySelectorAll('[data-card-slider]').forEach((slider) => {
    const track = slider.querySelector('[data-card-slider-track]');
    const section = slider.closest('section') ?? slider;
    const previous = section.querySelector('[data-card-slider-prev]');
    const next = section.querySelector('[data-card-slider-next]');
    const progress = slider.querySelector('[data-card-slider-progress]');

    // Group Tours and China Muslim sliders reuse the wrapper but are driven by [data-slider-track] instead.
    if (!track) {
        return;
    }

    const step = () => {
        const card = track.firstElementChild;

        return card ? card.getBoundingClientRect().width + parseFloat(getComputedStyle(track).columnGap || 0) : track.clientWidth;
    };

    const update = () => {
        const maxScroll = track.scrollWidth - track.clientWidth;
        const atStart = track.scrollLeft <= 2;
        const atEnd = track.scrollLeft >= maxScroll - 2;

        previous?.toggleAttribute('disabled', atStart);
        next?.toggleAttribute('disabled', atEnd);
        section.classList.toggle('is-scrollable', maxScroll > 2);

        if (progress) {
            const visible = track.clientWidth / track.scrollWidth;
            progress.style.width = `${Math.max(visible, 0.08) * 100}%`;
            progress.style.transform = `translateX(${maxScroll > 0 ? (track.scrollLeft / maxScroll) * ((1 / Math.max(visible, 0.08)) - 1) * 100 : 0}%)`;
        }
    };

    previous?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
    next?.addEventListener('click', () => track.scrollBy({ left: step(), behavior: 'smooth' }));
    track.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', update);
    update();
});

// Page-to-page motion: a loading line under the header as soon as a link to another page is followed.
document.addEventListener('click', (event) => {
    const link = event.target.closest?.('a[href]');

    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
        return;
    }

    const url = new URL(link.href, location.href);
    const samePage = url.pathname === location.pathname && url.search === location.search;

    if (url.origin !== location.origin || samePage || (link.target && link.target !== '_self') || link.hasAttribute('download')) {
        return;
    }

    document.documentElement.classList.add('is-leaving');
});

// Coming back with the browser's back button can restore the old page as it was; clear the loading line.
window.addEventListener('pageshow', () => document.documentElement.classList.remove('is-leaving'));

// Smart Holidays poster slideshow: a 3D coverflow that advances on its own, pausing on hover, focus,
// when scrolled out of view or when the visitor presses pause.
document.querySelectorAll('[data-sh-poster-carousel]').forEach((carousel) => {
    const stage = carousel.querySelector('.sh-poster-stage');
    const posters = [...carousel.querySelectorAll('[data-sh-poster]')];
    const currentLabel = carousel.querySelector('[data-sh-poster-current]');
    const progressBar = carousel.querySelector('[data-sh-poster-bar]');
    const toggleButton = carousel.querySelector('[data-sh-poster-toggle]');
    const interval = 4500;
    let currentIndex = 0;
    let timer = null;
    let startedAt = 0;
    let elapsed = 0;
    let isStoppedByVisitor = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let isHovered = false;
    let isInView = false;
    let pointerStartX = null;
    let suppressClick = false;

    carousel.style.setProperty('--sh-poster-interval', `${interval}ms`);

    const circularOffset = (index) => {
        let offset = index - currentIndex;

        if (offset > posters.length / 2) {
            offset -= posters.length;
        } else if (offset < -posters.length / 2) {
            offset += posters.length;
        }

        return offset;
    };

    const shouldPlay = () => !isStoppedByVisitor && !isHovered && isInView && !document.hidden && posters.length > 1;

    // Runs or pauses the countdown, keeping time already spent on the current poster.
    const schedule = () => {
        const isPlaying = shouldPlay();

        if (timer !== null) {
            window.clearTimeout(timer);
            timer = null;
            elapsed += performance.now() - startedAt;
        }

        carousel.classList.toggle('is-paused', !isPlaying);

        if (isPlaying) {
            startedAt = performance.now();
            timer = window.setTimeout(() => showPoster(currentIndex + 1), Math.max(0, interval - elapsed));
        }
    };

    const showPoster = (index) => {
        currentIndex = (index + posters.length) % posters.length;

        posters.forEach((poster, posterIndex) => {
            const offset = circularOffset(posterIndex);
            const distance = Math.abs(offset);
            const isActive = offset === 0;

            poster.style.setProperty('--offset', String(offset));
            poster.style.setProperty('--distance', String(distance));
            poster.style.zIndex = String(posters.length - distance);
            poster.classList.toggle('is-active', isActive);
            poster.classList.toggle('is-far', distance > 2);
            poster.setAttribute('aria-hidden', String(!isActive));
            poster.querySelector('a').tabIndex = isActive ? 0 : -1;
        });

        currentLabel.textContent = String(currentIndex + 1).padStart(2, '0');

        window.clearTimeout(timer);
        timer = null;
        elapsed = 0;
        progressBar.classList.remove('is-running');
        void progressBar.offsetWidth;
        progressBar.classList.toggle('is-running', !isStoppedByVisitor);
        schedule();
    };

    const setStopped = (isStopped) => {
        isStoppedByVisitor = isStopped;
        toggleButton.setAttribute('aria-pressed', String(isStopped));
        toggleButton.setAttribute('aria-label', isStopped ? 'Play slideshow' : 'Pause slideshow');
        toggleButton.firstElementChild.textContent = isStopped ? '▶' : '❚❚';
    };

    carousel.querySelector('[data-sh-poster-previous]').addEventListener('click', () => showPoster(currentIndex - 1));
    carousel.querySelector('[data-sh-poster-next]').addEventListener('click', () => showPoster(currentIndex + 1));
    toggleButton.addEventListener('click', () => {
        setStopped(!isStoppedByVisitor);
        showPoster(currentIndex);
    });

    // Clicking a side poster brings it to the front instead of opening its link.
    posters.forEach((poster, posterIndex) => {
        poster.querySelector('a').addEventListener('click', (event) => {
            if (suppressClick || posterIndex !== currentIndex) {
                event.preventDefault();
                suppressClick = false;

                if (posterIndex !== currentIndex) {
                    showPoster(posterIndex);
                }
            }
        });
    });

    stage.addEventListener('pointerdown', (event) => {
        pointerStartX = event.clientX;
    });
    stage.addEventListener('pointerup', (event) => {
        if (pointerStartX === null) {
            return;
        }

        const distanceX = event.clientX - pointerStartX;
        pointerStartX = null;

        if (Math.abs(distanceX) > 40) {
            suppressClick = true;
            showPoster(currentIndex + (distanceX < 0 ? 1 : -1));
        }
    });
    stage.addEventListener('pointercancel', () => {
        pointerStartX = null;
    });

    carousel.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowLeft' || event.key === 'ArrowRight') {
            event.preventDefault();
            showPoster(currentIndex + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });

    const setHovered = (value) => {
        isHovered = value;
        schedule();
    };

    stage.addEventListener('mouseenter', () => setHovered(true));
    stage.addEventListener('mouseleave', () => setHovered(false));
    carousel.addEventListener('focusin', () => setHovered(true));
    carousel.addEventListener('focusout', (event) => {
        if (!carousel.contains(event.relatedTarget)) {
            setHovered(false);
        }
    });
    document.addEventListener('visibilitychange', schedule);

    new IntersectionObserver(([entry]) => {
        isInView = entry.isIntersecting;
        schedule();
    }, { threshold: 0.35 }).observe(carousel);

    setStopped(isStoppedByVisitor);
    showPoster(0);
});

// Short-video reels: the centred reel plays muted, its bar fills with the clip, then the strip moves on.
document.querySelectorAll('[data-sh-reels]').forEach((section) => {
    const track = section.querySelector('[data-sh-reels-track]');
    const reels = [...section.querySelectorAll('[data-sh-reel]')];
    const currentLabel = section.querySelector('[data-sh-reels-current]');
    const toggleButton = section.querySelector('[data-sh-reels-toggle]');
    const maxReelSeconds = 10;
    const fallbackSeconds = 7;
    const loadTimeoutSeconds = 12;
    let currentIndex = 0;
    let isStoppedByVisitor = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    let isInView = false;
    let frame = null;
    let lastTick = 0;
    let waited = 0;
    let scrollTimer = null;

    const viewer = section.querySelector('[data-sh-reel-viewer]');
    const viewerStage = viewer.querySelector('[data-sh-reel-viewer-stage]');
    const viewerVideo = viewer.querySelector('[data-sh-reel-viewer-video]');
    const viewerBar = viewer.querySelector('[data-sh-reel-viewer-bar]');
    const viewerSound = viewer.querySelector('[data-sh-reel-viewer-sound]');
    const viewerLabel = viewer.querySelector('[data-sh-reel-viewer-label]');
    const viewerLink = viewer.querySelector('[data-sh-reel-viewer-link]');
    let viewerIndex = 0;
    let viewerPointer = null;
    let suppressViewerClick = false;

    const videoOf = (reel) => reel.querySelector('[data-sh-reel-video]');
    const shouldPlay = () => !isStoppedByVisitor && isInView && !document.hidden && !viewer.open;

    if (reels.length === 1) {
        videoOf(reels[0]).loop = true;
    }

    const stopLoop = () => {
        if (frame !== null) {
            window.cancelAnimationFrame(frame);
            frame = null;
        }
    };

    const tick = (now) => {
        const reel = reels[currentIndex];
        const video = videoOf(reel);
        const isRunning = !video.paused && video.readyState >= 2;
        let progress = 0;

        if (isRunning) {
            const length = Number.isFinite(video.duration) && video.duration > 0 ? Math.min(video.duration, maxReelSeconds) : maxReelSeconds;
            progress = video.currentTime / length;
        } else {
            waited += (now - lastTick) / 1000;
            // Blocked or broken videos still advance on a timer; slow ones get a generous wait.
            progress = 'reelBlocked' in video.dataset ? waited / fallbackSeconds : (waited >= loadTimeoutSeconds ? 1 : 0);
        }

        lastTick = now;
        reel.style.setProperty('--reel-progress', String(Math.min(progress, 1)));

        if (progress >= 1 && reels.length > 1) {
            showReel(currentIndex + 1);

            return;
        }

        frame = window.requestAnimationFrame(tick);
    };

    const sync = () => {
        const video = videoOf(reels[currentIndex]);

        if (!shouldPlay()) {
            video.pause();
            stopLoop();

            return;
        }

        video.preload = 'auto';
        video.play().then(() => {
            delete video.dataset.reelBlocked;
        }).catch(() => {
            video.dataset.reelBlocked = '';
        });

        if (frame === null) {
            lastTick = performance.now();
            frame = window.requestAnimationFrame(tick);
        }
    };

    const centreOffset = (reel) => reel.offsetLeft - (track.clientWidth - reel.offsetWidth) / 2;

    const showReel = (index, { scroll = true, behavior = 'smooth' } = {}) => {
        currentIndex = (index + reels.length) % reels.length;
        waited = 0;

        reels.forEach((reel, reelIndex) => {
            const isActive = reelIndex === currentIndex;

            reel.classList.toggle('is-active', isActive);
            reel.querySelectorAll('a, [data-sh-reel-open]').forEach((control) => {
                control.tabIndex = isActive ? 0 : -1;
            });

            if (!isActive) {
                const video = videoOf(reel);

                video.pause();

                if (video.readyState > 0) {
                    video.currentTime = 0;
                }

                reel.style.setProperty('--reel-progress', '0');
            }
        });

        currentLabel.textContent = String(currentIndex + 1).padStart(2, '0');

        if (scroll) {
            track.scrollTo({ left: centreOffset(reels[currentIndex]), behavior });
        }

        stopLoop();
        sync();
    };

    const setStopped = (isStopped) => {
        isStoppedByVisitor = isStopped;
        toggleButton.setAttribute('aria-pressed', String(isStopped));
        toggleButton.setAttribute('aria-label', isStopped ? 'Play videos' : 'Pause videos');
        toggleButton.firstElementChild.textContent = isStopped ? '▶' : '❚❚';
        sync();
    };

    section.querySelector('[data-sh-reels-previous]').addEventListener('click', () => showReel(currentIndex - 1));
    section.querySelector('[data-sh-reels-next]').addEventListener('click', () => showReel(currentIndex + 1));
    toggleButton.addEventListener('click', () => setStopped(!isStoppedByVisitor));

    // Full-screen viewer: the chosen reel plays large with sound; the strip waits until it closes.
    const setViewerMuted = (isMuted) => {
        viewerVideo.muted = isMuted;
        viewerSound.setAttribute('aria-pressed', String(isMuted));
        viewerSound.setAttribute('aria-label', isMuted ? 'Turn sound on' : 'Mute sound');
    };

    const playViewer = () => {
        viewerVideo.play().catch(() => {
            // Browsers may refuse sound without a fresh tap; fall back to muted playback.
            if (!viewerVideo.muted) {
                setViewerMuted(true);
                viewerVideo.play().catch(() => {});
            }
        });
    };

    const showInViewer = (index) => {
        viewerIndex = (index + reels.length) % reels.length;

        const reel = reels[viewerIndex];
        const source = videoOf(reel);
        const label = reel.querySelector('.sh-reel-label');
        const link = reel.querySelector('.sh-reel-link');

        viewerVideo.src = source.currentSrc || source.src;
        viewerVideo.poster = source.poster;
        viewerVideo.loop = reels.length === 1;
        viewer.querySelector('[data-sh-reel-viewer-glow]').style.backgroundImage = source.poster ? `url("${source.poster}")` : '';
        viewer.querySelector('[data-sh-reel-viewer-title]').textContent = reel.querySelector('.sh-reel-info strong').textContent;
        viewer.querySelector('[data-sh-reel-viewer-current]').textContent = String(viewerIndex + 1).padStart(2, '0');
        viewerLabel.textContent = label ? label.textContent : '';
        viewerLabel.hidden = !label;
        viewerLink.hidden = !link;
        viewerLink.href = link ? link.href : '#';
        viewerBar.style.transform = 'scaleX(0)';
        viewerStage.classList.remove('is-paused');
        playViewer();
    };

    const openViewer = (index) => {
        showReel(index, { behavior: 'instant' });
        setViewerMuted(false);
        viewer.showModal();
        sync();
        showInViewer(index);
    };

    viewer.addEventListener('close', () => {
        viewerVideo.pause();
        viewerVideo.removeAttribute('src');
        viewerVideo.load();
        showReel(viewerIndex, { behavior: 'instant' });
    });

    viewerVideo.addEventListener('timeupdate', () => {
        if (viewerVideo.duration > 0) {
            viewerBar.style.transform = `scaleX(${viewerVideo.currentTime / viewerVideo.duration})`;
        }
    });
    viewerVideo.addEventListener('ended', () => showInViewer(viewerIndex + 1));
    viewerVideo.addEventListener('play', () => viewerStage.classList.remove('is-paused'));
    viewerVideo.addEventListener('pause', () => viewerStage.classList.toggle('is-paused', viewer.open && !viewerVideo.ended));

    viewerSound.addEventListener('click', () => {
        setViewerMuted(!viewerVideo.muted);
        playViewer();
    });
    viewer.querySelector('[data-sh-reel-viewer-close]').addEventListener('click', () => viewer.close());
    viewer.querySelector('[data-sh-reel-viewer-previous]')?.addEventListener('click', () => showInViewer(viewerIndex - 1));
    viewer.querySelector('[data-sh-reel-viewer-next]')?.addEventListener('click', () => showInViewer(viewerIndex + 1));

    // Tapping the video pauses or resumes it; tapping outside the video closes the viewer.
    viewerVideo.addEventListener('click', () => {
        if (suppressViewerClick) {
            suppressViewerClick = false;

            return;
        }

        if (viewerVideo.paused) {
            playViewer();
        } else {
            viewerVideo.pause();
        }
    });
    viewer.addEventListener('click', (event) => {
        if (event.target === viewer || event.target.matches('[data-sh-reel-viewer-glow]')) {
            viewer.close();
        }
    });

    viewer.addEventListener('keydown', (event) => {
        if (reels.length > 1 && (event.key === 'ArrowLeft' || event.key === 'ArrowRight')) {
            event.preventDefault();
            showInViewer(viewerIndex + (event.key === 'ArrowRight' ? 1 : -1));
        }
    });

    // Swipe sideways for the next or previous video, swipe down to close.
    viewerStage.addEventListener('pointerdown', (event) => {
        viewerPointer = { x: event.clientX, y: event.clientY };
        suppressViewerClick = false;
    });
    viewerStage.addEventListener('pointerup', (event) => {
        if (viewerPointer === null) {
            return;
        }

        const distanceX = event.clientX - viewerPointer.x;
        const distanceY = event.clientY - viewerPointer.y;
        viewerPointer = null;

        if (reels.length > 1 && Math.abs(distanceX) > 50 && Math.abs(distanceX) > Math.abs(distanceY)) {
            suppressViewerClick = true;
            showInViewer(viewerIndex + (distanceX < 0 ? 1 : -1));
        } else if (distanceY > 90 && distanceY > Math.abs(distanceX)) {
            suppressViewerClick = true;
            viewer.close();
        }
    });
    viewerStage.addEventListener('pointercancel', () => {
        viewerPointer = null;
    });

    reels.forEach((reel, index) => {
        reel.addEventListener('click', (event) => {
            if (event.target.closest('.sh-reel-link') && index === currentIndex) {
                return;
            }

            event.preventDefault();
            openViewer(index);
        });

        videoOf(reel).addEventListener('ended', () => {
            if (index === currentIndex && reels.length > 1 && shouldPlay()) {
                showReel(currentIndex + 1);
            }
        });

        videoOf(reel).addEventListener('error', () => {
            videoOf(reel).dataset.reelBlocked = '';
        });
    });

    // A swipe or trackpad scroll makes whichever reel settles in the centre the playing one.
    track.addEventListener('scroll', () => {
        window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(() => {
            const middle = track.scrollLeft + track.clientWidth / 2;
            const nearest = reels.reduce((best, reel, index) => {
                const distance = Math.abs(reel.offsetLeft + reel.offsetWidth / 2 - middle);

                return distance < best.distance ? { index, distance } : best;
            }, { index: currentIndex, distance: Infinity });

            if (nearest.index !== currentIndex) {
                showReel(nearest.index, { scroll: false });
            }
        }, 140);
    }, { passive: true });

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(([entry]) => {
            isInView = entry.isIntersecting;
            sync();
        }, { threshold: 0.35 }).observe(track);
    } else {
        isInView = true;
    }

    document.addEventListener('visibilitychange', sync);
    setStopped(isStoppedByVisitor);
    showReel(Number(section.dataset.startIndex) || 0, { behavior: 'instant' });
});

// Admin settings: picking a colour theme changes the page at once and saves it in the background.
document.querySelectorAll('[data-admin-theme-form]').forEach((form) => {
    const status = form.querySelector('[data-admin-theme-status]');
    let request = 0;

    form.querySelector('[data-admin-theme-save]').hidden = true;

    form.addEventListener('change', async (event) => {
        if (event.target.name !== 'admin_theme') {
            return;
        }

        const current = ++request;

        window.applyAdminTheme?.(event.target.value);
        status.textContent = 'Saving…';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json' },
                body: new FormData(form),
            });

            if (current === request) {
                status.textContent = response.ok ? 'Saved. Every admin page now uses this look.' : 'Could not save. Please try again.';
            }
        } catch {
            if (current === request) {
                status.textContent = 'Could not save. Check your connection and try again.';
            }
        }
    });
});

// Admin guided tour: the "?" button on the dashboard walks through every part of the admin. After each new
// sign-in the dashboard first asks whether the admin would like the tour.
document.querySelectorAll('[data-admin-tour-root]').forEach((root) => {
    const name = root.dataset.tourName;
    const steps = [
        { title: `Welcome to Smart Holidays Admin${name ? `, ${name}` : ''}! 👋`, text: 'Would you like a quick tour? In about two minutes we will show you what every part of the admin does. You can replay it any time with the “?” button.', next: 'Start the tour', skip: 'Not now' },
        { target: 'stats', title: 'Your business at a glance', text: 'Total bookings, revenue from confirmed trips, active clients and enquiries waiting for a reply. The small line compares them with last month.' },
        { target: 'new-booking', title: 'Add a booking yourself', text: 'Booked a client by phone or WhatsApp? Press “New booking” to record it with the client, travel dates, travellers and amount.' },
        { target: 'revenue', title: 'Revenue overview', text: 'Money from confirmed and completed bookings over time. Use the range picker at the top right to switch between the last 7 days, 30 days and longer periods.' },
        { target: 'destinations', title: 'Popular destinations', text: 'Where your clients travel most, with travellers and revenue for each destination.' },
        { target: 'likes', title: 'Most loved packages', text: 'Smart Muslim packages that visitors tapped ♥ on most. A good hint for what to promote next.' },
        { target: 'recent-bookings', title: 'Recent bookings', text: 'The newest bookings with their status. Search here, or open “View all bookings” for the full list.' },
        { target: 'nav-bookings', sidebar: true, title: 'Bookings', text: 'Every booking in one list. Open one to confirm it, set the final amount, or cancel it. Confirming an online booking takes its seats.' },
        { target: 'nav-enquiries', sidebar: true, title: 'Enquiries', text: 'Trip requests and messages from the website. The number shows how many still need a reply; you can turn an enquiry into a booking.' },
        { target: 'nav-destinations', sidebar: true, title: 'Destinations', text: 'Bookings, travellers and revenue for each destination your clients travel to.' },
        { target: 'nav-online-booking', sidebar: true, title: 'Online booking', text: 'Open or close the “Book now” button for each Smart Muslim package, and set the available seats for each package or departure.' },
        { target: 'nav-clients', sidebar: true, title: 'Clients', text: 'Client accounts with their contact details and booking history. You can add clients and suspend an account if needed.' },
        { target: 'nav-package-likes', sidebar: true, title: 'Package likes', text: 'Full statistics of the ♥ likes visitors give each package.' },
        { target: 'nav-settings', sidebar: true, title: 'Settings', text: 'Your own account: name, email, password, light or dark mode, and the devices you are signed in on.' },
        { target: 'nav-smart-holidays', sidebar: true, title: 'Edit the Smart Holidays website', text: 'Change the text, photos and videos of the homepage, Group, Ground and Domestic Tours, Ticketing and Contact Us. Right-click a page for quick actions.' },
        { target: 'nav-smart-muslim', sidebar: true, title: 'Edit the Smart Muslim website', text: 'Manage the Smart Muslim homepage, menus, tour packages, series departures, China Muslim, Island & Honeymoon, MICE and the gallery.' },
        { target: 'notifications', title: 'New enquiries', text: 'A dot on the bell means enquiries are waiting. Press it to go straight to them.' },
        { target: 'help', title: 'Need a reminder?', text: 'Press this “?” button whenever you want to see this tour again.' },
        { title: 'You are all set! 🎉', text: 'That is everything. Enjoy managing Smart Holidays, and press “?” on the Overview page if you need the tour again.', next: 'Finish' },
    ];
    const menuToggle = document.querySelector('.admin-menu-toggle');
    const isVisible = (element) => element !== null && element.getClientRects().length > 0;
    const sidebarIsDrawer = () => isVisible(menuToggle);
    const setSidebarOpen = (open) => {
        if (sidebarIsDrawer() && (menuToggle.getAttribute('aria-expanded') === 'true') !== open) {
            menuToggle.click();
        }
    };

    let index = -1;
    let target = null;
    let frame = null;
    let ui = null;

    const build = () => {
        const backdrop = document.createElement('div');
        backdrop.className = 'cp-tour-backdrop';
        const spotlight = document.createElement('div');
        spotlight.className = 'cp-tour-spotlight admin-tour-spotlight';
        const card = document.createElement('section');
        card.className = 'cp-tour-card admin-tour-card';
        card.setAttribute('role', 'dialog');
        card.setAttribute('aria-modal', 'true');
        card.setAttribute('aria-labelledby', 'admin-tour-title');
        card.innerHTML = `
            <div class="cp-tour-progress"><span></span></div>
            <p class="cp-tour-count"></p>
            <h2 id="admin-tour-title"></h2>
            <p class="cp-tour-text"></p>
            <div class="cp-tour-actions">
                <button type="button" class="cp-tour-skip" data-tour-skip></button>
                <span>
                    <button type="button" class="cp-tour-back" data-tour-back>Back</button>
                    <button type="button" class="cp-tour-next" data-tour-next></button>
                </span>
            </div>`;
        document.body.append(backdrop, spotlight, card);
        // Clicks inside the card must not reach the page, which would close the phone menu.
        card.addEventListener('click', (event) => event.stopPropagation());
        card.querySelector('[data-tour-skip]').addEventListener('click', () => finish());
        card.querySelector('[data-tour-back]').addEventListener('click', () => go(index - 1));
        card.querySelector('[data-tour-next]').addEventListener('click', () => go(index + 1));

        return { backdrop, spotlight, card };
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

        // Inside the scrolling sidebar, only highlight the part of the target that is in view.
        const box = target.getBoundingClientRect();
        const clip = target.closest('.admin-sidebar')?.getBoundingClientRect();
        const visibleTop = clip ? Math.max(box.top, clip.top) : box.top;
        const visibleBottom = clip ? Math.min(box.bottom, clip.bottom) : box.bottom;
        const rect = { top: visibleTop, bottom: visibleBottom, left: box.left, right: box.right, width: box.width, height: Math.max(0, visibleBottom - visibleTop) };
        const pad = 6;
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
        const cardBox = card.getBoundingClientRect();
        const below = rect.bottom + pad + gap;
        const above = rect.top - pad - gap - cardBox.height;
        let top = below + cardBox.height <= innerHeight - 12 ? below : above >= 12 ? above : null;
        let left = rect.left;

        // Tall targets and the sidebar menu get the card beside them.
        if (top === null) {
            left = rect.right + gap + cardBox.width < innerWidth - 12 ? rect.right + pad + gap : rect.left - pad - gap - cardBox.width;
            top = Math.min(Math.max(12, rect.top), innerHeight - cardBox.height - 12);
        }

        card.style.top = `${Math.max(12, top)}px`;
        card.style.left = `${Math.min(Math.max(12, left), innerWidth - cardBox.width - 12)}px`;
    };

    const schedulePlace = () => {
        frame ??= requestAnimationFrame(place);
    };

    const show = (stepIndex) => {
        index = stepIndex;
        ui ??= build();
        document.body.classList.add('cp-tour-running');

        const step = steps[index];
        const { card } = ui;

        setSidebarOpen(Boolean(step.sidebar));
        document.querySelectorAll('.is-admin-tour-target').forEach((element) => element.classList.remove('is-admin-tour-target'));
        target = step.target ? document.querySelector(`[data-admin-tour="${step.target}"]`) : null;
        target?.classList.add('is-admin-tour-target');

        card.querySelector('.cp-tour-count').textContent = `Step ${index + 1} of ${steps.length}`;
        card.querySelector('.cp-tour-progress span').style.width = `${((index + 1) / steps.length) * 100}%`;
        card.querySelector('#admin-tour-title').textContent = step.title;
        card.querySelector('.cp-tour-text').textContent = step.text;
        card.querySelector('[data-tour-back]').hidden = index === 0;
        card.querySelector('[data-tour-skip]').hidden = index === steps.length - 1;
        card.querySelector('[data-tour-skip]').textContent = step.skip ?? 'Skip tour';
        card.querySelector('[data-tour-next]').textContent = step.next ?? 'Next →';

        // The phone menu slides in first, so wait a moment before measuring it.
        setTimeout(() => {
            if (isVisible(target)) {
                const behavior = window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth';
                target.scrollIntoView({ block: target.offsetHeight > innerHeight * 0.6 ? 'start' : 'center', behavior });
            }

            place();
        }, step.sidebar && sidebarIsDrawer() ? 320 : 0);
        place();
        setTimeout(place, 500);
        setTimeout(place, 1000);
        card.querySelector('[data-tour-next]').focus({ preventScroll: true });
    };

    const finish = () => {
        document.body.classList.remove('cp-tour-running', 'cp-tour-centered');
        document.querySelectorAll('.is-admin-tour-target').forEach((element) => element.classList.remove('is-admin-tour-target'));
        ui?.backdrop.remove();
        ui?.spotlight.remove();
        ui?.card.remove();
        ui = null;
        target = null;
        setSidebarOpen(false);
        document.querySelector('[data-admin-tour-start]')?.focus({ preventScroll: true });
    };

    const go = (stepIndex) => {
        if (stepIndex < 0) {
            return;
        }

        if (stepIndex >= steps.length) {
            finish();

            return;
        }

        show(stepIndex);
    };

    window.addEventListener('resize', schedulePlace);
    window.addEventListener('scroll', schedulePlace, { passive: true, capture: true });
    document.addEventListener('keydown', (event) => {
        if (!ui) {
            return;
        }

        if (event.key === 'Escape') {
            event.stopPropagation();
            finish();
        } else if (event.key === 'ArrowRight') {
            go(index + 1);
        } else if (event.key === 'ArrowLeft') {
            go(index - 1);
        }
    }, true);
    // The "?" button skips the welcome question and starts showing things straight away.
    document.querySelectorAll('[data-admin-tour-start]').forEach((button) => button.addEventListener('click', (event) => {
        event.stopPropagation();
        go(1);
    }));

    if (root.dataset.tourOffer === 'true') {
        show(0);
    }
});

// Dashboard: full-screen bookings view. Filter by status, search, and change a booking's status in place.
document.querySelectorAll('[data-booking-manager]').forEach((manager) => {
    const rows = [...manager.querySelectorAll('[data-manager-row]')];
    const search = manager.querySelector('[data-manager-search]');
    const message = manager.querySelector('[data-manager-message]');
    const token = manager.dataset.token;
    let status = 'all';
    let messageTimer = null;

    const say = (text, isError = false) => {
        window.clearTimeout(messageTimer);
        message.textContent = text;
        message.hidden = false;
        message.classList.toggle('is-error', isError);
        messageTimer = window.setTimeout(() => {
            message.hidden = true;
        }, isError ? 9000 : 3500);
    };

    const apply = () => {
        const words = search.value.trim().toLowerCase().split(/\s+/).filter(Boolean);
        let shown = 0;

        rows.forEach((row) => {
            const visible = (status === 'all' || row.dataset.status === status)
                && words.every((word) => row.dataset.search.includes(word));

            row.hidden = !visible;
            shown += visible ? 1 : 0;
        });

        manager.querySelector('[data-manager-empty]').hidden = shown > 0;
        manager.querySelector('[data-manager-result]').textContent = shown === rows.length
            ? `Showing ${shown} of ${manager.dataset.total} bookings`
            : `Showing ${shown} of the ${rows.length} latest bookings`;
    };

    const recount = () => {
        manager.querySelectorAll('[data-manager-count]').forEach((count) => {
            const value = count.dataset.managerCount;

            count.textContent = String(value === 'all' ? rows.length : rows.filter((row) => row.dataset.status === value).length);
        });
    };

    document.querySelectorAll('[data-booking-manager-open]').forEach((button) => button.addEventListener('click', () => {
        manager.showModal();
        search.focus();
    }));
    manager.querySelector('[data-booking-manager-close]').addEventListener('click', () => manager.close());
    // Changes made here also show on the dashboard once it reloads.
    manager.addEventListener('close', () => {
        if (manager.dataset.changed === 'true') {
            window.location.reload();
        }
    });

    manager.querySelectorAll('[data-manager-status]').forEach((chip) => chip.addEventListener('click', () => {
        status = chip.dataset.managerStatus;
        manager.querySelectorAll('[data-manager-status]').forEach((other) => {
            other.classList.toggle('is-active', other === chip);
            other.setAttribute('aria-pressed', String(other === chip));
        });
        apply();
    }));
    search.addEventListener('input', apply);

    manager.querySelectorAll('[data-manager-status-select]').forEach((select) => {
        let saved = select.value;

        select.addEventListener('change', async () => {
            const row = select.closest('[data-manager-row]');
            const field = select.closest('[data-manager-status-field]');

            field.classList.add('is-saving');
            select.disabled = true;

            try {
                const response = await fetch(select.dataset.url, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ status: select.value }),
                });
                const result = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(result.errors?.status?.[0] ?? result.message ?? 'Could not change the status. Please try again.');
                }

                field.classList.remove(`status-${saved}`);
                saved = result.status;
                field.classList.add(`status-${saved}`);
                row.dataset.status = saved;
                manager.dataset.changed = 'true';
                recount();
                say(result.message);
            } catch (error) {
                select.value = saved;
                say(`${select.dataset.reference}: ${error.message}`, true);
            } finally {
                field.classList.remove('is-saving');
                select.disabled = false;
            }
        });
    });
});
