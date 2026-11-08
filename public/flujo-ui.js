document.body.addEventListener('htmx:beforeSwap', (evento) => {
    const destino = evento.detail.target?.id;
    const codigo = evento.detail.xhr.status;

    if (['mensaje-flujo', 'bandeja-flujo'].includes(destino) 
        && [403, 404, 409, 422, 503].includes(codigo)) {
        evento.detail.shouldSwap = true;
    }
});