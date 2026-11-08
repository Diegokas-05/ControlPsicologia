document.body.addEventListener('htmx:beforeSwap', function (evento) {
    if (evento.detail.target?.id === 'resumen-go' && evento.detail.xhr.status === 503) {
        evento.detail.shouldSwap = true;
        evento.detail.isError = false;
    }
});