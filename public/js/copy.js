/*
 * Tombol salin: <button data-copy="1234567890">Salin</button>
 * Label tombol berubah sementara menjadi "Tersalin" (diumumkan ke pembaca layar lewat aria-live).
 */
document.querySelectorAll('[data-copy]').forEach((button) => {
    const label = button.querySelector('[data-copy-label]') || button;
    const original = label.textContent;

    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copy);
            label.textContent = 'Tersalin';
        } catch {
            label.textContent = 'Salin manual';
        }
        setTimeout(() => { label.textContent = original; }, 2000);
    });
});
