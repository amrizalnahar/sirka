import './rsa-encryptor';

/* ------------------------------------------------------------------
 * Livewire navigate cleanup — fix "klik pertama tidak responsif"
 * ------------------------------------------------------------------ */
document.addEventListener('livewire:navigated', () => {
    // Re-inisialisasi Alpine pada elemen yang persistent di layout
    // (contoh: dropdown user di topbar yang pakai x-data)
    document.querySelectorAll('[x-data]').forEach((el) => {
        if (el._x_dataStack) {
            el.removeAttribute('x-data');
            el.setAttribute('x-data', el.getAttribute('x-data'));
        }
    });

    // Scroll ke atas setelah navigasi (opsional, tapi UX lebih baik)
    window.scrollTo({ top: 0, behavior: 'instant' });
});
