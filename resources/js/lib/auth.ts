export function openGoogleLoginPopup(url: string): Promise<string | undefined> {
    return new Promise((resolve, reject) => {
        const width = 500;
        const height = 650;
        const left = window.screenX + (window.outerWidth - width) / 2;
        const top = window.screenY + (window.outerHeight - height) / 2;

        const popup = window.open(
            'about:blank',
            'GoogleLogin',
            `width=${width},height=${height},left=${left},top=${top},status=no,resizable=yes,scrollbars=yes`
        );

        if (!popup) {
            reject(new Error('Popup diblokir oleh browser.'));

            return;
        }

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        fetch('/auth/google/popup-session', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            },
        })
        .then(() => {
            popup.location.href = url;
        })
        .catch((err) => {
            popup.close();
            reject(err);
        });

        const handleMessage = (event: MessageEvent) => {
            if (event.origin !== window.location.origin) {
                return;
            }

            if (event.data?.type === 'GOOGLE_AUTH_SUCCESS') {
                window.removeEventListener('message', handleMessage);
                resolve(event.data.url);
            } else if (event.data?.type === 'GOOGLE_AUTH_ERROR') {
                window.removeEventListener('message', handleMessage);
                reject(new Error(event.data.message || 'Login gagal'));
            }
        };

        window.addEventListener('message', handleMessage);

        const checkClosed = setInterval(() => {
            if (popup.closed) {
                clearInterval(checkClosed);
                window.removeEventListener('message', handleMessage);
                reject(new Error('Login dibatalan oleh pengguna.'));
            }
        }, 1000);
    });
}
