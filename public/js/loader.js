class AppLoader {
    constructor() {
        this.loader = document.getElementById('app-loader');
        this.loaderText = document.getElementById('loader-text');
        this.progressBar = document.getElementById('loader-progress');
        this.startTime = Date.now();
        this.minLoadTime = 800;
        this.slowThreshold = 3000;
        this.timeoutId = null;
        this.progressInterval = null;

        // Debug logging
        console.log('AppLoader initialized. Elements:', {
            loader: !!this.loader,
            loaderText: !!this.loaderText,
            progressBar: !!this.progressBar
        });

        // If no loader element, exit silently
        if (!this.loader) {
            console.log('No app-loader found, skipping loader initialization');
            return;
        }

        this.init();
    }

    init() {
        // Show loader immediately
        this.loader.classList.remove('loader-hidden');

        // Set initial progress
        this.updateProgress(10);
        this.updateText('Chargement des ressources...');

        // Handle page load
        if (document.readyState === 'complete') {
            // Page already loaded
            setTimeout(() => this.handleLoadComplete(), 100);
        } else {
            window.addEventListener('load', () => this.handleLoadComplete(), { once: true });
        }

        // Setup Livewire events if Livewire is detected
        if (typeof Livewire !== 'undefined') {
            this.setupLivewireEvents();
        }

        // Simulate progress
        this.simulateProgress();

        // Check for slow connection
        this.checkSlowConnection();
    }

    updateProgress(percentage) {
        if (this.progressBar) {
            this.progressBar.style.width = `${percentage}%`;
        }
    }

    updateText(text) {
        if (this.loaderText) {
            this.loaderText.textContent = text;
        }
    }

    simulateProgress() {
        // Clear any existing interval
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        let progress = 10;
        this.progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 10;
                this.updateProgress(Math.min(progress, 90));
            }
        }, 200);
    }

    handleLoadComplete() {
        const elapsed = Date.now() - this.startTime;
        const remainingTime = Math.max(0, this.minLoadTime - elapsed);

        setTimeout(() => {
            this.completeLoading();
        }, remainingTime);
    }

    completeLoading() {
        // Clear progress simulation
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        // Complete progress bar
        this.updateProgress(100);
        this.updateText('Prêt !');

        // Hide loader
        setTimeout(() => {
            this.hideLoader();
            document.dispatchEvent(new CustomEvent('app-loaded'));
        }, 300);
    }

    setupLivewireEvents() {
        console.log('Setting up Livewire events');

        // Use debouncing to prevent multiple rapid calls
        let showLoaderTimeout;
        let hideLoaderTimeout;

        const debouncedShow = (text) => {
            clearTimeout(showLoaderTimeout);
            showLoaderTimeout = setTimeout(() => this.showLoader(text), 50);
        };

        const debouncedHide = () => {
            clearTimeout(hideLoaderTimeout);
            hideLoaderTimeout = setTimeout(() => this.hideLoader(), 50);
        };

        document.addEventListener('livewire:navigate', () => {
            debouncedShow('Navigation en cours...');
        });

        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => debouncedHide(), 300);
        });

        document.addEventListener('livewire:request-start', () => {
            debouncedShow('Traitement en cours...');
        });

        document.addEventListener('livewire:request-finished', () => {
            setTimeout(() => debouncedHide(), 200);
        });

        document.addEventListener('livewire:load-error', () => {
            debouncedShow('Erreur de chargement, nouvelle tentative...');
        });
    }

    showLoader(text = 'Chargement...') {
        const loader = this.getLoader();
        if (!loader) return;

        this.loader = loader;

        loader.classList.remove('loader-hidden');
        this.updateText(text);
        this.updateProgress(30);

        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        this.simulateProgress();
    }

    hideLoader() {
        const loader = this.getLoader();
        if (!loader) return;

        this.loader = loader;

        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        this.updateProgress(100);
        this.updateText('Terminé !');

        setTimeout(() => {
            const freshLoader = this.getLoader();
            if (freshLoader) {
                freshLoader.classList.add('loader-hidden');
            }
        }, 300);
    }

    checkSlowConnection() {
        // Clear existing timeout
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }

        this.timeoutId = setTimeout(() => {
            if (this.loaderText) {
                this.loaderText.classList.add('loader-slow');
            }
            this.updateText('Connexion lente, veuillez patienter...');
        }, this.slowThreshold);
    }

    cleanup() {
        // Clear intervals
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        // Clear timeouts
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
    }
}

// Simple initialization
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOMContentLoaded - Initializing AppLoader');
    window.appLoader = new AppLoader();
});

// Fallback to ensure loader is hidden if something goes wrong
window.addEventListener('load', () => {
    setTimeout(() => {
        const loader = document.getElementById('app-loader');
        if (loader && !loader.classList.contains('loader-hidden')) {
            console.log('Fallback: Hiding loader after timeout');
            loader.classList.add('loader-hidden');
        }
    }, 5000); // 5 second timeout
});

// Cleanup
window.addEventListener('beforeunload', () => {
    if (window.appLoader && window.appLoader.cleanup) {
        window.appLoader.cleanup();
    }
});
