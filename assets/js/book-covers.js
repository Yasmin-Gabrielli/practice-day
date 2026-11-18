/**
 * Capas automáticas de livros.
 *
 * EPUBs chegam com capa detectada no servidor. Para PDFs, renderizamos a
 * primeira página com PDF.js e enviamos a imagem para o cache do servidor.
 * Assim a capa aparece nas listas e continua disponível nas próximas visitas.
 */
(function () {
  'use strict';

  const PDFJS_SRC = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js';
  const PDFJS_WORKER = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
  const MAX_CONCURRENT = 2;

  const queue = [];
  let active = 0;
  let pdfjsPromise = null;

  const csrfToken = () => {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') || '' : window.PRACTICE_DAY_CSRF || '';
  };

  const loadPdfJs = () => {
    if (window.pdfjsLib) {
      window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
      return Promise.resolve(window.pdfjsLib);
    }

    if (pdfjsPromise) return pdfjsPromise;

    pdfjsPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = PDFJS_SRC;
      script.async = true;
      script.onload = () => {
        if (window.pdfjsLib) {
          window.pdfjsLib.GlobalWorkerOptions.workerSrc = PDFJS_WORKER;
          resolve(window.pdfjsLib);
        } else {
          reject(new Error('PDF.js indisponível'));
        }
      };
      script.onerror = () => reject(new Error('Falha ao carregar PDF.js'));
      document.head.appendChild(script);
    });

    return pdfjsPromise;
  };

  const saveCover = (url, dataUrl) => {
    const token = csrfToken();
    if (!url || !token) return Promise.resolve();

    return fetch(url, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8' },
      body: new URLSearchParams({ _csrf_token: token, imagem: dataUrl }),
    }).catch(() => {});
  };

  const renderCover = async (img) => {
    const pdfUrl = img.dataset.fileUrl;
    const saveUrl = img.dataset.saveUrl || img.dataset.coverUrl;
    if (!pdfUrl) return;

    img.dataset.coverBusy = '1';

    try {
      const pdfjsLib = await loadPdfJs();
      const documentTask = pdfjsLib.getDocument({ url: pdfUrl, withCredentials: true });
      const pdf = await documentTask.promise;
      const page = await pdf.getPage(1);
      const base = page.getViewport({ scale: 1 });
      const targetWidth = 560;
      const scale = Math.min(2.4, Math.max(0.4, targetWidth / base.width));
      const viewport = page.getViewport({ scale });
      const canvas = document.createElement('canvas');
      const context = canvas.getContext('2d', { alpha: false });

      canvas.width = Math.max(1, Math.floor(viewport.width));
      canvas.height = Math.max(1, Math.floor(viewport.height));
      context.fillStyle = '#ffffff';
      context.fillRect(0, 0, canvas.width, canvas.height);

      await page.render({ canvasContext: context, viewport }).promise;

      const dataUrl = canvas.toDataURL('image/jpeg', 0.86);
      img.src = dataUrl;
      img.dataset.coverCached = '1';
      img.classList.add('is-ready');
      img.removeAttribute('aria-busy');

      await saveCover(saveUrl, dataUrl);

      if (typeof pdf.destroy === 'function') {
        pdf.destroy().catch(() => {});
      }
    } catch (error) {
      img.dataset.coverCached = '2';
      img.classList.add('cover-unavailable');
      if (window.console && console.info) {
        console.info('[PracticeDay] Não foi possível gerar a capa do PDF.', error);
      }
    } finally {
      img.dataset.coverBusy = '0';
    }
  };

  const pump = () => {
    while (active < MAX_CONCURRENT && queue.length > 0) {
      const img = queue.shift();
      if (!img || !img.isConnected) continue;
      active += 1;
      renderCover(img).finally(() => {
        active -= 1;
        pump();
      });
    }
  };

  const observe = (img) => {
    if (img.dataset.coverQueued === '1') return;
    img.dataset.coverQueued = '1';
    img.setAttribute('aria-busy', 'true');

    if (!('IntersectionObserver' in window)) {
      queue.push(img);
      pump();
      return;
    }

    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        observer.disconnect();
        queue.push(img);
        pump();
      });
    }, { rootMargin: '450px 0px' });

    observer.observe(img);
  };

  const scan = (root) => {
    const scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll('img[data-pdf-cover][data-cover-cached="0"]').forEach(observe);
  };

  document.addEventListener('DOMContentLoaded', () => scan(document));
  window.PracticeDayBookCovers = { refresh: scan };
})();
