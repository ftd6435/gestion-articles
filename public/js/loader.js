class AppLoader {
    constructor() {
        this.startTime = Date.now();
        this.minLoadTime = 800;
        this.slowThreshold = 3000;

        this.progressInterval = null;
        this.timeoutId = null;

        console.log('AppLoader initialized');

        if (!this.getLoader()) {
            console.warn('AppLoader: #app-loader not found, aborting.');
            return;
        }

        this.init();
    }

    /* ----------------------------------------
     * Core helpers
     * ------------------------------------- */

    getLoader() {
        return document.getElementById('app-loader');
    }

    getLoaderText() {
        return document.getElementById('loader-text');
    }

    getProgressBar() {
        return document.getElementById('loader-progress');
    }

    /* ----------------------------------------
     * Init
     * ------------------------------------- */

    init() {
        this.showLoader('Chargement des ressources...');
        this.updateProgress(10);

        if (document.readyState === 'complete') {
            setTimeout(() => this.handleLoadComplete(), 100);
        } else {
            window.addEventListener('load', () => this.handleLoadComplete(), { once: true });
        }

        if (typeof Livewire !== 'undefined') {
            this.setupLivewireEvents();
        }

        this.simulateProgress();
        this.checkSlowConnection();
    }

    /* ----------------------------------------
     * UI updates
     * ------------------------------------- */

    updateProgress(percent) {
        const bar = this.getProgressBar();
        if (bar) {
            bar.style.width = `${percent}%`;
        }
    }

    updateText(text) {
        const el = this.getLoaderText();
        if (el) {
            el.textContent = text;
        }
    }

    /* ----------------------------------------
     * Progress simulation
     * ------------------------------------- */

    simulateProgress() {
        this.clearProgress();

        let progress = 10;
        this.progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 8;
                this.updateProgress(Math.min(progress, 90));
            }
        }, 200);
    }

    clearProgress() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }
    }

    /* ----------------------------------------
     * Load completion
     * ------------------------------------- */

    handleLoadComplete() {
        const elapsed = Date.now() - this.startTime;
        const delay = Math.max(0, this.minLoadTime - elapsed);

        setTimeout(() => this.completeLoading(), delay);
    }

    completeLoading() {
        this.clearProgress();
        this.updateProgress(100);
        this.updateText('Prêt !');

        setTimeout(() => {
            this.hideLoader();
            document.dispatchEvent(new CustomEvent('app-loaded'));
        }, 300);
    }

    /* ----------------------------------------
     * Show / Hide
     * ------------------------------------- */

    showLoader(text = 'Chargement...') {
        const loader = this.getLoader();
        if (!loader) return;

        loader.classList.remove('loader-hidden');
        this.updateText(text);
        this.updateProgress(30);
        this.simulateProgress();
    }

    hideLoader() {
        const loader = this.getLoader();
        if (!loader) return;

        this.clearProgress();
        this.updateProgress(100);

        setTimeout(() => {
            const freshLoader = this.getLoader();
            if (freshLoader) {
                freshLoader.classList.add('loader-hidden');
            }
        }, 300);
    }

    /* ----------------------------------------
     * Livewire integration
     * ------------------------------------- */

    setupLivewireEvents() {
        console.log('AppLoader: Livewire events attached');

        let showTimeout;
        let hideTimeout;

        const debounceShow = (text) => {
            clearTimeout(showTimeout);
            showTimeout = setTimeout(() => this.showLoader(text), 50);
        };

        const debounceHide = () => {
            clearTimeout(hideTimeout);
            hideTimeout = setTimeout(() => this.hideLoader(), 150);
        };

        document.addEventListener('livewire:navigate', () => {
            debounceShow('Navigation en cours...');
        });

        document.addEventListener('livewire:navigated', () => {
            debounceHide();
        });

        document.addEventListener('livewire:request-start', () => {
            debounceShow('Traitement en cours...');
        });

        document.addEventListener('livewire:request-finished', () => {
            debounceHide();
        });

        document.addEventListener('livewire:load-error', () => {
            debounceShow('Erreur de chargement...');
        });
    }

    /* ----------------------------------------
     * Slow connection detection
     * ------------------------------------- */

    checkSlowConnection() {
        clearTimeout(this.timeoutId);

        this.timeoutId = setTimeout(() => {
            const text = this.getLoaderText();
            if (text) {
                text.classList.add('loader-slow');
                text.textContent = 'Connexion lente, veuillez patienter...';
            }
        }, this.slowThreshold);
    }

    /* ----------------------------------------
     * Cleanup
     * ------------------------------------- */

    cleanup() {
        this.clearProgress();
        clearTimeout(this.timeoutId);
    }
}

/* ----------------------------------------
 * Boot
 * ------------------------------------- */

document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded → AppLoader boot');
    window.appLoader = new AppLoader();

    // ✅ Mobile safety hide
    setTimeout(() => {
        const loader = document.getElementById('app-loader');
        if (loader) {
            loader.classList.add('loader-hidden');
        }
    }, 2500);
});

/* ----------------------------------------
 * Failsafe
 * ------------------------------------- */

window.addEventListener('load', () => {
    setTimeout(() => {
        const loader = document.getElementById('app-loader');
        if (loader && !loader.classList.contains('loader-hidden')) {
            console.warn('AppLoader fallback hide');
            loader.classList.add('loader-hidden');
        }
    }, 5000);
});

window.addEventListener('beforeunload', () => {
    if (window.appLoader) {
        window.appLoader.cleanup();
    }
});
