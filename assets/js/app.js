'use strict';

// Ponto de entrada para scripts JavaScript da aplicação.
document.querySelectorAll('[data-open-on-load="true"]').forEach((element) => {
    bootstrap.Modal.getOrCreateInstance(element).show();
});
