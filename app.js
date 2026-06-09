document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!form.matches('[data-confirm]')) {
        return;
    }

    if (!confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});

