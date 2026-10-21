/*
 * Pratinjau gambar sebelum diunggah.
 * <input type="file" data-preview="#preview-id"> + <img id="preview-id">
 * File non-gambar (mis. PDF) hanya menampilkan nama file di elemen [data-preview-name], jika ada.
 */
document.querySelectorAll('input[type=file][data-preview]').forEach((input) => {
    const preview = document.querySelector(input.dataset.preview);
    const nameLabel = document.querySelector(input.dataset.previewName || null);
    const original = preview?.getAttribute('src');
    let objectUrl;

    input.addEventListener('change', () => {
        const file = input.files[0];
        if (objectUrl) URL.revokeObjectURL(objectUrl);

        if (file && file.type.startsWith('image/')) {
            objectUrl = URL.createObjectURL(file);
            preview.src = objectUrl;
            preview.hidden = false;
        } else if (preview) {
            preview.src = original || '';
            preview.hidden = !original;
        }

        if (nameLabel) nameLabel.textContent = file ? file.name : '';
    });
});
