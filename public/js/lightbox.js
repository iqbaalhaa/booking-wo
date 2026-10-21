/*
 * Lightbox portofolio: modal Bootstrap berisi carousel.
 * Tombol pemicu: data-bs-toggle="modal" data-bs-target="#lightbox" data-slide="<index>".
 */
const lightbox = document.getElementById('lightbox');

if (lightbox) {
    lightbox.addEventListener('show.bs.modal', (event) => {
        const index = Number(event.relatedTarget?.dataset.slide || 0);

        // Set slide aktif langsung (tanpa animasi) karena carousel masih tersembunyi
        lightbox.querySelectorAll('.carousel-item').forEach((item, i) => {
            item.classList.toggle('active', i === index);
        });
    });
}
