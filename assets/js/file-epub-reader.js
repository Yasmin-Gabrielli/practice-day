(function () {
  const cfg = window.PracticeDayEpubReader;
  if (!cfg || !cfg.libraryId || typeof ePub === 'undefined') return;

  const host = document.getElementById('epub-viewer');
  const loadingEl = document.querySelector('[data-epub-loading]');
  const progressLabel = document.querySelector('[data-epub-progress-label]');
  const pageLabel = document.querySelector('[data-epub-page-label]');
  const bookmarksList = document.querySelector('[data-epub-bookmarks-list]');
  const highlightsList = document.querySelector('[data-epub-highlights-list]');
  const notesList = document.querySelector('[data-epub-notes-list]');

  let book = null;
  let rendition = null;
  let sessionStart = Date.now();
  let lastLocation = null;
  let totalLocations = 0;
  let state = {
    marcadores: cfg.initial.marcadores || [],
    destaques: cfg.initial.destaques || [],
    anotacoes: cfg.initial.anotacoes || [],
  };

  const postForm = async (url, fields) => {
    const body = new URLSearchParams({ _csrf_token: cfg.csrfToken, ...fields });
    const res = await fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body,
    });
    return res.json();
  };

  const saveProgress = async (extra = {}) => {
    if (!rendition || !cfg.api.progresso) return;
    const loc = rendition.currentLocation();
    const cfi = loc?.start?.cfi || '';
    const pct = book?.locations?.percentageFromCfi?.(cfi);
    const progressoPct = pct != null ? Math.round(pct * 1000) / 10 : 0;
    const pagina = Math.max(1, Math.round(progressoPct));

    await postForm(cfg.api.progresso, {
      pagina_atual: String(pagina),
      progresso_porcentagem: String(progressoPct),
      cfi,
      tempo_leitura_segundos: String(extra.tempo || 0),
      paginas_lidas_sessao: String(extra.paginas || 0),
      total_paginas: String(totalLocations),
    });

    if (progressLabel) {
      progressLabel.textContent = `${progressoPct.toFixed(1).replace('.', ',')}%`;
    }
    if (pageLabel && totalLocations > 0) {
      pageLabel.textContent = `${Math.max(1, Math.round((progressoPct / 100) * totalLocations))} / ${totalLocations}`;
    }
  };

  const renderLists = () => {
    if (bookmarksList) {
      bookmarksList.innerHTML = '';
      state.marcadores.forEach((m) => {
        const li = document.createElement('li');
        li.innerHTML = `<strong>${m.titulo || 'Marcador'}</strong><small>Pág. ${m.numero_pagina}</small>
          <button type="button" class="btn btn-sm btn-link text-danger p-0" data-del-bookmark="${m.id}">Excluir</button>`;
        bookmarksList.appendChild(li);
      });
    }
    if (highlightsList) {
      highlightsList.innerHTML = '';
      state.destaques.forEach((h) => {
        const li = document.createElement('li');
        li.innerHTML = `<span style="background:${h.cor}33">${(h.texto_selecionado || '').slice(0, 80)}</span>
          <button type="button" class="btn btn-sm btn-link text-danger p-0" data-del-highlight="${h.id}">Excluir</button>`;
        highlightsList.appendChild(li);
      });
    }
    if (notesList) {
      notesList.innerHTML = '';
      state.anotacoes.forEach((n) => {
        const li = document.createElement('li');
        li.innerHTML = `<p>${n.conteudo}</p><small>${(n.texto_selecionado || '').slice(0, 60)}</small>
          <button type="button" class="btn btn-sm btn-link text-danger p-0" data-del-note="${n.id}">Excluir</button>`;
        notesList.appendChild(li);
      });
    }
  };

  document.body.addEventListener('click', async (e) => {
    const t = e.target;
    if (!(t instanceof HTMLElement)) return;

    const delBm = t.getAttribute('data-del-bookmark');
    if (delBm && cfg.api.marcadores) {
      const data = await postForm(cfg.api.marcadores, { action: 'delete', id: delBm });
      if (data.marcadores) state.marcadores = data.marcadores;
      renderLists();
    }

    const delHi = t.getAttribute('data-del-highlight');
    if (delHi && cfg.api.destaques) {
      const data = await postForm(cfg.api.destaques, { action: 'delete', id: delHi });
      if (data.destaques) state.destaques = data.destaques;
      renderLists();
    }

    const delNote = t.getAttribute('data-del-note');
    if (delNote && cfg.api.anotacoes) {
      const data = await postForm(cfg.api.anotacoes, { action: 'delete', id: delNote });
      if (data.anotacoes) state.anotacoes = data.anotacoes;
      renderLists();
    }
  });

  const init = async () => {
    book = ePub(cfg.serveUrl, { openAs: 'epub' });
    rendition = book.renderTo('epub-viewer', { width: '100%', height: '100%', flow: 'paginated' });

    await book.ready;
    await book.locations.generate(800);
    totalLocations = typeof book.locations.length === 'function' ? book.locations.length() : 0;

    const startCfi = cfg.initial.progresso?.cfi;
    if (startCfi) {
      await rendition.display(startCfi);
    } else {
      await rendition.display();
    }

    if (loadingEl) loadingEl.classList.add('d-none');

    rendition.on('relocated', (location) => {
      lastLocation = location;
      saveProgress({ paginas: 1 }).catch(() => {});
    });

    setInterval(() => {
      saveProgress({ tempo: 15, paginas: 0 }).catch(() => {});
    }, 15000);

    window.addEventListener('beforeunload', () => {
      const secs = Math.round((Date.now() - sessionStart) / 1000);
      navigator.sendBeacon(
        cfg.api.progresso,
        new URLSearchParams({
          _csrf_token: cfg.csrfToken,
          pagina_atual: '1',
          progresso_porcentagem: progressLabel?.textContent?.replace('%', '').replace(',', '.') || '0',
          tempo_leitura_segundos: String(Math.min(secs, 120)),
          paginas_lidas_sessao: '1',
        })
      );
    });
  };

  document.querySelector('[data-epub-prev]')?.addEventListener('click', () => rendition?.prev());
  document.querySelector('[data-epub-next]')?.addEventListener('click', () => rendition?.next());

  document.querySelector('[data-epub-bookmark]')?.addEventListener('click', async () => {
    const loc = rendition?.currentLocation();
    const cfi = loc?.start?.cfi || '';
    const pct = book?.locations?.percentageFromCfi?.(cfi);
    const pagina = pct != null ? Math.max(1, Math.round(pct * 100)) : 1;
    const titulo = window.prompt('Título do marcador (opcional):') || `Posição ${pagina}%`;
    const data = await postForm(cfg.api.marcadores, {
      numero_pagina: String(pagina),
      titulo,
    });
    if (data.marcadores) {
      state.marcadores = data.marcadores;
      renderLists();
    }
  });

  document.querySelector('[data-epub-highlight]')?.addEventListener('click', async () => {
    const sel = window.getSelection();
    const text = sel?.toString()?.trim();
    if (!text) {
      window.alert('Selecione um trecho no livro para destacar.');
      return;
    }
    const loc = rendition?.currentLocation();
    const cfi = loc?.start?.cfi || '';
    const pct = book?.locations?.percentageFromCfi?.(cfi);
    const pagina = pct != null ? Math.max(1, Math.round(pct * 100)) : 1;
    const payload = `${text}\n[cfi:${cfi}]`;
    const data = await postForm(cfg.api.destaques, {
      numero_pagina: String(pagina),
      texto_selecionado: payload.slice(0, 2000),
      cor: '#FFFF00',
    });
    if (data.destaques) {
      state.destaques = data.destaques;
      renderLists();
    }
    sel?.removeAllRanges();
  });

  document.querySelector('[data-epub-note]')?.addEventListener('click', async () => {
    const sel = window.getSelection();
    const text = sel?.toString()?.trim();
    if (!text) {
      window.alert('Selecione um trecho para vincular a anotação.');
      return;
    }
    const loc = rendition?.currentLocation();
    const cfi = loc?.start?.cfi || '';
    const pct = book?.locations?.percentageFromCfi?.(cfi);
    const pagina = pct != null ? Math.max(1, Math.round(pct * 100)) : 1;
    const highlightRes = await postForm(cfg.api.destaques, {
      numero_pagina: String(pagina),
      texto_selecionado: `${text}\n[cfi:${cfi}]`.slice(0, 2000),
      cor: '#FFE066',
    });
    const destaqueId = highlightRes?.destaque?.id;
    if (!destaqueId) return;
    const conteudo = window.prompt('Texto da anotação:');
    if (!conteudo) return;
    const noteRes = await postForm(cfg.api.anotacoes, {
      destaque_id: destaqueId,
      conteudo,
    });
    if (highlightRes.destaques) state.destaques = highlightRes.destaques;
    if (noteRes.anotacoes) state.anotacoes = noteRes.anotacoes;
    renderLists();
    sel?.removeAllRanges();
  });

  document.querySelector('[data-viewer-fullscreen]')?.addEventListener('click', () => {
    const stage = document.querySelector('.epub-stage');
    if (!stage) return;
    if (!document.fullscreenElement) stage.requestFullscreen?.();
    else document.exitFullscreen?.();
  });

  renderLists();
  init().catch((err) => {
    if (loadingEl) {
      loadingEl.textContent = 'Não foi possível abrir este EPUB.';
    }
    console.error(err);
  });
})();
