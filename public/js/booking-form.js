/*
 * Form booking: ringkasan harga real-time, batas jumlah tamu per paket,
 * dan status ketersediaan tanggal dari GET /api/availability?month=YYYY-MM.
 * Semua harga dibaca dari data-* yang dirender Blade; server tetap menghitung ulang.
 */
const form = document.getElementById('booking-form');

if (form) {
    const rupiah = (value) => 'Rp ' + Math.round(value).toLocaleString('id-ID');
    const messages = JSON.parse(form.dataset.messages);
    const dpPercentage = Number(form.dataset.dpPercentage);

    const packageSelect = form.querySelector('#package_id');
    const guestInput = form.querySelector('#guest_count');
    const guestHelp = form.querySelector('#guest-help');
    const dateInput = form.querySelector('#event_date');
    const dateStatus = form.querySelector('#date-status');
    const addonRows = [...form.querySelectorAll('[data-addon]')];

    const summary = {
        packageName: document.getElementById('summary-package-name'),
        packagePrice: document.getElementById('summary-package-price'),
        addons: document.getElementById('summary-addons'),
        total: document.getElementById('summary-total'),
        dp: document.getElementById('summary-dp'),
    };

    // ---- Ringkasan harga ----
    const updateSummary = () => {
        const option = packageSelect.selectedOptions[0];
        const packagePrice = Number(option.dataset.price || 0);

        summary.packageName.textContent = option.dataset.name ? `Paket ${option.dataset.name}` : 'Paket';
        summary.packagePrice.textContent = option.value ? rupiah(packagePrice) : '-';

        summary.addons.replaceChildren();
        let addonsTotal = 0;

        addonRows.forEach((row) => {
            const checkbox = row.querySelector('input[type=checkbox]');
            const qtyInput = row.querySelector('input[type=number]');
            qtyInput.disabled = !checkbox.checked;
            if (!checkbox.checked) return;

            const qty = Math.max(1, Number(qtyInput.value) || 1);
            const subtotal = Number(row.dataset.price) * qty;
            addonsTotal += subtotal;

            const dt = document.createElement('dt');
            dt.textContent = qty > 1 ? `${row.dataset.name} × ${qty}` : row.dataset.name;
            const dd = document.createElement('dd');
            dd.textContent = rupiah(subtotal);
            summary.addons.append(dt, dd);
        });

        const total = packagePrice + addonsTotal;
        summary.total.textContent = rupiah(total);
        summary.dp.textContent = rupiah(Math.ceil(total * dpPercentage / 100));
    };

    // ---- Batas jumlah tamu sesuai paket ----
    const updateGuestLimit = () => {
        const max = packageSelect.selectedOptions[0].dataset.maxGuests;
        if (max) {
            guestInput.max = max;
            guestHelp.textContent = `Maksimal ${Number(max).toLocaleString('id-ID')} tamu untuk paket ini.`;
        } else {
            guestInput.removeAttribute('max');
            guestHelp.textContent = '';
        }
    };

    // ---- Status tanggal ----
    const monthCache = new Map();

    const fetchMonth = async (month) => {
        if (!monthCache.has(month)) {
            const url = `${form.dataset.availabilityUrl}?month=${month}`;
            monthCache.set(month, fetch(url, { headers: { Accept: 'application/json' } }).then((response) => {
                if (!response.ok) throw new Error(response.statusText);
                return response.json();
            }));
        }
        return monthCache.get(month);
    };

    const statusClass = {
        available: 'text-success',
        pending: 'text-warning-emphasis',
        booked: 'text-danger',
        blocked: 'text-danger',
        past: 'text-danger',
    };

    const showDateStatus = async () => {
        const value = dateInput.value; // YYYY-MM-DD
        dateStatus.className = 'date-status';
        dateStatus.textContent = '';
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return;

        dateStatus.textContent = 'Memeriksa ketersediaan…';
        try {
            const data = await fetchMonth(value.slice(0, 7));
            if (dateInput.value !== value) return; // pengguna sudah mengganti tanggal
            const status = data.dates[value];
            dateStatus.textContent = messages[status] ?? '';
            dateStatus.classList.add(statusClass[status] ?? '');
        } catch {
            dateStatus.textContent = 'Ketersediaan tanggal belum bisa diperiksa. Tanggal tetap dicek saat booking dikirim.';
        }
    };

    packageSelect.addEventListener('change', () => { updateGuestLimit(); updateSummary(); });
    addonRows.forEach((row) => row.addEventListener('input', updateSummary));
    addonRows.forEach((row) => row.addEventListener('change', updateSummary));
    dateInput.addEventListener('change', showDateStatus);

    updateGuestLimit();
    updateSummary();
    showDateStatus();
}
