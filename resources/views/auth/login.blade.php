<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - AdminPro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
</head>
<body>
    <div class="auth-wrapper">
        <!-- Left Side - Branding -->
        <div class="auth-branding">
            <div class="branding-content">
                <div class="logo-wrapper">
                    <div class="logo-icon">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h1 class="brand-name">GestionStock</h1>
                </div>
                <p class="brand-tagline">Gérez votre entreprise avec efficacité</p>

                <div class="features-list">
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Dashboard intuitif</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Analyses en temps réel</span>
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-check-circle"></i>
                        <span>Sécurité renforcée</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Login Form -->
        <div class="auth-form-container">
            <div class="auth-form-wrapper">
                <!-- Mobile Logo -->
                <div class="mobile-logo">
                    <div class="logo-icon-mobile">
                        <i class="fas fa-bolt"></i>
                    </div>
                    <h2>GestionStock</h2>
                </div>

                <div class="auth-card">
                    <div class="form-header text-center">
                        <h2 class="form-title">Bon retour 👋</h2>
                        <p class="form-subtitle">Connectez-vous à votre compte</p>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="auth-form">
                        @csrf

                        <!-- Téléphone -->
                        <div class="form-group">
                            <label class="form-label">Téléphone</label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-phone input-icon"></i>
                                <input
                                    type="text"
                                    name="telephone"
                                    value="{{ old('telephone') }}"
                                    class="form-control @error('telephone') is-invalid @enderror"
                                    required
                                >
                            </div>

                            @error('telephone')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label class="form-label">Mot de passe</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    required
                                >
                                <button type="button" class="toggle-password" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>

                            @error('password')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="text-end mb-3">
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Mot de passe oublié ?
                            </a>
                        </div>

                        <button type="submit" class="btn btn-primary btn-auth">
                            Se connecter
                        </button>

                        <div class="register-link">
                            Pas encore de compte ? Appelez votre administrateur
                        </div>
                    </form>

                </div>

            </div>

            <!-- Footer -->
            <div class="auth-footer">
                <p>&copy; 2024 AdminPro. Tous droits réservés.</p>
                <div class="footer-links">
                    <a href="#">Conditions d'utilisation</a>
                    <span>•</span>
                    <a href="#">Politique de confidentialité</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/auth.js') }}"></script>
</body>
</html>
