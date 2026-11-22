(function () {
  function parseData(card) {
    var el = card.querySelector('script.sv-swatch-data');
    if (!el) return null;
    try {
      return JSON.parse(el.textContent);
    } catch (e) {
      return null;
    }
  }

  function findColor(list, key) {
    for (var i = 0; i < list.length; i += 1) {
      if (list[i].key === key) return list[i];
    }
    return null;
  }

  function isLiveColor(item) {
    var st = (item && item.status) || 'live';
    return st === 'live' || st === 'available';
  }

  function shopProvider(data, item) {
    if (item && item.provider) return item.provider;
    return (data && data.provider) || 'Zazzle';
  }

  function ensureCtaSlot(wrap) {
    if (!wrap.classList.contains('sv-cta-slot')) {
      wrap.classList.add('sv-cta-slot');
    }
    var a = wrap.querySelector('[data-swatch-shop]') || wrap.querySelector('a[data-swatch-btn]');
    var statusEl = wrap.querySelector('[data-swatch-status]');
    if (!a) {
      a = document.createElement('a');
      a.className = 'wp-block-button__link wp-element-button';
      a.setAttribute('data-swatch-btn', '');
      a.setAttribute('data-swatch-shop', '');
      a.target = '_blank';
      a.rel = 'noreferrer noopener';
      wrap.insertBefore(a, wrap.firstChild);
    } else if (!a.getAttribute('data-swatch-shop')) {
      a.setAttribute('data-swatch-shop', '');
    }
    if (!statusEl) {
      statusEl = document.createElement('span');
      statusEl.className = 'wp-block-button__link wp-element-button sv-shop-status';
      statusEl.setAttribute('data-swatch-status', '');
      statusEl.setAttribute('aria-disabled', 'true');
      wrap.appendChild(statusEl);
    }
    return { a: a, statusEl: statusEl };
  }

  function setShopCta(card, item, data) {
    var wrap = card.querySelector('[data-swatch-cta]');
    if (!wrap) return;
    var parts = ensureCtaSlot(wrap);
    var a = parts.a;
    var statusEl = parts.statusEl;
    if (isLiveColor(item)) {
      wrap.classList.add('is-live');
      wrap.classList.remove('is-unavailable');
      if (item.url) {
        a.href = item.url;
      } else {
        a.removeAttribute('href');
      }
      a.textContent = 'Shop on ' + shopProvider(data, item);
      a.removeAttribute('aria-hidden');
      a.removeAttribute('tabindex');
      statusEl.setAttribute('aria-hidden', 'true');
      return;
    }
    wrap.classList.remove('is-live');
    wrap.classList.add('is-unavailable');
    a.removeAttribute('href');
    a.setAttribute('aria-hidden', 'true');
    a.setAttribute('tabindex', '-1');
    a.textContent = 'Shop on ' + shopProvider(data, item);
    statusEl.textContent = item.status === 'coming_soon' ? 'COMING SOON' : 'SOLD OUT';
    statusEl.removeAttribute('aria-hidden');
    statusEl.setAttribute('aria-disabled', 'true');
  }

  function apply(card, key) {
    var data = card._swatch;
    if (!data) return;
    var item = findColor(data.colors || [], key);
    if (!item) return;

    var img = card.querySelector('[data-swatch-img]');
    var lab = card.querySelector('[data-swatch-label]');
    var orderBy = card.querySelector('[data-swatch-orderby]');
    var zoom = card.querySelector('[data-swatch-zoom]');
    var dots = card.querySelectorAll('[data-color-key]');
    var alt = item.label ? (data.title + ' in ' + item.label) : data.title;

    if (img && item.img) {
      img.removeAttribute('srcset');
      img.removeAttribute('sizes');
      img.src = item.img;
      img.alt = alt;
      if (item.photoId) {
        img.className = 'wp-image-' + item.photoId;
      }
    }
    if (zoom && item.img) {
      zoom.href = item.img;
      zoom.setAttribute('aria-label', 'View larger photo of ' + alt);
    }
    if (lab) {
      lab.textContent = item.label;
    }
    setShopCta(card, item, data);
    if (orderBy) {
      if (item.orderByText) {
        orderBy.textContent = item.orderByText;
        orderBy.hidden = false;
      } else {
        orderBy.textContent = '';
        orderBy.hidden = true;
      }
    }
    for (var i = 0; i < dots.length; i += 1) {
      var d = dots[i];
      var on = d.getAttribute('data-color-key') === key;
      d.classList.toggle('is-on', on);
      d.setAttribute('aria-pressed', on ? 'true' : 'false');
    }
  }

  function initSwatches() {
    document.querySelectorAll('[data-swatch-card]').forEach(function (card) {
      if (card._swatch) return;
      var data = parseData(card);
      if (!data) return;
      card._swatch = data;
      card.addEventListener('click', function (e) {
        var t = e.target.closest ? e.target.closest('[data-color-key]') : null;
        if (!t) return;
        apply(card, t.getAttribute('data-color-key'));
      });
    });
  }

  /* Lightbox: same behavior as the Ideas/earrings vote photos. */
  var ov = null;
  var last = null;

  function closeLightbox() {
    if (!ov) return;
    if (ov.parentNode) ov.parentNode.removeChild(ov);
    ov = null;
    if (document.body) document.body.classList.remove('sv-lightbox-on');
    if (last) {
      last.focus();
      last = null;
    }
  }

  function openLightbox(src, from, alt) {
    closeLightbox();
    last = from;
    ov = document.createElement('div');
    ov.className = 'sv-lightbox';
    ov.setAttribute('role', 'dialog');
    ov.setAttribute('aria-modal', 'true');
    ov.setAttribute('aria-label', 'Close-up');
    var img = document.createElement('img');
    img.setAttribute('width', '1024');
    img.setAttribute('height', '1024');
    img.width = 1024;
    img.height = 1024;
    img.src = src;
    img.alt = alt || '';
    ov.appendChild(img);
    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'sv-lightbox-close';
    btn.setAttribute('aria-label', 'Close');
    btn.textContent = '\u00d7';
    ov.appendChild(btn);
    document.body.appendChild(ov);
    document.body.classList.add('sv-lightbox-on');
    btn.focus();
  }

  function initLightbox() {
    if (window.svCardZoomReady) return;
    window.svCardZoomReady = true;
    document.addEventListener('click', function (e) {
      var t = e.target;
      if (!t) return;
      var a = t.closest ? t.closest('a.sv-card-zoom') : null;
      if (a) {
        var href = a.getAttribute('href');
        if (!href) return;
        e.preventDefault();
        var im = a.querySelector('img');
        var alt = im ? (im.getAttribute('alt') || '') : '';
        openLightbox(href, a, alt);
        return;
      }
      if (ov) {
        e.preventDefault();
        closeLightbox();
      }
    });
    document.addEventListener('keydown', function (e) {
      if (!ov) return;
      if (e.key === 'Escape') closeLightbox();
    });
  }

  /* Collection filter bar (Sweatshirts & Hoodies master section) */
  function measureStickyOffset() {
    var offset = 0;
    var candidates = ['#wpadminbar', '.sv-header', '.sv-header-wrap', 'header.wp-block-template-part'];
    for (var i = 0; i < candidates.length; i += 1) {
      var el = document.querySelector(candidates[i]);
      if (!el) continue;
      var cs = window.getComputedStyle ? window.getComputedStyle(el) : null;
      if (!cs) continue;
      if (cs.display === 'none' || cs.visibility === 'hidden') continue;
      if (cs.position === 'fixed' || cs.position === 'sticky') {
        var r = el.getBoundingClientRect();
        if (r.height > 0 && r.top <= 4 && r.bottom > offset) {
          offset = Math.ceil(r.bottom);
        }
      }
    }
    return offset;
  }

  function readRequestedCollection(sectionKey, multiSection) {
    try {
      if (window.location && window.location.search) {
        var params = new URLSearchParams(window.location.search);
        var q = params.get('collection');
        if (q) return q.trim().toLowerCase();
      }
    } catch (e) {}
    if (window.location && window.location.hash) {
      var rawHash = window.location.hash.replace(/^#/, '').trim();
      if (!rawHash) return '';
      if (multiSection && rawHash.indexOf('=') !== -1) {
        try {
          var hp = new URLSearchParams(rawHash);
          var scoped = hp.get(sectionKey);
          return scoped ? scoped.trim().toLowerCase() : '';
        } catch (e) {}
      }
      return rawHash.toLowerCase();
    }
    return '';
  }

  function initCollectionFilter() {
    var allSections = Array.prototype.slice.call(document.querySelectorAll('[data-scv-collection-section]'));
    var multiSection = allSections.length > 1;
    allSections.forEach(function (section, secIndex) {
      var sectionKey = section.getAttribute('data-scv-category') || ('section-' + (secIndex + 1));
      if (section._scvFilterReady) return;
      section._scvFilterReady = true;

      var wrap = section.querySelector('[data-scv-filter-wrap]');
      var viewport = section.querySelector('[data-scv-filter-viewport]');
      var bar = section.querySelector('[data-scv-filter-bar]');
      var statusEl = section.querySelector('[data-scv-filter-status]');
      var heading = section.querySelector('.sv-master-heading');
      var list = section.querySelector('[data-scv-collections-list]');
      if (!wrap || !bar || !list) return;

      var pills = Array.prototype.slice.call(bar.querySelectorAll('[data-collection-pill]'));
      var groups = Array.prototype.slice.call(list.querySelectorAll('[data-collection-group]'));
      if (!pills.length || !groups.length) return;

      var stickyTop = 0;

      function syncStickyOffset() {
        stickyTop = measureStickyOffset();
        wrap.style.setProperty('--sv-sticky-top', stickyTop + 'px');
      }

      function updateScrollHints() {
        if (!viewport) return;
        var maxScroll = bar.scrollWidth - bar.clientWidth;
        if (maxScroll <= 2) {
          viewport.classList.remove('has-scroll-left', 'has-scroll-right');
          return;
        }
        viewport.classList.toggle('has-scroll-left', bar.scrollLeft > 4);
        viewport.classList.toggle('has-scroll-right', bar.scrollLeft < maxScroll - 4);
      }

      function updateStuckState() {
        if (window.innerWidth > 767) {
          wrap.classList.remove('is-stuck');
          return;
        }
        var r = wrap.getBoundingClientRect();
        var secR = section.getBoundingClientRect();
        var stuck = r.top <= stickyTop + 1 && secR.top < stickyTop && secR.bottom > stickyTop + r.height;
        wrap.classList.toggle('is-stuck', stuck);
      }

      function findPill(token) {
        if (!token) return null;
        var norm = String(token).trim().toLowerCase();
        var stripped = norm.indexOf('collection-') === 0 ? norm.slice('collection-'.length) : norm;
        for (var i = 0; i < pills.length; i += 1) {
          var p = pills[i];
          var slug = (p.getAttribute('data-collection-pill') || '').toLowerCase();
          var shortSlug = (p.getAttribute('data-collection-short') || '').toLowerCase();
          if (slug === norm || slug === stripped || (shortSlug && (shortSlug === norm || shortSlug === stripped))) {
            return p;
          }
        }
        return null;
      }

      function scrollPillIntoView(pill, instant) {
        if (!pill || bar.scrollWidth <= bar.clientWidth + 2) return;
        var target = pill.offsetLeft - (bar.clientWidth - pill.offsetWidth) / 2;
        var max = bar.scrollWidth - bar.clientWidth;
        if (target < 0) target = 0;
        if (target > max) target = max;
        try {
          bar.scrollTo({ left: target, behavior: instant ? 'auto' : 'smooth' });
        } catch (e) {
          bar.scrollLeft = target;
        }
      }

      function selectPill(pill, opts) {
        if (!pill) return;
        opts = opts || {};
        var targetSlug = pill.getAttribute('data-collection-pill') || 'all';
        var isAll = targetSlug === 'all';

        var wasStuck = false;
        var naturalWrapTop = 0;
        if (opts.preserveStickyScroll && window.innerWidth <= 767) {
          var wRect = wrap.getBoundingClientRect();
          if (wRect.top <= stickyTop + 2 && section.getBoundingClientRect().top < stickyTop) {
            wasStuck = true;
            if (heading) {
              var hRect = heading.getBoundingClientRect();
              var hMargin = parseFloat(window.getComputedStyle(heading).marginBottom) || 0;
              naturalWrapTop = hRect.bottom + window.scrollY + hMargin;
            } else {
              naturalWrapTop = section.getBoundingClientRect().top + window.scrollY;
            }
          }
        }

        for (var i = 0; i < pills.length; i += 1) {
          var p = pills[i];
          var on = p === pill;
          p.classList.toggle('is-active', on);
          p.setAttribute('aria-checked', on ? 'true' : 'false');
          p.setAttribute('tabindex', on ? '0' : '-1');
        }

        var visibleCount = 0;
        for (var j = 0; j < groups.length; j += 1) {
          var g = groups[j];
          var gSlug = g.getAttribute('data-collection-group') || '';
          var show = isAll || gSlug === targetSlug;
          g.hidden = !show;
          if (show) {
            visibleCount += parseInt(g.getAttribute('data-collection-count') || '0', 10);
          }
        }

        if (wasStuck) {
          var destY = Math.max(0, Math.round(naturalWrapTop - stickyTop));
          if (Math.abs(window.scrollY - destY) > 2) {
            window.scrollTo(0, destY);
          }
        }

        if (opts.scrollPill !== false) {
          scrollPillIntoView(pill, !!opts.instantPill);
        }

        if (statusEl && opts.announce) {
          var label = pill.getAttribute('data-collection-label') || pill.textContent.trim();
          statusEl.textContent = isAll
            ? ('Showing all collections, ' + visibleCount + ' products.')
            : ('Showing ' + label + ', ' + visibleCount + (visibleCount === 1 ? ' product.' : ' products.'));
        }

        if (opts.updateUrl && window.history && window.history.replaceState) {
          try {
            var u = new URL(window.location.href);
            var shortToken = isAll ? 'all' : (pill.getAttribute('data-collection-short') || targetSlug);
            if (multiSection) {
              if (u.searchParams.has('collection')) {
                u.searchParams.delete('collection');
              }
              var rawH = (u.hash || '').replace(/^#/, '');
              var hp = new URLSearchParams(rawH.indexOf('=') !== -1 ? rawH : '');
              if (isAll) {
                hp.set(sectionKey, 'all');
              } else {
                hp.set(sectionKey, shortToken);
              }
              u.hash = hp.toString();
            } else if (isAll) {
              if (u.searchParams.has('collection')) {
                u.searchParams.delete('collection');
              }
              if (u.hash && findPill(u.hash.slice(1))) {
                u.hash = '';
              }
            } else {
              if (u.searchParams.has('collection')) {
                u.searchParams.set('collection', shortToken);
              } else {
                u.hash = 'collection-' + shortToken;
              }
            }
            window.history.replaceState(null, '', u.toString());
          } catch (e) {}
        }

        updateStuckState();
        updateScrollHints();
      }

      bar.addEventListener('click', function (e) {
        var pill = e.target.closest ? e.target.closest('[data-collection-pill]') : null;
        if (!pill) return;
        selectPill(pill, { announce: true, updateUrl: true, preserveStickyScroll: true });
      });

      bar.addEventListener('keydown', function (e) {
        var current = e.target.closest ? e.target.closest('[data-collection-pill]') : null;
        if (!current) return;
        var idx = pills.indexOf(current);
        if (idx === -1) return;
        var nextIdx = -1;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
          nextIdx = (idx + 1) % pills.length;
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
          nextIdx = (idx - 1 + pills.length) % pills.length;
        } else if (e.key === 'Home') {
          nextIdx = 0;
        } else if (e.key === 'End') {
          nextIdx = pills.length - 1;
        } else if (e.key === ' ' || e.key === 'Enter') {
          e.preventDefault();
          selectPill(current, { announce: true, updateUrl: true, preserveStickyScroll: true });
          return;
        }
        if (nextIdx >= 0) {
          e.preventDefault();
          var nextPill = pills[nextIdx];
          nextPill.focus();
          selectPill(nextPill, { announce: true, updateUrl: true, preserveStickyScroll: true });
        }
      });

      bar.addEventListener('scroll', updateScrollHints, { passive: true });
      window.addEventListener('scroll', updateStuckState, { passive: true });
      window.addEventListener('resize', function () {
        syncStickyOffset();
        updateScrollHints();
        updateStuckState();
      }, { passive: true });
      window.addEventListener('hashchange', function () {
        var req = readRequestedCollection(sectionKey, multiSection);
        var match = req ? findPill(req) : (!multiSection || !window.location.hash ? pills[0] : null);
        if (match) {
          selectPill(match, { announce: true, updateUrl: false, preserveStickyScroll: false });
        }
      });

      syncStickyOffset();
      var initialReq = readRequestedCollection(sectionKey, multiSection);
      var initialPill = initialReq ? findPill(initialReq) : null;
      if (initialPill) {
        selectPill(initialPill, { announce: false, updateUrl: false, instantPill: true });
      } else {
        updateScrollHints();
        updateStuckState();
      }
    });
  }

  function init() {
    initSwatches();
    initLightbox();
    initCollectionFilter();
  }

  if (document.readyState !== 'loading') {
    init();
  } else {
    document.addEventListener('DOMContentLoaded', init);
  }
})();
