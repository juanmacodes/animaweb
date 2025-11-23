document.addEventListener('DOMContentLoaded', () => {
    let deferredPrompt;
    const installBtn = document.createElement('button');
    installBtn.id = 'pwa-install-btn';
    installBtn.innerText = '📲 INSTALL APP';
    installBtn.style.display = 'none';
    installBtn.style.position = 'fixed';
    installBtn.style.bottom = '80px'; // Above chat
    installBtn.style.right = '20px';
    installBtn.style.zIndex = '9998';
    installBtn.style.background = '#00F0FF';
    installBtn.style.color = '#000';
    installBtn.style.border = 'none';
    installBtn.style.padding = '10px 15px';
    installBtn.style.fontWeight = 'bold';
    installBtn.style.borderRadius = '5px';
    installBtn.style.cursor = 'pointer';
    installBtn.style.boxShadow = '0 0 10px rgba(0, 240, 255, 0.5)';

    document.body.appendChild(installBtn);

    // 1. Handle Install Prompt
    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;
        installBtn.style.display = 'block';
    });

    installBtn.addEventListener('click', async () => {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            if (outcome === 'accepted') {
                installBtn.style.display = 'none';
            }
            deferredPrompt = null;
        }
    });

    // 2. Handle Push Notifications
    if ('Notification' in window && 'serviceWorker' in navigator) {
        const notifyBtn = document.createElement('button');
        notifyBtn.id = 'pwa-notify-btn';
        notifyBtn.innerText = '🔔 ENABLE ALERTS';
        notifyBtn.style.position = 'fixed';
        notifyBtn.style.bottom = '130px';
        notifyBtn.style.right = '20px';
        notifyBtn.style.zIndex = '9998';
        notifyBtn.style.background = '#FF0055';
        notifyBtn.style.color = '#fff';
        notifyBtn.style.border = 'none';
        notifyBtn.style.padding = '10px 15px';
        notifyBtn.style.fontWeight = 'bold';
        notifyBtn.style.borderRadius = '5px';
        notifyBtn.style.cursor = 'pointer';
        notifyBtn.style.boxShadow = '0 0 10px rgba(255, 0, 85, 0.5)';

        if (Notification.permission === 'default') {
            document.body.appendChild(notifyBtn);
        }

        notifyBtn.addEventListener('click', () => {
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    notifyBtn.style.display = 'none';
                    new Notification("Anima System", {
                        body: "Neural Link Established. You will now receive updates.",
                        icon: "https://placehold.co/192x192/00F0FF/000000?text=ANIMA"
                    });
                    // Here we would subscribe via PushManager and send endpoint to backend
                    // subscribeUserToPush(); 
                }
            });
        });
    }
});
