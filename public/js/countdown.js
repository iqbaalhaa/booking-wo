/*
 * Hitung mundur ke hari H: <div data-countdown="2027-02-18T00:00:00+07:00"> berisi [data-unit=days|hours|minutes].
 */
document.querySelectorAll('[data-countdown]').forEach((element) => {
    const target = new Date(element.dataset.countdown).getTime();
    const units = {
        days: element.querySelector('[data-unit=days]'),
        hours: element.querySelector('[data-unit=hours]'),
        minutes: element.querySelector('[data-unit=minutes]'),
    };

    const update = () => {
        const totalMinutes = Math.max(0, Math.floor((target - Date.now()) / 60000));
        units.days.textContent = Math.floor(totalMinutes / 1440);
        units.hours.textContent = Math.floor((totalMinutes % 1440) / 60);
        units.minutes.textContent = totalMinutes % 60;
    };

    update();
    setInterval(update, 30000);
});
