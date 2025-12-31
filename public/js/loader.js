class AppLoader {
    constructor() {
        this.loader = document.getElementById('app-loader');
        this.content = document.getElementById('app-content');
        this.loaderText = document.getElementById('loader-text');
        this.progressBar = document.getElementById('loader-progress');
        this.startTime = Date.now();
        this.minLoadTime = 800; // Minimum load time in ms
        this.slowThreshold = 3000; // Slow connection threshold
        this.timeoutId = null;

        this.init();
    }

    init() {
        // Set initial progress
        this.updateProgress(10);
        this.updateText('Chargement des ressources...');
        // Handle page load
        window.addEventListener('load', () => this.handleLoadComplete());
        // Handle Livewire navigation
        this.setupLivewireEvents();
        // Handle browser navigation
        this.setupNavigationEvents();
        // Simulate progress for better UX
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
        let progress = 10;
        const interval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 10;
                this.updateProgress(Math.min(progress, 90));
            }
        }, 200);

        // Store interval ID for cleanup
        this.progressInterval = interval;
    }

    handleLoadComplete() {
        const elapsed = Date.now() - this.startTime;
        const remainingTime = Math.max(0, this.minLoadTime - elapsed);

        // Ensure minimum load time for smooth UX
        setTimeout(() => {
            this.completeLoading();
        }, remainingTime);
    }

    completeLoading() {
        // Clear progress simulation
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        // Complete progress bar
        this.updateProgress(100);
        this.updateText('Prêt !');

        // Hide loader and show content
        setTimeout(() => {
            this.loader.classList.add('loader-hidden');
            this.content.classList.remove('loader-hidden');
            // Dispatch custom event
            document.dispatchEvent(new CustomEvent('app-loaded'));
        }, 300);
    }

    setupLivewireEvents() {
        // Show loader before Livewire navigation
        document.addEventListener('livewire:navigate', () => {
            this.showLoader('Navigation en cours...');
        });
        // Hide loader after Livewire navigation
        document.addEventListener('livewire:navigated', () => {
            setTimeout(() => this.hideLoader(), 300);
        });
        // Show loader on Livewire requests
        document.addEventListener('livewire:request-start', () => {
            this.showLoader('Traitement en cours...');
        });
        // Hide loader after Livewire requests
        document.addEventListener('livewire:request-finished', () => {
            setTimeout(() => this.hideLoader(), 200);
        });
        // Handle Livewire errors
        document.addEventListener('livewire:load-error', () => {
            this.showLoader('Erreur de chargement, nouvelle tentative...');
        });
    }

    setupNavigationEvents() {
        // Show loader before page unload (traditional navigation)
        window.addEventListener('beforeunload', () => {
            this.showLoader('Chargement de la page...');
        });
        // Handle browser back/forward
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                this.showLoader('Restoration de la page...');
                setTimeout(() => this.hideLoader(), 300);
            }
        });
    }

    showLoader(text = 'Chargement...') {
        this.loader.classList.remove('loader-hidden');
        this.content.classList.add('loader-hidden');
        this.updateText(text);
        this.updateProgress(30);
        this.startProgressSimulation();
    }

    hideLoader() {
        this.updateProgress(100);
        this.updateText('Terminé !');
        setTimeout(() => {
            this.loader.classList.add('loader-hidden');
            this.content.classList.remove('loader-hidden');
        }, 300);
    }

    startProgressSimulation() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }

        let progress = 30;
        this.progressInterval = setInterval(() => {
            if (progress < 90) {
                progress += Math.random() * 5;
                this.updateProgress(Math.min(progress, 90));
            }
        }, 200);
    }

    checkSlowConnection() {
        this.timeoutId = setTimeout(() => {
            this.loaderText.classList.add('loader-slow');
            this.updateText('Connexion lente, veuillez patienter...');
        }, this.slowThreshold);
    }

    cleanup() {
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
        }
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
    }
}

// Initialize loader when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.appLoader = new AppLoader();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.appLoader) {
        window.appLoader.cleanup();
    }
});
