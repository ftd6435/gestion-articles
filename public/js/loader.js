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

        // Check if loader exists
        if (!this.loader) {
            console.error('AppLoader: app-loader element not found');
            return;
        }

        this.init();
    }

    init() {
        // Set initial progress
        this.updateProgress(10);
        this.updateText('Chargement des ressources...');

        // Handle page load
        if (document.readyState === 'complete') {
            // Page already loaded, trigger immediately
            setTimeout(() => this.handleLoadComplete(), 100);
        } else {
            window.addEventListener('load', () => this.handleLoadComplete(), { once: true });
        }

        // Handle Livewire navigation
        this.setupLivewireEvents();
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

        // Ensure minimum load time for smooth UX
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

        // Hide loader only (no content to show)
        setTimeout(() => {
            if (this.loader) {
                this.loader.classList.add('loader-hidden');
            }
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

    showLoader(text = 'Chargement...') {
        // Check if loader exists
        if (!this.loader) {
            console.warn('AppLoader: Cannot show loader - element not found');
            return;
        }

        try {
            this.loader.classList.remove('loader-hidden');
            this.updateText(text);
            this.updateProgress(30);

            // Restart progress simulation
            if (this.progressInterval) {
                clearInterval(this.progressInterval);
                this.progressInterval = null;
            }
            this.simulateProgress();
        } catch (error) {
            console.error('AppLoader: Error in showLoader', error);
        }
    }

    hideLoader() {
        // Clear progress simulation
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        this.updateProgress(100);
        this.updateText('Terminé !');

        setTimeout(() => {
            if (this.loader) {
                try {
                    this.loader.classList.add('loader-hidden');
                } catch (error) {
                    console.error('AppLoader: Error in hideLoader', error);
                }
            }
        }, 300);
    }

    checkSlowConnection() {
        // Clear existing timeout
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
        }

        this.timeoutId = setTimeout(() => {
            if (this.loaderText) {
                try {
                    this.loaderText.classList.add('loader-slow');
                } catch (error) {
                    console.warn('AppLoader: Could not add loader-slow class', error);
                }
            }
            this.updateText('Connexion lente, veuillez patienter...');
        }, this.slowThreshold);
    }

    cleanup() {
        // Clear intervals
        if (this.progressInterval) {
            clearInterval(this.progressInterval);
            this.progressInterval = null;
        }

        // Clear timeouts
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
            this.timeoutId = null;
        }
    }
}

// Initialize loader safely
function initializeLoader() {
    // Wait a bit to ensure DOM is ready
    setTimeout(() => {
        try {
            window.appLoader = new AppLoader();
            console.log('AppLoader initialized');
        } catch (error) {
            console.error('Failed to initialize AppLoader:', error);
            // Fallback: hide loader if it exists
            const loader = document.getElementById('app-loader');
            if (loader) {
                loader.style.display = 'none';
                loader.classList.add('loader-hidden');
            }
        }
    }, 100);
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeLoader);
} else {
    // DOM already loaded
    initializeLoader();
}

// Fallback: make sure loader is hidden after 5 seconds max
window.addEventListener('load', () => {
    setTimeout(() => {
        const loader = document.getElementById('app-loader');
        if (loader) {
            loader.style.display = 'none';
            loader.classList.add('loader-hidden');
        }
    }, 5000);
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.appLoader) {
        window.appLoader.cleanup();
    }
});
