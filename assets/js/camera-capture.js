/**
 * Captura de páginas pela câmera.
 *
 * - Usa a câmera traseira por padrão (celulares) com resolução alta.
 * - A prévia e a foto mantêm a orientação real do documento (sem espelhamento),
 *   para que textos e páginas não saiam invertidos.
 * - Espera o quadro estabilizar antes de fotografar, reduzindo fotos tremidas.
 * - Permite revisar, reordenar e remover páginas antes de enviar.
 */
(function () {
  'use strict';

  const MAX_PAGES = 20;
  const MAX_BYTES = 10 * 1024 * 1024;
  const MAX_EDGE = 3000;
  const megabytes = (value) => value * 1024 * 1024;

  const init = () => {
    const form = document.querySelector('[data-scan-form]');
    if (!form) return;

    const video = document.querySelector('[data-camera-video]');
    const stage = document.querySelector('[data-scan-stage]');
    const placeholder = document.querySelector('[data-camera-placeholder]');
    const flash = document.querySelector('[data-scan-flash]');
    const cameraStatus = document.querySelector('[data-camera-status]');
    const cameraState = document.querySelector('[data-camera-state]');
    const errorBox = document.querySelector('[data-scan-error]');
    const openButtons = Array.from(document.querySelectorAll('[data-camera-open]'));
    const captureBtn = document.querySelector('[data-camera-capture]');
    const flipBtn = document.querySelector('[data-camera-flip]');
    const stopBtn = document.querySelector('[data-camera-stop]');
    const filesInput = document.querySelector('[data-camera-files]');

    const thumbs = document.querySelector('[data-scan-thumbs]');
    const empty = document.querySelector('[data-scan-empty]');
    const countBadge = document.querySelector('[data-scan-count]');
    const clearBtn = document.querySelector('[data-scan-clear]');
    const submitBtn = document.querySelector('[data-scan-submit]');
    const resetBtn = document.querySelector('[data-scan-reset]');

    if (!video || !stage || !thumbs) return;

    /** @type {{id:number,file:File,url:string}[]} */
    const pages = [];
    let nextId = 1;
    let stream = null;
    let facing = 'environment';
    let lastGoodFacing = facing;
    let starting = false;
    let submitting = false;

    video.muted = true;
    video.playsInline = true;

    const showError = (message) => {
      if (!errorBox) return;
      errorBox.textContent = message;
      errorBox.hidden = false;
    };

    const hideError = () => {
      if (!errorBox) return;
      errorBox.textContent = '';
      errorBox.hidden = true;
    };

    const setCameraState = (label, tone) => {
      if (!cameraState) return;
      cameraState.textContent = label;
      cameraState.className = 'badge border ' + (tone === 'on' ? 'text-bg-success' : 'text-bg-light');
    };

    const updateCount = () => {
      const total = pages.length;
      if (countBadge) countBadge.textContent = total + (total === 1 ? ' página' : ' páginas');
      if (empty) empty.hidden = total > 0;
      if (clearBtn) clearBtn.hidden = total === 0;
      if (submitBtn) submitBtn.disabled = total === 0;
    };

    const iconButton = (icon, label, extraClass) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.className = 'scan-thumb-action' + (extraClass ? ' ' + extraClass : '');
      button.title = label;
      button.setAttribute('aria-label', label);
      const i = document.createElement('i');
      i.className = 'bi ' + icon;
      i.setAttribute('aria-hidden', 'true');
      button.appendChild(i);
      return button;
    };

    const movePage = (from, to) => {
      if (to < 0 || to >= pages.length || from === to) return;
      const [page] = pages.splice(from, 1);
      pages.splice(to, 0, page);
      renderPages();
    };

    const removePage = (index) => {
      const [page] = pages.splice(index, 1);
      if (page) URL.revokeObjectURL(page.url);
      renderPages();
    };

    const renderPages = () => {
      thumbs.textContent = '';

      pages.forEach((page, index) => {
        const item = document.createElement('div');
        item.className = 'scan-thumb';

        const img = document.createElement('img');
        img.src = page.url;
        img.alt = 'Página ' + (index + 1) + ' capturada';
        img.loading = 'lazy';

        const badge = document.createElement('span');
        badge.className = 'scan-thumb-index';
        badge.textContent = String(index + 1);

        const actions = document.createElement('div');
        actions.className = 'scan-thumb-actions';

        const up = iconButton('bi-arrow-left', 'Mover página para a esquerda');
        up.addEventListener('click', () => movePage(index, index - 1));

        const down = iconButton('bi-arrow-right', 'Mover página para a direita');
        down.addEventListener('click', () => movePage(index, index + 1));

        const remove = iconButton('bi-trash', 'Remover página ' + (index + 1), 'is-danger');
        remove.addEventListener('click', () => removePage(index));

        actions.append(up, down, remove);

        const input = document.createElement('input');
        input.type = 'file';
        input.name = 'paginas[]';
        input.hidden = true;
        const transfer = new DataTransfer();
        transfer.items.add(page.file);
        input.files = transfer.files;

        item.append(img, badge, actions, input);
        thumbs.appendChild(item);
      });

      updateCount();
    };

    const addPages = (fileList) => {
      const incoming = Array.from(fileList || []);
      if (incoming.length === 0) return;

      hideError();
      const accepted = [];
      let rejectedType = 0;
      let rejectedSize = 0;

      for (const file of incoming) {
        if (!/^image\/(jpeg|png|webp)$/i.test(file.type)) {
          rejectedType += 1;
          continue;
        }
        if (file.size > MAX_BYTES) {
          rejectedSize += 1;
          continue;
        }
        accepted.push(file);
      }

      const space = MAX_PAGES - pages.length;
      const usable = accepted.slice(0, Math.max(0, space));

      usable.forEach((file) => {
        pages.push({ id: nextId++, file, url: URL.createObjectURL(file) });
      });

      renderPages();

      const messages = [];
      if (rejectedType > 0) messages.push(rejectedType + ' arquivo(s) não são imagens JPEG, PNG ou WEBP.');
      if (rejectedSize > 0) messages.push(rejectedSize + ' arquivo(s) passam do limite de 10 MB por página.');
      if (accepted.length > usable.length) messages.push('Você pode salvar no máximo ' + MAX_PAGES + ' páginas por digitalização.');
      if (messages.length > 0) showError(messages.join(' '));
    };

    const clearPages = () => {
      pages.forEach((page) => URL.revokeObjectURL(page.url));
      pages.length = 0;
      renderPages();
    };

    const waitForMetadata = () => new Promise((resolve) => {
      if (video.readyState >= 1 && video.videoWidth > 0) {
        resolve();
        return;
      }
      const done = () => {
        video.removeEventListener('loadedmetadata', done);
        resolve();
      };
      video.addEventListener('loadedmetadata', done);
      window.setTimeout(done, 1800);
    });

    const stopTracks = () => {
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
      }
    };

    const stopCamera = () => {
      stopTracks();
      video.srcObject = null;
      video.hidden = true;
      video.classList.remove('is-mirrored');
      if (placeholder) placeholder.hidden = false;
      if (cameraStatus) cameraStatus.hidden = true;
      if (captureBtn) captureBtn.disabled = true;
      if (stopBtn) stopBtn.disabled = true;
      stage.classList.remove('is-portrait');
      setCameraState('Desligada', 'off');
    };

    const updateOrientation = () => {
      if (!video.videoWidth || !video.videoHeight) return;
      stage.classList.toggle('is-portrait', video.videoHeight > video.videoWidth);
    };

    const requestCamera = async () => {
      const attempts = [
        {
          audio: false,
          video: {
            facingMode: { ideal: facing },
            width: { ideal: 3840 },
            height: { ideal: 2160 },
          },
        },
        { audio: false, video: { facingMode: { ideal: facing } } },
        { audio: false, video: { facingMode: facing } },
        { audio: false, video: true },
      ];

      let lastError = null;
      for (const constraints of attempts) {
        try {
          return await navigator.mediaDevices.getUserMedia(constraints);
        } catch (error) {
          lastError = error;
          if (error && (error.name === 'NotAllowedError' || error.name === 'SecurityError')) {
            throw error;
          }
        }
      }
      throw lastError || new Error('Não foi possível acessar a câmera.');
    };

    const describeCameraError = (error) => {
      const name = error && error.name ? error.name : '';
      if (name === 'NotAllowedError' || name === 'PermissionDeniedError') {
        return 'Permissão da câmera negada. Libere o acesso nas configurações do navegador ou selecione imagens da galeria.';
      }
      if (name === 'NotFoundError' || name === 'DevicesNotFoundError') {
        return 'Nenhuma câmera foi encontrada neste dispositivo. Use a opção "Selecionar imagens".';
      }
      if (name === 'NotReadableError' || name === 'TrackStartError') {
        return 'A câmera está em uso por outro aplicativo. Feche-o e tente novamente.';
      }
      if (name === 'SecurityError') {
        return 'O navegador exige HTTPS ou localhost para liberar a câmera.';
      }
      return 'Não foi possível abrir a câmera. Verifique a permissão do navegador e tente novamente.';
    };

    const startCamera = async (force) => {
      if (starting) return;
      if (!force && stream && stream.getVideoTracks().some((track) => track.readyState === 'live')) {
        return;
      }
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        showError('Este navegador não permite acesso direto à câmera. Use "Selecionar imagens".');
        return;
      }

      starting = true;
      hideError();
      setCameraState('Abrindo…', 'off');

      try {
        stopTracks();
        stream = await requestCamera();
        lastGoodFacing = facing;

        video.srcObject = stream;
        video.hidden = false;
        if (placeholder) placeholder.hidden = true;

        await waitForMetadata();
        try {
          await video.play();
        } catch (_) {
          /* autoplay pode ser bloqueado; o frame ainda fica disponível */
        }

        // Pequena espera para o auto-foco/exposição estabilizar antes da 1ª foto.
        await new Promise((resolve) => window.setTimeout(resolve, 260));

        const track = stream.getVideoTracks()[0];
        const settings = track && typeof track.getSettings === 'function' ? track.getSettings() : {};
        if (settings && settings.facingMode) {
          facing = String(settings.facingMode).indexOf('user') !== -1 ? 'user' : 'environment';
        }
        lastGoodFacing = facing;

        // Mantém a prévia fiel ao documento: nada de espelhamento.
        video.classList.remove('is-mirrored');
        updateOrientation();

        if (captureBtn) captureBtn.disabled = !video.videoWidth;
        if (stopBtn) stopBtn.disabled = false;
        if (cameraStatus) cameraStatus.hidden = false;
        setCameraState('Ligada', 'on');
      } catch (error) {
        stopCamera();
        showError(describeCameraError(error));
      } finally {
        starting = false;
      }
    };

    const flipCamera = async () => {
      const previous = lastGoodFacing;
      facing = facing === 'environment' ? 'user' : 'environment';
      hideError();

      if (!stream) {
        await startCamera(true);
        return;
      }

      await startCamera(true);

      if (!stream) {
        facing = previous;
        await startCamera(true);
      }
    };

    const waitForFrame = () => new Promise((resolve) => {
      let settled = false;
      const finish = () => {
        if (settled) return;
        settled = true;
        resolve();
      };

      if (typeof video.requestVideoFrameCallback === 'function') {
        video.requestVideoFrameCallback(() => window.setTimeout(finish, 50));
      }
      window.setTimeout(finish, 400);
    });

    const renderFrame = (scale, quality) => new Promise((resolve) => {
      const width = Math.max(1, Math.round(video.videoWidth * scale));
      const height = Math.max(1, Math.round(video.videoHeight * scale));
      const canvas = document.createElement('canvas');
      canvas.width = width;
      canvas.height = height;

      const context = canvas.getContext('2d', { alpha: false });
      if (!context) {
        resolve(null);
        return;
      }

      context.fillStyle = '#ffffff';
      context.fillRect(0, 0, width, height);
      context.drawImage(video, 0, 0, width, height);

      canvas.toBlob((blob) => resolve(blob), 'image/jpeg', quality);
    });

    const capturePage = async () => {
      if (!stream || !video.videoWidth || !video.videoHeight) {
        showError('A câmera ainda não está pronta. Aguarde a prévia aparecer.');
        return;
      }

      hideError();
      if (captureBtn) captureBtn.disabled = true;
      if (flash) {
        flash.classList.add('is-active');
        window.setTimeout(() => flash.classList.remove('is-active'), 150);
      }

      await waitForFrame();

      try {
        const longestEdge = Math.max(video.videoWidth, video.videoHeight);
        const scale = Math.min(1, MAX_EDGE / longestEdge);
        let blob = await renderFrame(scale, 0.92);

        if (blob && blob.size > megabytes(9.5)) {
          const smaller = Math.min(scale, 2200 / longestEdge);
          blob = await renderFrame(smaller, 0.8);
        }

        if (!blob) {
          showError('Não foi possível gerar a imagem. Tente novamente.');
          return;
        }

        if (blob.size > MAX_BYTES) {
          showError('A foto ficou muito grande. Aproxime-se um pouco e capture novamente.');
          return;
        }

        const file = new File([blob], 'pagina-' + Date.now() + '.jpg', { type: 'image/jpeg' });
        addPages([file]);
      } catch (error) {
        showError('Falha ao capturar a página. Tente novamente.');
      } finally {
        if (captureBtn) captureBtn.disabled = !stream;
      }
    };

    openButtons.forEach((button) => button.addEventListener('click', () => startCamera(false)));
    if (stopBtn) stopBtn.addEventListener('click', () => {
      stopCamera();
      hideError();
    });
    if (flipBtn) flipBtn.addEventListener('click', flipCamera);
    if (captureBtn) captureBtn.addEventListener('click', capturePage);

    if (filesInput) {
      filesInput.addEventListener('change', () => {
        addPages(filesInput.files);
        filesInput.value = '';
      });
    }

    if (clearBtn) clearBtn.addEventListener('click', clearPages);

    if (resetBtn) {
      resetBtn.addEventListener('click', () => {
        window.setTimeout(() => {
          clearPages();
          hideError();
        }, 0);
      });
    }

    video.addEventListener('resize', updateOrientation);
    video.addEventListener('loadedmetadata', updateOrientation);

    form.addEventListener('submit', (event) => {
      if (pages.length === 0) {
        event.preventDefault();
        showError('Capture ou selecione ao menos uma imagem antes de salvar.');
        return;
      }

      if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
        return;
      }

      submitting = true;
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Salvando…';
      }
    });

    window.addEventListener('beforeunload', (event) => {
      if (pages.length === 0 || submitting) return;
      event.preventDefault();
      event.returnValue = '';
    });

    window.addEventListener('pagehide', stopTracks);

    // Prévia das páginas já salvas na galeria.
    const dialog = document.querySelector('[data-scan-page-dialog]');
    const dialogImage = document.querySelector('[data-scan-page-dialog-image]');
    const dialogTitle = document.querySelector('[data-scan-page-dialog-title]');
    const dialogClose = document.querySelector('[data-scan-page-dialog-close]');

    document.querySelectorAll('[data-scan-page-preview]').forEach((button) => {
      button.addEventListener('click', () => {
        const url = button.getAttribute('data-page-url');
        if (!url) return;

        if (dialog && dialogImage && typeof dialog.showModal === 'function') {
          dialogImage.src = url;
          dialogImage.alt = button.getAttribute('data-page-label') || 'Página digitalizada';
          if (dialogTitle) dialogTitle.textContent = button.getAttribute('data-page-label') || 'Página digitalizada';
          dialog.showModal();
          return;
        }

        window.open(url, '_blank', 'noopener');
      });
    });

    if (dialog) {
      dialog.addEventListener('click', (event) => {
        if (event.target === dialog) dialog.close();
      });
    }

    if (dialogClose && dialog) {
      dialogClose.addEventListener('click', () => dialog.close());
    }

    // Estado inicial: câmera desligada e sem páginas.
    if (placeholder) placeholder.hidden = false;
    stopCamera();
    renderPages();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
