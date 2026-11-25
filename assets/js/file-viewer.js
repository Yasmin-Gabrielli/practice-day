/**
 * Leitor de documentos do PracticeDay.
 *
 * Cobre PDF (PDF.js), TXT/DOCX (ajustes de leitura) e imagens (zoom/rotação).
 * O leitor EPUB tem um script próprio (file-epub-reader.js) e reaproveita
 * as mesmas preferências visuais definidas aqui.
 */
(function () {
  'use strict';

  const cfg = window.PracticeDayFileViewer;
  if (!cfg) return;

  const shell = document.querySelector('[data-reader-shell]');
  const stage = document.querySelector('[data-viewer-stage]');
  if (!shell || !stage) return;

  const type = String(cfg.type || shell.dataset.readerType || 'unsupported');
  const prefsKey = 'practiceDay.reader.prefs.v1';
  const defaults = {
    theme: 'light',
    fontSize: 100,
    lineHeight: 1.7,
    width: 72,
    flow: 'paged',
  };

  const readPrefs = () => {
    try {
      const stored = JSON.parse(window.localStorage.getItem(prefsKey) || '{}');
      return { ...defaults, ...(stored && typeof stored === 'object' ? stored : {}) };
    } catch (_) {
      return { ...defaults };
    }
  };

  const prefs = readPrefs();
  if (!['light', 'sepia', 'dark'].includes(prefs.theme)) prefs.theme = defaults.theme;
  if (!['paged', 'scroll'].includes(prefs.flow)) prefs.flow = defaults.flow;
  prefs.fontSize = Math.min(180, Math.max(80, Number(prefs.fontSize) || defaults.fontSize));
  prefs.width = Math.min(95, Math.max(55, Number(prefs.width) || defaults.width));
  prefs.lineHeight = [1.45, 1.7, 2].includes(Number(prefs.lineHeight)) ? Number(prefs.lineHeight) : defaults.lineHeight;

  const persistPrefs = () => {
    try {
      window.localStorage.setItem(prefsKey, JSON.stringify(prefs));
    } catch (_) {
      /* armazenamento indisponível */
    }
  };

  const announcer = document.querySelector('[data-reader-announcer]');
  let announceTimer = 0;
  const announce = (message) => {
    if (!announcer || !message) return;
    announcer.textContent = '';
    window.clearTimeout(announceTimer);
    announceTimer = window.setTimeout(() => {
      announcer.textContent = message;
    }, 30);
  };

  // ---------------------------------------------------------------------
  // Aparência / preferências
  // ---------------------------------------------------------------------

  const applyPrefs = () => {
    shell.dataset.readerTheme = prefs.theme;
    shell.style.setProperty('--reader-font-scale', String(prefs.fontSize / 100));
    shell.style.setProperty('--reader-line-height', String(prefs.lineHeight));
    shell.style.setProperty('--reader-measure', prefs.width + 'ch');

    document.querySelectorAll('[data-reader-theme-option]').forEach((button) => {
      const active = button.dataset.readerThemeOption === prefs.theme;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });

    const fontRange = document.querySelector('[data-reader-font-range]');
    if (fontRange) fontRange.value = String(prefs.fontSize);

    const lineHeight = document.querySelector('[data-reader-line-height]');
    if (lineHeight) lineHeight.value = String(prefs.lineHeight);

    const widthRange = document.querySelector('[data-reader-width-range]');
    if (widthRange) widthRange.value = String(prefs.width);

    const flowSelect = document.querySelector('[data-reader-flow-select]');
    if (flowSelect) flowSelect.value = prefs.flow;
  };

  const setTheme = (theme) => {
    if (!['light', 'sepia', 'dark'].includes(theme)) return;
    prefs.theme = theme;
    applyPrefs();
    persistPrefs();
    if (theme === 'dark' || theme === 'sepia') {
      applyPdfTheme();
    } else {
      applyPdfTheme();
    }
  };

  const cycleTheme = () => {
    const order = ['light', 'sepia', 'dark'];
    const next = order[(order.indexOf(prefs.theme) + 1) % order.length];
    setTheme(next);
    announce('Tema de leitura: ' + ({ light: 'claro', sepia: 'sépia', dark: 'escuro' })[next] + '.');
  };

  document.querySelectorAll('[data-reader-theme-option]').forEach((button) => {
    button.addEventListener('click', () => {
      setTheme(button.dataset.readerThemeOption);
      announce('Tema de leitura atualizado.');
    });
  });

  document.querySelector('[data-reader-theme-toggle]')?.addEventListener('click', cycleTheme);

  document.querySelector('[data-reader-font-range]')?.addEventListener('input', (event) => {
    prefs.fontSize = Math.min(180, Math.max(80, Number(event.currentTarget.value) || 100));
    applyPrefs();
    persistPrefs();
  });

  document.querySelector('[data-reader-line-height]')?.addEventListener('change', (event) => {
    const value = Number(event.currentTarget.value);
    if ([1.45, 1.7, 2].includes(value)) {
      prefs.lineHeight = value;
      applyPrefs();
      persistPrefs();
    }
  });

  document.querySelector('[data-reader-width-range]')?.addEventListener('input', (event) => {
    prefs.width = Math.min(95, Math.max(55, Number(event.currentTarget.value) || 72));
    applyPrefs();
    persistPrefs();
  });

  // ---------------------------------------------------------------------
  // Painel lateral
  // ---------------------------------------------------------------------

  const panel = document.querySelector('[data-reader-panel]');
  const panelToggle = document.querySelector('[data-reader-panel-toggle]');

  const setPanel = (open) => {
    if (!panel || !panelToggle) return;
    panel.hidden = !open;
    panelToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    panelToggle.classList.toggle('is-active', open);
    if (open) {
      const activeTab = panel.querySelector('[data-reader-tab].is-active') || panel.querySelector('[data-reader-tab]');
      activateTab(activeTab);
    }
  };

  panelToggle?.addEventListener('click', () => {
    setPanel(panel ? panel.hidden : true);
  });

  function activateTab(tab) {
    if (!tab || !panel) return;
    panel.querySelectorAll('[data-reader-tab]').forEach((item) => {
      const selected = item === tab;
      item.classList.toggle('is-active', selected);
      item.setAttribute('aria-selected', selected ? 'true' : 'false');
      item.tabIndex = selected ? 0 : -1;
    });
    panel.querySelectorAll('[data-reader-section]').forEach((section) => {
      section.hidden = section.dataset.readerSection !== tab.dataset.readerTab;
    });
    const list = panel.querySelector('[data-reader-tab].is-active');
    if (list) list.focus({ preventScroll: true });
  }

  panel?.querySelectorAll('[data-reader-tab]').forEach((tab) => {
    tab.addEventListener('click', () => activateTab(tab));
    tab.addEventListener('keydown', (event) => {
      if (event.key !== 'ArrowRight' && event.key !== 'ArrowLeft') return;
      const tabs = Array.from(panel.querySelectorAll('[data-reader-tab]'));
      const index = tabs.indexOf(tab);
      const next = tabs[(index + (event.key === 'ArrowRight' ? 1 : tabs.length - 1)) % tabs.length];
      event.preventDefault();
      activateTab(next);
    });
  });

  // ---------------------------------------------------------------------
  // Tela cheia
  // ---------------------------------------------------------------------

  const fullscreenButton = document.querySelector('[data-reader-fullscreen]');

  const toggleFullscreen = async () => {
    try {
      if (document.fullscreenElement) {
        await document.exitFullscreen();
      } else if (shell.requestFullscreen) {
        await shell.requestFullscreen();
      }
    } catch (_) {
      announce('Não foi possível entrar em tela cheia neste navegador.');
    }
  };

  fullscreenButton?.addEventListener('click', toggleFullscreen);

  document.addEventListener('fullscreenchange', () => {
    const active = document.fullscreenElement === shell;
    shell.classList.toggle('is-fullscreen', active);
    if (fullscreenButton) {
      fullscreenButton.setAttribute('aria-label', active ? 'Sair da tela cheia' : 'Entrar em tela cheia');
      fullscreenButton.title = active ? 'Sair da tela cheia (F)' : 'Tela cheia (F)';
      const icon = fullscreenButton.querySelector('i');
      if (icon) icon.className = active ? 'bi bi-fullscreen-exit' : 'bi bi-arrows-fullscreen';
    }
    window.dispatchEvent(new Event('resize'));
  });

  // ---------------------------------------------------------------------
  // Diálogos
  // ---------------------------------------------------------------------

  const openDialog = (dialog) => {
    if (!dialog) return;
    if (typeof dialog.showModal === 'function') dialog.showModal();
    else dialog.setAttribute('open', 'open');
  };

  const closeDialog = (dialog) => {
    if (!dialog) return;
    if (typeof dialog.close === 'function') dialog.close();
    else dialog.removeAttribute('open');
  };

  const shortcutsDialog = document.getElementById('reader-shortcuts-dialog');
  const bookmarkDialog = document.getElementById('reader-bookmark-dialog');

  document.querySelector('[data-reader-shortcuts]')?.addEventListener('click', () => openDialog(shortcutsDialog));
  document.querySelector('[data-reader-settings]')?.addEventListener('click', () => setPanel(true));

  shortcutsDialog?.querySelector('[data-dialog-close]')?.addEventListener('click', () => closeDialog(shortcutsDialog));
  bookmarkDialog?.querySelector('[data-dialog-cancel]')?.addEventListener('click', () => closeDialog(bookmarkDialog));

  document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) closeDialog(dialog);
    });
  });

  // ---------------------------------------------------------------------
  // Progresso / API da biblioteca
  // ---------------------------------------------------------------------

  const progressBar = document.querySelector('[data-reader-progress-bar]');
  const progressRoot = document.querySelector('[data-reader-progress]');
  const progressLabel = document.querySelector('[data-reader-progress-label]');
  const statusLabel = document.querySelector('[data-reader-status]');
  const api = cfg.libraryApi || {};
  const sessionStart = Date.now();

  let progressTimer = 0;

  const updateProgressUi = (percent, statusText) => {
    const safe = Math.max(0, Math.min(100, percent));
    if (progressBar) progressBar.style.width = safe.toFixed(2) + '%';
    if (progressRoot) progressRoot.setAttribute('aria-valuenow', String(Math.round(safe)));
    if (progressLabel) progressLabel.textContent = safe.toFixed(1).replace('.', ',') + '%';
    if (statusLabel && typeof statusText === 'string') statusLabel.textContent = statusText;
  };

  const postForm = (url, fields) => {
    const body = new URLSearchParams({ _csrf_token: cfg.csrfToken || '', ...fields });
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body,
    }).then((response) => response.json()).catch(() => null);
  };

  const scheduleProgress = () => {
    if (!api.progresso) return;
    window.clearTimeout(progressTimer);
    progressTimer = window.setTimeout(() => {
      postForm(api.progresso, {
        ...progressFields(),
        tempo_leitura_segundos: '0',
        paginas_lidas_sessao: '1',
      });
    }, 1200);
  };

  const sendProgressBeacon = () => {
    if (!api.progresso) return;
    const seconds = Math.min(180, Math.round((Date.now() - sessionStart) / 1000));
    const body = new URLSearchParams({
      _csrf_token: cfg.csrfToken || '',
      ...progressFields(),
      tempo_leitura_segundos: String(seconds),
      paginas_lidas_sessao: '0',
    });
    if (navigator.sendBeacon) navigator.sendBeacon(api.progresso, body);
  };

  window.addEventListener('pagehide', sendProgressBeacon);

  window.setInterval(() => {
    if (!api.progresso || !state.page) return;
    postForm(api.progresso, {
      ...progressFields(),
      tempo_leitura_segundos: '20',
      paginas_lidas_sessao: '0',
    });
  }, 20000);

  const state = {
    page: Math.max(1, Number(cfg.library && cfg.library.pagina_atual) || Number(cfg.initialPage) || 1),
    total: Math.max(0, Number(cfg.totalPages) || 0),
    percent: 0,
  };

  // ---------------------------------------------------------------------
  // PDF
  // ---------------------------------------------------------------------

  const savedScrollOffset = Math.max(0, Number(cfg.library && cfg.library.posicao_scroll) || 0);
  let restoringPosition = true;

  const pdf = {
    doc: null,
    zoom: Math.min(4, Math.max(0.5, Number(cfg.library && cfg.library.zoom) || 1)),
    rotation: 0,
    flow: prefs.flow,
    rendered: new Set(),
    renderToken: 0,
    scrollObserver: null,
    lazyObserver: null,
    ready: false,
  };

  const pdfScroll = document.querySelector('[data-pdf-scroll]');
  const pdfPages = document.querySelector('[data-pdf-pages]');
  const pdfLoading = document.querySelector('[data-pdf-loading]');
  const pdfLoadingText = document.querySelector('[data-pdf-loading-text]');
  const pdfError = document.querySelector('[data-pdf-error]');
  const pageInput = document.querySelector('[data-pdf-page-input]');
  const totalLabel = document.querySelector('[data-pdf-total]');
  const flowButton = document.querySelector('[data-reader-flow-toggle]');
  const flowSelect = document.querySelector('[data-reader-flow-select]');

  function currentPageElement() {
    return pdfPages ? pdfPages.querySelector('[data-page="' + state.page + '"]') : null;
  }

  // Guardamos o deslocamento relativo à página atual, não o scrollTop absoluto:
  // assim a posição sobrevive a mudanças de altura entre páginas e zoom.
  function scrollOffsetForCurrentPage() {
    if (!pdfScroll) return savedScrollOffset;
    const el = currentPageElement();
    const base = el ? Math.max(0, Number(el.offsetTop) || 0) : 0;
    return Math.max(0, Math.round(Number(pdfScroll.scrollTop) - base));
  }

  function progressFields() {
    return {
      pagina_atual: String(state.page || 1),
      total_paginas: String(state.total || 0),
      progresso_porcentagem: String(Math.round((state.percent || 0) * 100) / 100),
      posicao_scroll: String(scrollOffsetForCurrentPage()),
      zoom: String(pdf.zoom || 1),
    };
  }

  pdfScroll?.addEventListener('scroll', () => {
    if (type !== 'pdf' || !pdf.ready || !api.progresso) return;
    scheduleProgress();
  }, { passive: true });

  const pdfCanvasFilter = () => {
    if (prefs.theme === 'sepia') return 'sepia(.28) saturate(.92) brightness(.98)';
    if (prefs.theme === 'dark') return 'invert(.9) hue-rotate(180deg) brightness(.92) contrast(.92)';
    return 'none';
  };

  function applyPdfTheme() {
    if (!pdfPages) return;
    // No modo claro deixamos o PDF original; nos demais aplicamos um filtro suave.
    const filter = pdfCanvasFilter();
    pdfPages.dataset.theme = prefs.theme;
    pdfPages.style.setProperty('--pdf-filter', filter);
    if (pdf.ready) {
      if (pdf.flow === 'scroll') rebuildScroll();
      else renderPaged();
    }
  }

  const setPdfFlow = (flow, options = {}) => {
    const next = flow === 'scroll' ? 'scroll' : 'paged';
    if (next === pdf.flow && options.force !== true) {
      updateFlowUi();
      return;
    }
    pdf.flow = next;
    prefs.flow = next;
    shell.dataset.flow = next;
    persistPrefs();
    updateFlowUi();
    if (!pdf.ready) return;

    if (next === 'scroll') {
      rebuildScroll();
      const target = Number(state.page) || 1;
      window.requestAnimationFrame(() => {
        const el = pdfPages?.querySelector('[data-page="' + target + '"]');
        if (el) pdfScroll.scrollTop = Math.max(0, el.offsetTop - 16);
      });
    } else {
      renderPaged();
    }
  };

  const updateFlowUi = () => {
    const scrolling = pdf.flow === 'scroll';
    if (flowButton) {
      flowButton.setAttribute('aria-pressed', scrolling ? 'true' : 'false');
      flowButton.title = scrolling ? 'Modo rolagem (clique para paginado)' : 'Modo paginado (clique para rolagem)';
    }
    if (flowSelect) flowSelect.value = pdf.flow;
    shell.dataset.flow = pdf.flow;
    if (pdfScroll) pdfScroll.classList.toggle('is-scroll', scrolling);
    if (pdfPages) pdfPages.classList.toggle('is-scroll', scrolling);
  };

  const updatePdfUi = (scroll) => {
    const total = pdf.doc ? pdf.doc.numPages : (Number(cfg.totalPages) || 0);
    state.total = total;
    state.page = Math.max(1, Math.min(total || 1, Number(state.page) || 1));
    state.percent = total > 0 ? (state.page / total) * 100 : 0;

    if (pageInput && document.activeElement !== pageInput) pageInput.value = String(state.page);
    if (totalLabel) totalLabel.textContent = total > 0 ? String(total) : '—';
    updateProgressUi(state.percent, total > 0 ? 'Página ' + state.page + ' de ' + total : 'Página ' + state.page);

    if (scroll && pdf.flow === 'paged' && !restoringPosition) {
      pdfScroll?.scrollTo({ top: 0, behavior: 'smooth' });
    }
  };

  const createCanvas = async (page, scale) => {
    const viewport = page.getViewport({ scale, rotation: pdf.rotation });
    const ratio = Math.min(2, window.devicePixelRatio || 1);
    const canvas = document.createElement('canvas');
    canvas.className = 'pdf-canvas';
    canvas.width = Math.max(1, Math.floor(viewport.width * ratio));
    canvas.height = Math.max(1, Math.floor(viewport.height * ratio));
    canvas.style.width = Math.floor(viewport.width) + 'px';
    canvas.style.height = Math.floor(viewport.height) + 'px';

    const context = canvas.getContext('2d', { alpha: false });
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, canvas.width, canvas.height);

    await page.render({
      canvasContext: context,
      viewport,
      transform: ratio !== 1 ? [ratio, 0, 0, ratio, 0, 0] : undefined,
    }).promise;

    return canvas;
  };

  const fitScale = (page) => {
    if (!pdfScroll) return 1;
    const base = page.getViewport({ scale: 1, rotation: pdf.rotation });
    const available = Math.max(240, pdfScroll.clientWidth - 48);
    return Math.max(0.1, available / base.width);
  };

  const renderPaged = async () => {
    if (!pdf.doc || !pdfPages) return;
    const token = ++pdf.renderToken;
    const pageNumber = Math.max(1, Math.min(pdf.doc.numPages, Number(state.page) || 1));

    try {
      const page = await pdf.doc.getPage(pageNumber);
      if (token !== pdf.renderToken) return;

      const scale = fitScale(page) * pdf.zoom;
      const canvas = await createCanvas(page, scale);
      if (token !== pdf.renderToken) return;

      const wrap = document.createElement('div');
      wrap.className = 'pdf-page';
      wrap.dataset.page = String(pageNumber);
      wrap.setAttribute('role', 'img');
      wrap.setAttribute('aria-label', 'Página ' + pageNumber + ' de ' + pdf.doc.numPages);
      wrap.appendChild(canvas);
      pdfPages.replaceChildren(wrap);
      updatePdfUi(true);
    } catch (error) {
      console.error('[PracticeDay] Falha ao renderizar página do PDF.', error);
      showPdfError('Não foi possível renderizar esta página. Tente baixar o arquivo.');
    }
  };

  const renderScrollPage = async (container) => {
    if (!pdf.doc || !container || container.dataset.rendered === '1') return;
    container.dataset.rendered = '1';
    const pageNumber = Number(container.dataset.page);
    try {
      const page = await pdf.doc.getPage(pageNumber);
      const canvas = await createCanvas(page, fitScale(page) * pdf.zoom);
      container.replaceChildren(canvas);
      container.style.minHeight = '0';
    } catch (error) {
      container.dataset.rendered = '0';
      container.textContent = 'Página ' + pageNumber + ' indisponível.';
    }
  };

  const setupScrollObservers = () => {
    if (!pdfScroll || !pdfPages) return;

    pdf.scrollObserver?.disconnect();
    pdf.lazyObserver?.disconnect();

    if (!('IntersectionObserver' in window)) {
      pdfPages.querySelectorAll('[data-page]').forEach(renderScrollPage);
      return;
    }

    pdf.lazyObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          renderScrollPage(entry.target);
          pdf.lazyObserver.unobserve(entry.target);
        }
      });
    }, { root: pdfScroll, rootMargin: '700px 0px' });

    pdf.scrollObserver = new IntersectionObserver((entries) => {
      let best = null;
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        if (!best || entry.intersectionRatio > best.intersectionRatio) best = entry;
      });
      if (best) {
        const pageNumber = Number(best.target.dataset.page);
        if (pageNumber && pageNumber !== state.page) {
          state.page = pageNumber;
          updatePdfUi(false);
          scheduleProgress();
        }
      }
    }, { root: pdfScroll, threshold: [0, 0.35, 0.6, 0.9] });

    pdfPages.querySelectorAll('[data-page]').forEach((item) => {
      pdf.lazyObserver.observe(item);
      pdf.scrollObserver.observe(item);
    });
  };

  const rebuildScroll = () => {
    if (!pdf.doc || !pdfPages) return;
    pdfPages.replaceChildren();
    const fragment = document.createDocumentFragment();
    for (let index = 1; index <= pdf.doc.numPages; index += 1) {
      const container = document.createElement('div');
      container.className = 'pdf-page pdf-page-placeholder';
      container.dataset.page = String(index);
      container.dataset.rendered = '0';
      container.setAttribute('role', 'img');
      container.setAttribute('aria-label', 'Página ' + index + ' de ' + pdf.doc.numPages);
      fragment.appendChild(container);
    }
    pdfPages.appendChild(fragment);
    setupScrollObservers();
    updatePdfUi(false);
  };

  const goToPage = (pageNumber, options = {}) => {
    if (!pdf.doc) return;
    const total = pdf.doc.numPages;
    const target = Math.max(1, Math.min(total, Number(pageNumber) || 1));

    state.page = target;
    state.percent = total > 0 ? (target / total) * 100 : 0;

    if (pdf.flow === 'scroll') {
      const el = pdfPages?.querySelector('[data-page="' + target + '"]');
      if (el) {
        pdfScroll.scrollTo({ top: Math.max(0, el.offsetTop - 16), behavior: options.instant ? 'auto' : 'smooth' });
      }
      updatePdfUi(false);
    } else {
      renderPaged();
    }

    if (options.announce !== false) {
      announce('Página ' + target + ' de ' + total + '.');
    }
    scheduleProgress();
  };

  const restorePdfScroll = () => {
    if (!pdfScroll) return;
    const el = currentPageElement();
    const base = el ? Math.max(0, Number(el.offsetTop) || 0) : 0;
    const top = Math.max(0, base + savedScrollOffset);
    window.requestAnimationFrame(() => {
      pdfScroll.scrollTop = top;
      // Uma segunda frame garante que o layout da página renderizada já foi aplicado.
      window.requestAnimationFrame(() => { pdfScroll.scrollTop = top; });
    });
  };

  const showPdfError = (message) => {
    if (!pdfError) return;
    pdfError.textContent = message;
    pdfError.classList.remove('d-none');
  };

  const setZoom = (delta) => {
    pdf.zoom = Math.max(0.5, Math.min(4, Math.round((pdf.zoom + delta) * 100) / 100));
    if (pdf.flow === 'scroll') {
      const current = state.page;
      rebuildScroll();
      window.requestAnimationFrame(() => {
        const el = pdfPages?.querySelector('[data-page="' + current + '"]');
        if (el) pdfScroll.scrollTop = Math.max(0, el.offsetTop - 16);
      });
    } else {
      renderPaged();
    }
    announce('Zoom ' + Math.round(pdf.zoom * 100) + '%.');
    scheduleProgress();
  };

  const initPdf = () => {
    const pdfjsLib = window.pdfjsLib;
    if (!pdfjsLib) {
      if (pdfLoading) pdfLoading.classList.add('d-none');
      showPdfError('O visualizador de PDF não pôde ser carregado. Verifique a conexão e tente novamente.');
      return;
    }

    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    updateFlowUi();

    pdfjsLib.getDocument({ url: cfg.serveUrl, withCredentials: true }).promise
      .then(async (document) => {
        pdf.doc = document;
        pdf.ready = true;
        const start = Math.max(1, Math.min(document.numPages, Number(state.page) || 1));
        state.page = start;

        if (pageInput) pageInput.max = String(document.numPages);
        state.total = document.numPages;
        state.percent = (start / document.numPages) * 100;
        updatePdfUi(false);

        document.querySelectorAll('[data-reader-prev]').forEach((button) => { button.disabled = false; });
        document.querySelectorAll('[data-reader-next]').forEach((button) => { button.disabled = false; });

        if (pdf.flow === 'scroll') {
          rebuildScroll();
          const startPage = pdfPages ? pdfPages.querySelector('[data-page="' + start + '"]') : null;
          if (startPage) await renderScrollPage(startPage);
        } else {
          await renderPaged();
        }
        restorePdfScroll();
        window.requestAnimationFrame(() => { restoringPosition = false; });

        if (pdfLoading) pdfLoading.classList.add('d-none');
        if (pdfError) pdfError.classList.add('d-none');
        if (pdfLoadingText) pdfLoadingText.textContent = 'PDF pronto.';
      })
      .catch((error) => {
        console.error('[PracticeDay] Falha ao abrir PDF.', error);
        if (pdfLoading) pdfLoading.classList.add('d-none');
        showPdfError('Erro ao carregar o PDF. Use o botão de download para abrir o arquivo localmente.');
      });
  };

  document.querySelector('[data-pdf-zoom-in]')?.addEventListener('click', () => setZoom(0.2));
  document.querySelector('[data-pdf-zoom-out]')?.addEventListener('click', () => setZoom(-0.2));
  document.querySelector('[data-pdf-fit]')?.addEventListener('click', () => {
    pdf.zoom = 1;
    if (pdf.flow === 'scroll') rebuildScroll();
    else renderPaged();
    announce('Página ajustada à largura.');
  });
  document.querySelector('[data-pdf-rotate]')?.addEventListener('click', () => {
    pdf.rotation = (pdf.rotation + 90) % 360;
    if (pdf.flow === 'scroll') rebuildScroll();
    else renderPaged();
    announce('Página girada em 90 graus.');
  });
  document.querySelector('[data-pdf-print]')?.addEventListener('click', () => {
    const popup = window.open(cfg.serveUrl, '_blank', 'noopener');
    if (!popup) {
      announce('Permita janelas pop-up para imprimir o documento.');
      return;
    }
    popup.addEventListener('load', () => {
      try { popup.print(); } catch (_) { /* ignora */ }
    });
  });

  pageInput?.addEventListener('change', () => {
    goToPage(Number(pageInput.value) || 1);
  });
  pageInput?.addEventListener('keydown', (event) => {
    if (event.key !== 'Enter') return;
    event.preventDefault();
    goToPage(Number(pageInput.value) || 1);
    pageInput.blur();
  });

  flowButton?.addEventListener('click', () => setPdfFlow(pdf.flow === 'scroll' ? 'paged' : 'scroll'));
  flowSelect?.addEventListener('change', () => setPdfFlow(flowSelect.value));

  // ---------------------------------------------------------------------
  // Marcadores (PDF)
  // ---------------------------------------------------------------------

  let bookmarks = Array.isArray(cfg.bookmarks) ? cfg.bookmarks.slice() : [];
  const bookmarkList = document.querySelector('[data-reader-bookmarks-list]');
  const bookmarkEmpty = document.querySelector('[data-reader-bookmarks-empty]');
  const bookmarkForm = document.querySelector('[data-reader-bookmark-form]');
  const bookmarkTitle = document.getElementById('reader-bookmark-title');

  const renderBookmarks = () => {
    if (!bookmarkList) return;
    bookmarkList.replaceChildren();

    bookmarks.forEach((bookmark) => {
      const item = document.createElement('li');
      item.className = 'reader-panel-list-item';

      const jump = document.createElement('button');
      jump.type = 'button';
      jump.className = 'reader-bookmark-jump';
      jump.innerHTML = '<strong></strong><span></span>';
      jump.querySelector('strong').textContent = bookmark.titulo || 'Marcador';
      jump.querySelector('span').textContent = 'Página ' + (Number(bookmark.numero_pagina) || 1);
      jump.addEventListener('click', () => goToPage(Number(bookmark.numero_pagina) || 1));

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'reader-icon-button reader-icon-danger';
      remove.setAttribute('aria-label', 'Excluir marcador ' + (bookmark.titulo || ''));
      remove.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i>';
      remove.addEventListener('click', async () => {
        if (!api.marcadores) return;
        const data = await postForm(api.marcadores, { action: 'delete', id: String(bookmark.id) });
        if (data && Array.isArray(data.marcadores)) {
          bookmarks = data.marcadores;
          renderBookmarks();
          announce('Marcador excluído.');
        }
      });

      item.append(jump, remove);
      bookmarkList.appendChild(item);
    });

    if (bookmarkEmpty) bookmarkEmpty.hidden = bookmarks.length > 0;
    if (bookmarkList) bookmarkList.hidden = bookmarks.length === 0;
  };

  document.querySelector('[data-reader-bookmark]')?.addEventListener('click', () => {
    if (!api.marcadores) {
      announce('Marcadores exigem um registro da biblioteca para este arquivo.');
      return;
    }
    if (!bookmarkForm || !bookmarkDialog) return;
    if (bookmarkTitle) {
      bookmarkTitle.value = 'Página ' + state.page;
    }
    openDialog(bookmarkDialog);
    window.setTimeout(() => bookmarkTitle?.select(), 40);
  });

  bookmarkForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!api.marcadores) return;
    const title = (bookmarkTitle?.value || '').trim() || 'Página ' + state.page;
    const data = await postForm(api.marcadores, {
      numero_pagina: String(state.page),
      titulo: title.slice(0, 150),
    });
    if (data && Array.isArray(data.marcadores)) {
      bookmarks = data.marcadores;
      renderBookmarks();
      announce('Marcador criado.');
    }
    closeDialog(bookmarkDialog);
  });

  // ---------------------------------------------------------------------
  // Imagem
  // ---------------------------------------------------------------------

  const image = document.querySelector('[data-image-view]');
  const imageWrap = document.querySelector('[data-image-wrap]');

  if (type === 'image' && image && imageWrap) {
    let scale = 1;
    let rotation = 0;
    let naturalWidth = 0;
    let naturalHeight = 0;

    const fitImage = () => {
      if (!naturalWidth) return 1;
      return Math.min(1, Math.max(0.15, (imageWrap.clientWidth - 40) / naturalWidth));
    };

    const applyImage = () => {
      if (!naturalWidth) return;
      image.style.width = Math.round(naturalWidth * scale) + 'px';
      image.style.height = Math.round(naturalHeight * scale) + 'px';
      image.style.transform = 'rotate(' + rotation + 'deg)';
    };

    const setup = () => {
      naturalWidth = image.naturalWidth || image.clientWidth || 1;
      naturalHeight = image.naturalHeight || image.clientHeight || 1;
      scale = fitImage();
      applyImage();
      updateProgressUi(100, 'Imagem pronta para leitura');
    };

    if (image.complete) setup();
    else image.addEventListener('load', setup, { once: true });

    document.querySelector('[data-img-zoom-in]')?.addEventListener('click', () => {
      scale = Math.min(4, scale + 0.25);
      applyImage();
      announce('Zoom ' + Math.round(scale * 100) + '%.');
    });
    document.querySelector('[data-img-zoom-out]')?.addEventListener('click', () => {
      scale = Math.max(0.2, scale - 0.25);
      applyImage();
      announce('Zoom ' + Math.round(scale * 100) + '%.');
    });
    document.querySelector('[data-img-zoom-reset]')?.addEventListener('click', () => {
      scale = fitImage();
      rotation = 0;
      applyImage();
      announce('Imagem restaurada.');
    });
    document.querySelector('[data-img-rotate]')?.addEventListener('click', () => {
      rotation = (rotation + 90) % 360;
      applyImage();
      announce('Imagem girada em 90 graus.');
    });

    let dragging = false;
    let startX = 0;
    let startY = 0;
    let startLeft = 0;
    let startTop = 0;

    imageWrap.addEventListener('pointerdown', (event) => {
      if (event.pointerType === 'mouse' && event.button !== 0) return;
      dragging = true;
      startX = event.clientX;
      startY = event.clientY;
      startLeft = imageWrap.scrollLeft;
      startTop = imageWrap.scrollTop;
      imageWrap.classList.add('is-dragging');
      try { imageWrap.setPointerCapture(event.pointerId); } catch (_) { /* ignora */ }
    });
    imageWrap.addEventListener('pointermove', (event) => {
      if (!dragging) return;
      imageWrap.scrollLeft = startLeft - (event.clientX - startX);
      imageWrap.scrollTop = startTop - (event.clientY - startY);
    });
    const stopDrag = () => {
      dragging = false;
      imageWrap.classList.remove('is-dragging');
    };
    imageWrap.addEventListener('pointerup', stopDrag);
    imageWrap.addEventListener('pointercancel', stopDrag);
    imageWrap.addEventListener('wheel', (event) => {
      if (!event.ctrlKey) return;
      event.preventDefault();
      scale = Math.max(0.2, Math.min(4, scale + (event.deltaY < 0 ? 0.1 : -0.1)));
      applyImage();
    }, { passive: false });

    window.addEventListener('resize', () => {
      if (scale <= fitImage() + 0.001) {
        scale = fitImage();
        applyImage();
      }
    });
  }

  // ---------------------------------------------------------------------
  // Texto (TXT/DOCX)
  // ---------------------------------------------------------------------

  if (type === 'txt' || type === 'docx') {
    updateProgressUi(0, 'Documento pronto para leitura');
  }

  // ---------------------------------------------------------------------
  // Navegação global, atalhos e gestos
  // ---------------------------------------------------------------------

  const needsPdf = () => type === 'pdf' && pdf.doc;

  const navigate = (direction) => {
    if (!needsPdf()) return;
    goToPage(state.page + direction);
  };

  document.querySelectorAll('[data-reader-prev]').forEach((button) => {
    button.addEventListener('click', () => navigate(-1));
  });
  document.querySelectorAll('[data-reader-next]').forEach((button) => {
    button.addEventListener('click', () => navigate(1));
  });

  // Swipe horizontal: no modo paginado troca de página; na rolagem o gesto
  // vertical continua sendo do navegador.
  let swipeStartX = 0;
  let swipeStartY = 0;
  let swipeTracking = false;

  stage.addEventListener('touchstart', (event) => {
    if (event.touches.length !== 1) return;
    swipeStartX = event.touches[0].clientX;
    swipeStartY = event.touches[0].clientY;
    swipeTracking = true;
  }, { passive: true });

  stage.addEventListener('touchend', (event) => {
    if (!swipeTracking) return;
    swipeTracking = false;
    const touch = event.changedTouches[0];
    if (!touch) return;
    const dx = touch.clientX - swipeStartX;
    const dy = touch.clientY - swipeStartY;

    if (Math.abs(dx) < 56 || Math.abs(dx) < Math.abs(dy) * 1.4) return;
    if (type !== 'pdf' || pdf.flow !== 'paged') return;

    navigate(dx < 0 ? 1 : -1);
  }, { passive: true });

  document.addEventListener('keydown', (event) => {
    const target = event.target;
    if (target instanceof HTMLElement) {
      const tag = target.tagName.toLowerCase();
      if (['input', 'select', 'textarea', 'button'].includes(tag) || target.isContentEditable) {
        if (event.key !== 'Escape') return;
      }
    }
    if (event.ctrlKey || event.metaKey || event.altKey) return;

    const key = event.key;

    if (key === 'Escape') {
      if (document.fullscreenElement === shell) {
        document.exitFullscreen?.();
        return;
      }
      if (shell.classList.contains('is-fullscreen')) {
        shell.classList.remove('is-fullscreen');
        return;
      }
      document.querySelectorAll('dialog[open]').forEach((dialog) => closeDialog(dialog));
      if (panel && !panel.hidden) {
        setPanel(false);
        panelToggle?.focus({ preventScroll: true });
      }
      return;
    }

    if (document.querySelector('dialog[open]')) return;

    switch (key) {
      case 'ArrowLeft':
        if (type === 'pdf') {
          event.preventDefault();
          navigate(-1);
        } else if (type === 'image' && imageWrap) {
          event.preventDefault();
          imageWrap.scrollBy({ left: -160, behavior: 'smooth' });
        }
        break;
      case 'ArrowRight':
        if (type === 'pdf') {
          event.preventDefault();
          navigate(1);
        } else if (type === 'image' && imageWrap) {
          event.preventDefault();
          imageWrap.scrollBy({ left: 160, behavior: 'smooth' });
        }
        break;
      case 'ArrowUp':
      case 'ArrowDown':
        if (type === 'image' && imageWrap) {
          event.preventDefault();
          imageWrap.scrollBy({ top: key === 'ArrowUp' ? -160 : 160, behavior: 'smooth' });
        }
        break;
      case 'PageUp':
        if (type === 'pdf') { event.preventDefault(); navigate(-1); }
        break;
      case 'PageDown':
        if (type === 'pdf') { event.preventDefault(); navigate(1); }
        break;
      case 'Home':
        if (type === 'pdf') { event.preventDefault(); goToPage(1); }
        break;
      case 'End':
        if (type === 'pdf' && pdf.doc) { event.preventDefault(); goToPage(pdf.doc.numPages); }
        break;
      case '+':
      case '=':
        if (type === 'pdf') { event.preventDefault(); setZoom(0.2); }
        else if (type === 'image') { event.preventDefault(); document.querySelector('[data-img-zoom-in]')?.click(); }
        break;
      case '-':
      case '_':
        if (type === 'pdf') { event.preventDefault(); setZoom(-0.2); }
        else if (type === 'image') { event.preventDefault(); document.querySelector('[data-img-zoom-out]')?.click(); }
        break;
      case 'f':
      case 'F':
        event.preventDefault();
        toggleFullscreen();
        break;
      case 't':
      case 'T':
        event.preventDefault();
        cycleTheme();
        break;
      case '?':
        event.preventDefault();
        openDialog(shortcutsDialog);
        break;
      case 'r':
      case 'R':
        if (type === 'pdf') {
          event.preventDefault();
          document.querySelector('[data-pdf-rotate]')?.click();
        } else if (type === 'image') {
          event.preventDefault();
          document.querySelector('[data-img-rotate]')?.click();
        }
        break;
      default:
        break;
    }
  });

  // ---------------------------------------------------------------------
  // Inicialização
  // ---------------------------------------------------------------------

  applyPrefs();

  if (type === 'pdf') {
    shell.dataset.flow = pdf.flow;
    updateFlowUi();
    applyPdfTheme();
    renderBookmarks();
    initPdf();
  } else if (type === 'image') {
    updateProgressUi(100, 'Imagem pronta para leitura');
  } else if (type === 'txt' || type === 'docx') {
    updateProgressUi(0, 'Documento pronto para leitura');
  }

  // Em telas grandes o painel começa fechado; em telas pequenas ele vira
  // uma sobreposição e só abre por ação do usuário.
  if (panel) setPanel(false);
})();
