// Check compatibility for the browser we're running this in
if ("serviceWorker" in navigator) {
    if (navigator.serviceWorker.controller) {
        console.log("[PWA Builder] active service worker found, no need to register");
    } else {
        // Register the service worker
        navigator.serviceWorker
            .register(window.serviceWorkerWithCacheBuster, {
                scope: "./"
            })
            .then(function (reg) {
                console.log("[PWA Builder] Service worker has been registered for scope: " + reg.scope);
            });
    }
}

let deferredPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    // Prevent Chrome 67 and earlier from automatically showing the prompt
    e.preventDefault();
    // Stash the event so it can be triggered later.
    deferredPrompt = e;
});

async function installPwa () {
    if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choiceResult) {

            if (choiceResult.outcome === 'accepted') {
                console.log('Your PWA has been installed');
            } else {
                console.log('User chose to not install your PWA');
            }

            deferredPrompt = null;

        });
    }
}
