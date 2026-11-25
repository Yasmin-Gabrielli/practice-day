/**
 * Leitor EPUB do PracticeDay.
 *
 * Usa epub.js para renderizar o livro e mantém a mesma linguagem visual e as
 * mesmas preferências do leitor de PDF/TXT (file-viewer.js).
 */
(function () {
  'use strict';

  const cfg = window.PracticeDayEpubReader;
  if (!cfg) return;

  const shell = document.querySelector('[data-reader-shell]');
  const stage = document.querySelector('[data-viewer-stage]');
  const host = document.getElementById('epub-viewer');
  if (!shell || !stage || !host) return;

  if (typeof window.ePub === 'undefined' || typeof window.JSZip === 'undefined') {
    const text = document.querySelector('.epub-loading-text');
    if (text) text.textContent = 'Não foi possível carregar o leitor EPUB. Verifique a conexão e tente novamente.';
    return;
  }

  const prefsKey = 'practiceDay.reader.prefs.v1';
  const defaults = { theme: 'light', fontSize: 100, lineHeight: 1.7, width: 72, flow: 'paged' };

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
    announceTimer = window.setTimeout(() => { announcer.textContent = message; }, 30);
  };

  const progressBar = document.querySelector('[data-reader-progress-bar]');
  const progressRoot = document.querySelector('[data-reader-progress]');
  const progressLabel = document.querySelector('[data-reader-progress-label]');
  const statusLabel = document.querySelector('[data-reader-status]');
  const locationLabel = document.querySelector('[data-epub-location]');
  const loading = document.querySelector('[data-epub-loading]');
  const loadingText = document.querySelector('.epub-loading-text');

  const state = {
    book: null,
    rendition: null,
    locationsReady: false,
    totalLocations: 0,
    percent: Math.max(0, Math.min(100, Number(cfg.initial?.progresso?.progresso_porcentagem) || 0)),
    cfi: cfg.initialCfi || '',
    chapter: '',
    lastSelection: null,
    bookmarks: Array.isArray(cfg.initial?.marcadores) ? cfg.initial.marcadores.slice() : [],
    highlights: Array.isArray(cfg.initial?.destaques) ? cfg.initial.destaques.slice() : [],
    notes: Array.isArray(cfg.initial?.anotacoes) ? cfg.initial.anotacoes.slice() : [],
    appliedHighlights: new Set(),
    sessionStart: Date.now(),
    saveTimer: 0,
  };

  const api = cfg.api || {};

  // ---------------------------------------------------------------------
  // Aparência
  // ---------------------------------------------------------------------

  const themeTokens = {
    light: { background: '#ffffff', color: '#1f2937', link: '#1d4ed8', selection: 'rgba(37, 99, 235, .22)' },
    sepia: { background: '#fbf3e3', color: '#3d3223', link: '#92400e', selection: 'rgba(180, 83, 9, .24)' },
    dark: { background: '#141b24', color: '#e6ebf3', link: '#93c5fd', selection: 'rgba(147, 197, 253, .3)' },
  };

  const applyHostWidth = () => {
    const maxWidth = Math.round((prefs.width / 72) * 760);
    host.style.maxWidth = maxWidth + 'px';
    host.style.width = '100%';
    host.style.margin = '0 auto';
    host.style.alignSelf = 'center';
  };

  const applyPrefs = () => {
    shell.dataset.readerTheme = prefs.theme;
    shell.dataset.flow = prefs.flow;
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

    applyHostWidth();
    applyBookTheme();
  };

  const applyBookTheme = () => {
    if (!state.rendition) return;
    const tokens = themeTokens[prefs.theme] || themeTokens.light;
    try {
      state.rendition.themes.override('body', {
        'background': tokens.background + ' !important',
        'color': tokens.color + ' !important',
        'line-height': String(prefs.lineHeight) + ' !important',
        'padding': '1.1rem 1.4rem 3rem',
      });
      state.rendition.themes.override('a', { 'color': tokens.link + ' !important' });
      state.rendition.themes.override('p, li, span, div', { 'color': tokens.color + ' !important' });
      state.rendition.themes.override('::selection', { 'background': tokens.selection });
      state.rendition.themes.fontSize(prefs.fontSize + '%');
    } catch (error) {
      console.info('[PracticeDay] Não foi possível aplicar o tema ao EPUB.', error);
    }
  };

  const setTheme = (theme) => {
    if (!['light', 'sepia', 'dark'].includes(theme)) return;
    prefs.theme = theme;
    applyPrefs();
    persistPrefs();
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
  // Painel, abas e diálogos
  // ---------------------------------------------------------------------

  const panel = document.querySelector('[data-reader-panel]');
  const panelToggle = document.querySelector('[data-reader-panel-toggle]');

  const activateTab = (tab) => {
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
  };

  const setPanel = (open) => {
    if (!panel || !panelToggle) return;
    panel.hidden = !open;
    panelToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    panelToggle.classList.toggle('is-active', open);
    if (open) {
      activateTab(panel.querySelector('[data-reader-tab].is-active') || panel.querySelector('[data-reader-tab]'));
    }
  };

  panelToggle?.addEventListener('click', () => setPanel(panel ? panel.hidden : true));

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

  document.querySelector('[data-reader-settings]')?.addEventListener('click', () => setPanel(true));
  document.querySelector('[data-epub-toc-toggle]')?.addEventListener('click', () => {
    setPanel(true);
    const tocTab = panel?.querySelector('[data-reader-tab="toc"]');
    if (tocTab) activateTab(tocTab);
  });

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

  const bookmarkDialog = document.getElementById('reader-bookmark-dialog');
  const bookmarkForm = document.querySelector('[data-reader-bookmark-form]');
  const bookmarkTitle = document.getElementById('reader-bookmark-title');
  const noteDialog = document.getElementById('epub-note-dialog');
  const noteForm = document.querySelector('[data-epub-note-form]');
  const noteQuote = document.querySelector('[data-epub-note-quote]');
  const noteText = document.getElementById('epub-note-text');
  const shortcutsDialog = document.getElementById('reader-shortcuts-dialog');

  document.querySelector('[data-reader-shortcuts]')?.addEventListener('click', () => openDialog(shortcutsDialog));
  shortcutsDialog?.querySelector('[data-dialog-close]')?.addEventListener('click', () => closeDialog(shortcutsDialog));
  bookmarkDialog?.querySelector('[data-dialog-cancel]')?.addEventListener('click', () => closeDialog(bookmarkDialog));
  noteDialog?.querySelector('[data-dialog-cancel]')?.addEventListener('click', () => closeDialog(noteDialog));

  document.querySelectorAll('dialog').forEach((dialog) => {
    dialog.addEventListener('click', (event) => {
      if (event.target === dialog) closeDialog(dialog);
    });
  });

  // ---------------------------------------------------------------------
  // API
  // ---------------------------------------------------------------------

  const postForm = (url, fields) => {
    if (!url) return Promise.resolve(null);
    const body = new URLSearchParams({ _csrf_token: cfg.csrfToken || '', ...fields });
    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body,
    }).then((response) => response.json()).catch(() => null);
  };

  const percentFromCfi = (cfi) => {
    if (!cfi || !state.book || !state.locationsReady) return state.percent;
    try {
      const value = state.book.locations.percentageFromCfi(cfi);
      if (typeof value === 'number' && !Number.isNaN(value)) return Math.max(0, Math.min(100, value * 100));
    } catch (_) { /* ignora */ }
    return state.percent;
  };

  const updateUi = (percent, chapter) => {
    state.percent = Math.max(0, Math.min(100, percent || 0));
    if (chapter) state.chapter = chapter;

    if (progressBar) progressBar.style.width = state.percent.toFixed(2) + '%';
    if (progressRoot) progressRoot.setAttribute('aria-valuenow', String(Math.round(state.percent)));
    if (progressLabel) progressLabel.textContent = state.percent.toFixed(1).replace('.', ',') + '%';

    const readable = state.chapter || 'Leitura em andamento';
    if (statusLabel) statusLabel.textContent = readable + ' · ' + state.percent.toFixed(1).replace('.', ',') + '%';
    if (locationLabel) locationLabel.textContent = readable;
  };

  const saveProgress = (extra = {}) => {
    if (!api.progresso || !state.rendition) return;
    const location = state.rendition.currentLocation();
    const cfi = location?.start?.cfi || state.cfi;
    const percent = percentFromCfi(cfi);
    const page = Math.max(1, Math.round(percent));

    postForm(api.progresso, {
      pagina_atual: String(page),
      progresso_porcentagem: String(Math.round(percent * 10) / 10),
      cfi: cfi || '',
      total_paginas: String(state.totalLocations || 0),
      tempo_leitura_segundos: String(extra.tempo || 0),
      paginas_lidas_sessao: String(extra.paginas || 0),
    });
  };

  const scheduleSave = () => {
    if (!api.progresso) return;
    window.clearTimeout(state.saveTimer);
    state.saveTimer = window.setTimeout(() => saveProgress({ paginas: 1 }), 1400);
  };

  const beaconProgress = () => {
    if (!api.progresso || !state.book) return;
    const seconds = Math.min(180, Math.round((Date.now() - state.sessionStart) / 1000));
    let cfi = state.cfi || '';
    try {
      cfi = state.rendition?.currentLocation?.()?.start?.cfi || cfi;
    } catch (_) { /* usa a última posição conhecida */ }
    const body = new URLSearchParams({
      _csrf_token: cfg.csrfToken || '',
      pagina_atual: String(Math.max(1, Math.round(state.percent))),
      progresso_porcentagem: String(Math.round(state.percent * 10) / 10),
      cfi,
      tempo_leitura_segundos: String(seconds),
      paginas_lidas_sessao: '0',
    });
    if (navigator.sendBeacon) navigator.sendBeacon(api.progresso, body);
  };

  window.addEventListener('pagehide', beaconProgress);

  window.setInterval(() => {
    if (!state.book) return;
    saveProgress({ tempo: 20 });
  }, 20000);

  // ---------------------------------------------------------------------
  // Sumário e navegação
  // ---------------------------------------------------------------------

  const tocList = document.querySelector('[data-epub-toc]');
  const tocEmpty = document.querySelector('[data-epub-toc-empty]');
  let tocItems = [];

  const flattenToc = (items, depth = 0) => {
    const result = [];
    (items || []).forEach((item) => {
      result.push({ href: item.href, label: (item.label || '').trim(), depth });
      if (item.subitems && item.subitems.length) {
        result.push(...flattenToc(item.subitems, depth + 1));
      }
    });
    return result;
  };

  const renderToc = (toc) => {
    tocItems = flattenToc(toc || []);
    if (!tocList) return;
    tocList.replaceChildren();

    tocItems.forEach((item) => {
      const listItem = document.createElement('li');
      const button = document.createElement('button');
      button.type = 'button';
      button.textContent = item.label || 'Seção';
      button.className = 'epub-toc-depth-' + Math.min(2, item.depth);
      button.addEventListener('click', () => {
        try {
          state.rendition?.display(item.href);
        } catch (_) { /* ignora */ }
        if (window.innerWidth < 992) setPanel(false);
      });
      listItem.appendChild(button);
      tocList.appendChild(listItem);
    });

    if (tocEmpty) tocEmpty.hidden = tocItems.length > 0;
    tocList.hidden = tocItems.length === 0;
  };

  const highlightCurrentToc = (href) => {
    if (!tocList) return;
    const normalized = String(href || '').split('#')[0];
    tocList.querySelectorAll('button').forEach((button) => {
      button.classList.remove('is-current');
    });
    const match = tocItems.find((item) => String(item.href || '').split('#')[0] === normalized);
    if (!match) return;
    const buttons = Array.from(tocList.querySelectorAll('button'));
    const index = tocItems.indexOf(match);
    if (buttons[index]) buttons[index].classList.add('is-current');
  };

  const chapterFor = (href) => {
    const normalized = String(href || '').split('#')[0];
    let found = '';
    tocItems.forEach((item) => {
      if (!found && String(item.href || '').split('#')[0] === normalized) found = item.label;
    });
    return found;
  };

  const goPrev = () => {
    try { state.rendition?.prev(); } catch (_) { /* ignora */ }
  };

  const goNext = () => {
    try { state.rendition?.next(); } catch (_) { /* ignora */ }
  };

  const goToStart = () => {
    try {
      if (state.locationsReady) {
        const cfi = state.book.locations.cfiFromPercentage(0);
        if (cfi) state.rendition.display(cfi);
      } else {
        state.rendition.display();
      }
    } catch (_) { /* ignora */ }
  };

  const goToEnd = () => {
    try {
      if (state.locationsReady) {
        const cfi = state.book.locations.cfiFromPercentage(0.999);
        if (cfi) state.rendition.display(cfi);
      }
    } catch (_) { /* ignora */ }
  };

  document.querySelectorAll('[data-reader-prev]').forEach((button) => button.addEventListener('click', goPrev));
  document.querySelectorAll('[data-reader-next]').forEach((button) => button.addEventListener('click', goNext));

  // ---------------------------------------------------------------------
  // Fluxo (paginado / rolagem)
  // ---------------------------------------------------------------------

  const flowButton = document.querySelector('[data-reader-flow-toggle]');
  const flowSelect = document.querySelector('[data-reader-flow-select]');

  const updateFlowUi = () => {
    const scrolling = prefs.flow === 'scroll';
    shell.dataset.flow = prefs.flow;
    if (flowButton) {
      flowButton.setAttribute('aria-pressed', scrolling ? 'true' : 'false');
      flowButton.title = scrolling ? 'Modo rolagem (clique para paginado)' : 'Modo paginado (clique para rolagem)';
    }
    if (flowSelect) flowSelect.value = prefs.flow;
  };

  const applyFlow = () => {
    updateFlowUi();
    if (!state.rendition) return;
    try {
      state.rendition.flow(prefs.flow === 'scroll' ? 'scrolled' : 'paginated');
    } catch (error) {
      console.info('[PracticeDay] Não foi possível alternar o modo de leitura.', error);
    }
  };

  flowButton?.addEventListener('click', () => {
    prefs.flow = prefs.flow === 'scroll' ? 'paged' : 'scroll';
    persistPrefs();
    applyFlow();
    announce(prefs.flow === 'scroll' ? 'Modo de rolagem contínua.' : 'Modo página a página.');
  });

  flowSelect?.addEventListener('change', () => {
    prefs.flow = flowSelect.value === 'scroll' ? 'scroll' : 'paged';
    persistPrefs();
    applyFlow();
  });

  // ---------------------------------------------------------------------
  // Marcadores, destaques e anotações
  // ---------------------------------------------------------------------

  const bookmarkList = document.querySelector('[data-epub-bookmarks-list]');
  const bookmarkEmpty = document.querySelector('[data-epub-bookmarks-empty]');
  const highlightList = document.querySelector('[data-epub-highlights-list]');
  const highlightEmpty = document.querySelector('[data-epub-highlights-empty]');
  const notesList = document.querySelector('[data-epub-notes-list]');
  const notesEmpty = document.querySelector('[data-epub-notes-empty]');

  const splitSelection = (value) => {
    const raw = String(value || '');
    const match = raw.match(/\n?\[cfi:([^\]]+)\]\s*$/);
    return {
      text: match ? raw.slice(0, match.index).trim() : raw.trim(),
      cfi: match ? match[1].trim() : '',
    };
  };

  const addAnnotation = (highlight) => {
    if (!state.rendition || !highlight) return;
    const { text, cfi } = splitSelection(highlight.texto_selecionado);
    if (!cfi || state.appliedHighlights.has(cfi)) return;
    try {
      state.rendition.annotations.highlight(
        cfi,
        { id: highlight.id },
        () => announce('Destaque: ' + text.slice(0, 80)),
        'pd-epub-highlight',
        { 'fill': highlight.cor || '#FFFF00', 'fill-opacity': '0.32', 'mix-blend-mode': 'multiply' }
      );
      state.appliedHighlights.add(cfi);
    } catch (_) { /* cfi inválido */ }
  };

  const renderBookmarks = () => {
    if (!bookmarkList) return;
    bookmarkList.replaceChildren();

    state.bookmarks.forEach((bookmark) => {
      const item = document.createElement('li');
      item.className = 'reader-panel-list-item';

      const jump = document.createElement('button');
      jump.type = 'button';
      jump.className = 'reader-bookmark-jump';
      jump.innerHTML = '<strong></strong><span></span>';
      jump.querySelector('strong').textContent = bookmark.titulo || 'Marcador';
      jump.querySelector('span').textContent = Math.round(Number(bookmark.numero_pagina) || 0) + '% do livro';
      jump.addEventListener('click', () => {
        const percent = Math.max(0, Math.min(100, Number(bookmark.numero_pagina) || 0));
        if (state.locationsReady) {
          const cfi = state.book.locations.cfiFromPercentage(percent / 100);
          if (cfi) state.rendition.display(cfi);
        }
        if (window.innerWidth < 992) setPanel(false);
      });

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'reader-icon-button reader-icon-danger';
      remove.setAttribute('aria-label', 'Excluir marcador');
      remove.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i>';
      remove.addEventListener('click', async () => {
        const data = await postForm(api.marcadores, { action: 'delete', id: String(bookmark.id) });
        if (data && Array.isArray(data.marcadores)) {
          state.bookmarks = data.marcadores;
          renderBookmarks();
          announce('Marcador excluído.');
        }
      });

      item.append(jump, remove);
      bookmarkList.appendChild(item);
    });

    if (bookmarkEmpty) bookmarkEmpty.hidden = state.bookmarks.length > 0;
    bookmarkList.hidden = state.bookmarks.length === 0;
  };

  const renderHighlights = () => {
    if (!highlightList) return;
    highlightList.replaceChildren();

    state.highlights.forEach((highlight) => {
      const { text, cfi } = splitSelection(highlight.texto_selecionado);
      const item = document.createElement('li');

      const quote = document.createElement('blockquote');
      quote.textContent = text.length > 160 ? text.slice(0, 157) + '…' : text;
      quote.style.borderLeftColor = highlight.cor || '#FFFF00';

      const actions = document.createElement('div');
      actions.className = 'd-flex justify-content-between align-items-center gap-2';

      const jump = document.createElement('button');
      jump.type = 'button';
      jump.className = 'reader-link-button';
      jump.textContent = 'Ir para o trecho';
      jump.addEventListener('click', () => {
        if (!cfi) return;
        try { state.rendition.display(cfi); } catch (_) { /* ignora */ }
        if (window.innerWidth < 992) setPanel(false);
      });

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'reader-icon-button reader-icon-danger';
      remove.setAttribute('aria-label', 'Excluir destaque');
      remove.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i>';
      remove.addEventListener('click', async () => {
        const data = await postForm(api.destaques, { action: 'delete', id: String(highlight.id) });
        if (data && Array.isArray(data.destaques)) {
          state.highlights = data.destaques;
          if (cfi) state.appliedHighlights.delete(cfi);
          renderHighlights();
          announce('Destaque excluído.');
        }
      });

      actions.append(jump, remove);
      item.append(quote, actions);
      highlightList.appendChild(item);

      addAnnotation(highlight);
    });

    if (highlightEmpty) highlightEmpty.hidden = state.highlights.length > 0;
    highlightList.hidden = state.highlights.length === 0;
  };

  const renderNotes = () => {
    if (!notesList) return;
    notesList.replaceChildren();

    state.notes.forEach((note) => {
      const item = document.createElement('li');
      item.className = 'reader-panel-list-item';

      const content = document.createElement('div');
      content.className = 'reader-bookmark-jump';
      const title = document.createElement('strong');
      title.textContent = note.conteudo || 'Anotação';
      const excerpt = document.createElement('span');
      const plain = splitSelection(note.texto_selecionado).text;
      excerpt.textContent = plain ? plain.slice(0, 90) : 'Trecho do livro';
      content.append(title, excerpt);

      const remove = document.createElement('button');
      remove.type = 'button';
      remove.className = 'reader-icon-button reader-icon-danger';
      remove.setAttribute('aria-label', 'Excluir anotação');
      remove.innerHTML = '<i class="bi bi-trash3" aria-hidden="true"></i>';
      remove.addEventListener('click', async () => {
        const data = await postForm(api.anotacoes, { action: 'delete', id: String(note.id) });
        if (data && Array.isArray(data.anotacoes)) {
          state.notes = data.anotacoes;
          renderNotes();
          announce('Anotação excluída.');
        }
      });

      item.append(content, remove);
      notesList.appendChild(item);
    });

    if (notesEmpty) notesEmpty.hidden = state.notes.length > 0;
    notesList.hidden = state.notes.length === 0;
  };

  const createBookmark = async (title) => {
    const data = await postForm(api.marcadores, {
      numero_pagina: String(Math.max(1, Math.round(state.percent))),
      titulo: String(title || 'Marcador').slice(0, 150),
    });
    if (data && Array.isArray(data.marcadores)) {
      state.bookmarks = data.marcadores;
      renderBookmarks();
      announce('Marcador criado.');
    }
  };

  document.querySelector('[data-reader-bookmark]')?.addEventListener('click', () => {
    if (!api.marcadores) {
      announce('Marcadores indisponíveis para este livro.');
      return;
    }
    if (bookmarkTitle) bookmarkTitle.value = state.chapter || 'Posição ' + Math.round(state.percent) + '%';
    openDialog(bookmarkDialog);
    window.setTimeout(() => bookmarkTitle?.select(), 40);
  });

  bookmarkForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    await createBookmark((bookmarkTitle?.value || '').trim());
    closeDialog(bookmarkDialog);
  });

  const createHighlight = async (text, cfiRange, color) => {
    const data = await postForm(api.destaques, {
      numero_pagina: String(Math.max(1, Math.round(state.percent))),
      texto_selecionado: (text + '\n[cfi:' + cfiRange + ']').slice(0, 2000),
      cor: color,
    });
    if (data && Array.isArray(data.destaques)) {
      state.highlights = data.destaques;
      renderHighlights();
      return data;
    }
    return null;
  };

  document.querySelector('[data-epub-highlight]')?.addEventListener('click', async () => {
    if (!state.lastSelection) {
      announce('Selecione um trecho no livro para destacar.');
      return;
    }
    const { text, cfiRange } = state.lastSelection;
    const data = await createHighlight(text, cfiRange, '#FFFF00');
    if (data) announce('Trecho destacado.');
    state.lastSelection = null;
  });

  document.querySelector('[data-epub-note]')?.addEventListener('click', () => {
    if (!state.lastSelection) {
      announce('Selecione um trecho para vincular a anotação.');
      return;
    }
    if (noteQuote) noteQuote.textContent = state.lastSelection.text.slice(0, 220);
    if (noteText) noteText.value = '';
    openDialog(noteDialog);
    window.setTimeout(() => noteText?.focus(), 40);
  });

  noteForm?.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (!state.lastSelection) {
      closeDialog(noteDialog);
      return;
    }
    const content = (noteText?.value || '').trim();
    if (content === '') return;

    const { text, cfiRange } = state.lastSelection;
    const highlightData = await createHighlight(text, cfiRange, '#FFE066');
    const highlightId = highlightData?.destaque?.id;
    if (!highlightId) {
      announce('Não foi possível criar a base da anotação.');
      closeDialog(noteDialog);
      return;
    }

    const data = await postForm(api.anotacoes, { destaque_id: String(highlightId), conteudo: content });
    if (data && Array.isArray(data.anotacoes)) {
      state.notes = data.anotacoes;
      renderNotes();
      activateTab(panel?.querySelector('[data-reader-tab="notes"]'));
      announce('Anotação criada.');
    }
    state.lastSelection = null;
    closeDialog(noteDialog);
  });

  // ---------------------------------------------------------------------
  // Seleção dentro do EPUB
  // ---------------------------------------------------------------------

  const captureSelection = (cfiRange, contents) => {
    try {
      const selection = contents?.window?.getSelection?.();
      const text = selection?.toString?.().trim() || '';
      if (text && cfiRange) state.lastSelection = { text, cfiRange };
    } catch (_) { /* ignora */ }
  };

  // ---------------------------------------------------------------------
  // Gestos e teclado
  // ---------------------------------------------------------------------

  let touchStartX = 0;
  let touchStartY = 0;

  const attachContentListeners = (contents) => {
    const doc = contents?.document;
    if (!doc) return;

    doc.addEventListener('touchstart', (event) => {
      if (event.touches.length !== 1) return;
      touchStartX = event.touches[0].clientX;
      touchStartY = event.touches[0].clientY;
    }, { passive: true });

    doc.addEventListener('touchend', (event) => {
      if (prefs.flow !== 'paged') return;
      const touch = event.changedTouches[0];
      if (!touch) return;
      const dx = touch.clientX - touchStartX;
      const dy = touch.clientY - touchStartY;
      if (Math.abs(dx) < 56 || Math.abs(dx) < Math.abs(dy) * 1.4) return;
      if (dx < 0) goNext();
      else goPrev();
    }, { passive: true });

    doc.addEventListener('keydown', (event) => handleKey(event));
  };

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

  const fullscreenButton = document.querySelector('[data-reader-fullscreen]');
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
    window.setTimeout(() => state.rendition?.resize(), 80);
  });

  const shouldIgnoreKey = (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) return false;
    const tag = target.tagName.toLowerCase();
    return ['input', 'select', 'textarea', 'button'].includes(tag) || target.isContentEditable;
  };

  function handleKey(event) {
    if (event.ctrlKey || event.metaKey || event.altKey) return;
    const key = event.key;

    if (key === 'Escape') {
      if (document.fullscreenElement === shell) { document.exitFullscreen?.(); return; }
      document.querySelectorAll('dialog[open]').forEach((dialog) => closeDialog(dialog));
      if (panel && !panel.hidden) { setPanel(false); panelToggle?.focus({ preventScroll: true }); }
      return;
    }

    if (document.querySelector('dialog[open]')) return;
    if (shouldIgnoreKey(event)) return;

    switch (key) {
      case 'ArrowLeft':
      case 'PageUp':
        event.preventDefault();
        goPrev();
        break;
      case 'ArrowRight':
      case 'PageDown':
        event.preventDefault();
        goNext();
        break;
      case 'Home':
        event.preventDefault();
        goToStart();
        break;
      case 'End':
        event.preventDefault();
        goToEnd();
        break;
      case '+':
      case '=':
        event.preventDefault();
        prefs.fontSize = Math.min(180, prefs.fontSize + 10);
        applyPrefs();
        persistPrefs();
        announce('Texto em ' + prefs.fontSize + '%.');
        break;
      case '-':
      case '_':
        event.preventDefault();
        prefs.fontSize = Math.max(80, prefs.fontSize - 10);
        applyPrefs();
        persistPrefs();
        announce('Texto em ' + prefs.fontSize + '%.');
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
      default:
        break;
    }
  }

  document.addEventListener('keydown', handleKey);

  // Swipe na área externa (quando o iframe ainda não capturou o toque).
  stage.addEventListener('touchstart', (event) => {
    if (event.touches.length !== 1) return;
    touchStartX = event.touches[0].clientX;
    touchStartY = event.touches[0].clientY;
  }, { passive: true });

  stage.addEventListener('touchend', (event) => {
    if (prefs.flow !== 'paged') return;
    const touch = event.changedTouches[0];
    if (!touch) return;
    const dx = touch.clientX - touchStartX;
    const dy = touch.clientY - touchStartY;
    if (Math.abs(dx) < 56 || Math.abs(dx) < Math.abs(dy) * 1.4) return;
    if (dx < 0) goNext();
    else goPrev();
  }, { passive: true });

  // ---------------------------------------------------------------------
  // Inicialização
  // ---------------------------------------------------------------------

  const init = async () => {
    applyPrefs();
    updateFlowUi();

    state.book = window.ePub(cfg.serveUrl, { openAs: 'epub' });
    state.rendition = state.book.renderTo(host, {
      width: '100%',
      height: '100%',
      flow: prefs.flow === 'scroll' ? 'scrolled' : 'paginated',
      spread: 'auto',
    });

    state.rendition.on('selected', (cfiRange, contents) => captureSelection(cfiRange, contents));

    state.rendition.hooks.content.register((contents) => {
      attachContentListeners(contents);
    });

    state.rendition.on('relocated', (location) => {
      const cfi = location?.start?.cfi || '';
      if (cfi) state.cfi = cfi;
      const percent = percentFromCfi(cfi);
      const chapter = chapterFor(location?.start?.href || '');
      updateUi(percent, chapter);
      highlightCurrentToc(location?.start?.href || '');
      scheduleSave();
    });

    await state.book.ready;

    try {
      await state.book.locations.generate(800);
      state.locationsReady = true;
      state.totalLocations = typeof state.book.locations.length === 'function' ? state.book.locations.length() : 0;
    } catch (error) {
      console.info('[PracticeDay] Não foi possível gerar as localizações do EPUB.', error);
    }

    await state.book.loaded.navigation;
    renderToc(state.book.navigation?.toc || []);

    applyBookTheme();
    updateUi(state.percent, '');

    const start = cfg.initialCfi || (state.locationsReady ? state.book.locations.cfiFromPercentage(Math.max(0, Math.min(1, state.percent / 100))) : '');
    try {
      if (start) await state.rendition.display(start);
      else await state.rendition.display();
    } catch (error) {
      console.info('[PracticeDay] Não foi possível abrir a posição salva; abrindo o início.', error);
      await state.rendition.display();
    }

    if (loading) loading.classList.add('d-none');
    renderBookmarks();
    renderHighlights();
    renderNotes();

    // As anotações podem ser aplicadas depois que o primeiro capítulo renderiza.
    state.rendition.on('rendered', () => {
      state.highlights.forEach(addAnnotation);
    });

    announce('Livro pronto para leitura.');
  };

  renderBookmarks();
  renderHighlights();
  renderNotes();
  updateUi(state.percent, '');

  init().catch((error) => {
    console.error('[PracticeDay] Falha ao abrir o EPUB.', error);
    if (loading) loading.classList.remove('d-none');
    if (loadingText) loadingText.textContent = 'Não foi possível abrir este EPUB. Tente baixar o arquivo e abrir em outro leitor.';
  });
})();
