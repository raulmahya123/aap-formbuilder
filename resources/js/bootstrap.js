import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(response => response, error => {
    if (!error.config?.suppressFeedback) {
        const status = error.response?.status;
        const message = !error.response ? 'Koneksi terputus. Periksa koneksi internet, lalu coba lagi.'
            : status === 401 || status === 419 ? 'Sesi telah berakhir. Masuk kembali untuk melanjutkan.'
            : status === 403 ? 'Anda tidak memiliki izin untuk menjalankan tindakan ini.'
            : status === 404 ? 'Data yang dicari sudah tidak tersedia.'
            : status === 422 ? 'Periksa kembali isian yang ditandai sebelum menyimpan.'
            : 'Layanan sedang bermasalah. Silakan coba beberapa saat lagi.';
        window.dispatchEvent(new CustomEvent('ui:toast', { detail: { type: 'error', message } }));
    }
    return Promise.reject(error);
});
