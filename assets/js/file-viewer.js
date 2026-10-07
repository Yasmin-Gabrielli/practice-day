(function () {
  const cfg = window.PracticeDayFileViewer;
  if (!cfg) return;

  const stage = document.querySelector('[data-viewer-stage]');
  const fullscreenBtn = document.querySelector('[data-viewer-fullscreen]');

  if (fullscreenBtn && stage) {
    fullscreenBtn.addEventListener('click', () => {
      const target = stage;
      if (!document.fullscreenElement) {
        target.requestFullscreen?.().catch(() => {});
      } else {
        document.exitFullscreen?.();
      }
    });
  }

  if (cfg.type === 'pdf' && window['pdfjsLib']) {
    const pdfjsLib = window['pdfjsLib'];
    pdfjsLib.GlobalWorkerOptions.workerSrc =
      'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const canvas = document.getElementById('pdf-canvas');
    const loadingEl = document.querySelector('[data-pdf-loading]');
    const errorEl = document.querySelector('[data-pdf-error]');
    const pageLabel = document.querySelector('[data-pdf-page-label]');
    const btnPrev = document.querySelector('[data-pdf-prev]');
    const btnNext = document.querySelector('[data-pdf-next]');
    const btnZoomIn = document.querySelector('[data-pdf-zoom-in]');
    const btnZoomOut = document.querySelector('[data-pdf-zoom-out]');
    const btnPrint = document.querySelector('[data-pdf-print]');
    const btnBookmark = document.querySelector('[data-pdf-bookmark]');

    let pdfDoc = null;
    let pageNum = 1;
    let scale = 1.2;
    let rendering = false;
    let sessionStart = Date.now();

    const postReading = (extra = {}) => {
      if (!cfg.libraryApi?.progresso || !pdfDoc) return Promise.resolve();
      const body = new URLSearchParams({
        _csrf_token: cfg.csrfToken,
        pagina_atual: String(pageNum),
        total_paginas: String(pdfDoc.numPages),
        progresso_porcentagem: String(Math.round((pageNum / pdfDoc.numPages) * 10000) / 100),
        tempo_leitura_segundos: String(extra.tempo || 0),
        paginas_lidas_sessao: String(extra.paginas || 0),
      });
      return fetch(cfg.libraryApi.progresso, { method: 'POST', credentials: 'same-origin', headers: {'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body });
    };

    const renderPage = (num) => {
      if (!pdfDoc || !canvas) return;
      rendering = true;
      pdfDoc.getPage(num).then((page) => {
        const viewport = page.getViewport({ scale });
        const ctx = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;
        page.render({ canvasContext: ctx, viewport }).promise.then(() => {
          rendering = false;
          if (pageLabel) {
            pageLabel.textContent = `${num} / ${pdfDoc.numPages}`;
          }
          postReading({ paginas: 1 }).catch(() => {});
        });
      });
    };

    const queuePage = (num) => {
      if (rendering || !pdfDoc) return;
      if (num < 1 || num > pdfDoc.numPages) return;
      pageNum = num;
      renderPage(pageNum);
    };

    pdfjsLib
      .getDocument({ url: cfg.serveUrl, withCredentials: true })
      .promise.then((doc) => {
        pdfDoc = doc;
        if (loadingEl) loadingEl.classList.add('d-none');
        const saved = Number(cfg.library?.pagina_atual || 1);
        queuePage(Math.max(1, Math.min(saved, pdfDoc.numPages)));
      })
      .catch((err) => {
        if (loadingEl) loadingEl.classList.add('d-none');
        if (errorEl) {
          errorEl.textContent = 'Erro ao carregar o PDF. Tente baixar o arquivo.';
          errorEl.classList.remove('d-none');
        }
        console.error(err);
      });

    btnPrev?.addEventListener('click', () => queuePage(pageNum - 1));
    btnNext?.addEventListener('click', () => queuePage(pageNum + 1));
    btnZoomIn?.addEventListener('click', () => {
      scale = Math.min(scale + 0.2, 3);
      renderPage(pageNum);
    });
    btnZoomOut?.addEventListener('click', () => {
      scale = Math.max(scale - 0.2, 0.6);
      renderPage(pageNum);
    });
    btnPrint?.addEventListener('click', () => {
      const w = window.open(cfg.serveUrl, '_blank');
      if (w) {
        w.addEventListener('load', () => {
          try {
            w.print();
          } catch (_) {}
        });
      }
    });
    btnBookmark?.addEventListener('click', async () => {
      if (!cfg.libraryApi?.marcadores || !pdfDoc) return;
      const title = window.prompt('Título do marcador (opcional):') || `Página ${pageNum}`;
      const body = new URLSearchParams({_csrf_token: cfg.csrfToken, numero_pagina: String(pageNum), titulo: title});
      await fetch(cfg.libraryApi.marcadores, {method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/x-www-form-urlencoded;charset=UTF-8'}, body});
    });
    setInterval(() => postReading({tempo: 15}).catch(() => {}), 15000);
    window.addEventListener('beforeunload', () => postReading({tempo: Math.min(120, Math.round((Date.now() - sessionStart) / 1000))}).catch(() => {}));
  }

  if (cfg.type === 'image') {
    const img = document.querySelector('[data-image-view]');
    const wrap = document.querySelector('[data-image-wrap]');
    let scale = 1;

    const applyScale = () => {
      if (!img) return;
      img.style.transform = `scale(${scale})`;
    };

    document.querySelector('[data-img-zoom-in]')?.addEventListener('click', () => {
      scale = Math.min(scale + 0.25, 4);
      applyScale();
    });
    document.querySelector('[data-img-zoom-out]')?.addEventListener('click', () => {
      scale = Math.max(scale - 0.25, 0.5);
      applyScale();
    });
    document.querySelector('[data-img-zoom-reset]')?.addEventListener('click', () => {
      scale = 1;
      applyScale();
    });

    if (wrap && img) {
      let dragging = false;
      let startX = 0;
      let startY = 0;
      let scrollLeft = 0;
      let scrollTop = 0;

      wrap.addEventListener('pointerdown', (e) => {
        dragging = true;
        startX = e.clientX;
        startY = e.clientY;
        scrollLeft = wrap.scrollLeft;
        scrollTop = wrap.scrollTop;
        wrap.setPointerCapture(e.pointerId);
      });
      wrap.addEventListener('pointermove', (e) => {
        if (!dragging) return;
        wrap.scrollLeft = scrollLeft - (e.clientX - startX);
        wrap.scrollTop = scrollTop - (e.clientY - startY);
      });
      wrap.addEventListener('pointerup', () => {
        dragging = false;
      });
    }
  }
})();
