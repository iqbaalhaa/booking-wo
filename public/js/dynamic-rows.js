/*
 * Baris input dinamis (tambah/hapus), mis. fitur paket.
 *
 * <div data-dynamic-rows>
 *   <div data-rows> ...baris... </div>
 *   <template> satu baris kosong </template>
 *   <button type="button" data-add-row>Tambah</button>
 * </div>
 * Setiap baris punya tombol [data-remove-row]. Minimal satu baris selalu tersisa.
 */
document.querySelectorAll('[data-dynamic-rows]').forEach((wrapper) => {
    const rows = wrapper.querySelector('[data-rows]');
    const template = wrapper.querySelector('template');

    const addRow = () => {
        rows.append(template.content.cloneNode(true));
        rows.lastElementChild.querySelector('input, textarea, select')?.focus();
    };

    wrapper.querySelector('[data-add-row]').addEventListener('click', addRow);

    rows.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-row]');
        if (!button) return;

        const row = button.closest('[data-row]');
        if (rows.children.length > 1) {
            row.remove();
        } else {
            row.querySelectorAll('input, textarea').forEach((input) => { input.value = ''; });
        }
    });

    // Enter di baris terakhir menambah baris baru, bukan mengirim form
    rows.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && event.target.matches('input')) {
            event.preventDefault();
            if (event.target.closest('[data-row]') === rows.lastElementChild) addRow();
        }
    });
});
