<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $company = \App\Models\CompanySetting::query()->first();
        $brandName = $company?->short_name ?: ($company?->name ?: config('app.name'));
        $brandName = \Illuminate\Support\Str::limit($brandName, 22);
        $companyFullName = $company?->name ?: config('app.name');
        $companyLogoUrl = $company?->logo_path ? asset($company->logo_path) : null;
    @endphp
    <title>Inscription - {{ $brandName }}</title>
    <link rel="icon" href="/images/pk-app.jpg" />
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
                        @if ($companyLogoUrl)
                            <img src="{{ $companyLogoUrl }}" alt="Logo" class="brand-logo-img">
                        @else
                            <i class="fas fa-bolt"></i>
                        @endif
                    </div>
                    <h1 class="brand-name">{{ $brandName }}</h1>
                </div>
                <p class="brand-tagline">{{ $companyFullName }}</p>

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
                        @if ($companyLogoUrl)
                            <img src="{{ $companyLogoUrl }}" alt="Logo" class="brand-logo-img-mobile">
                        @else
                            <i class="fas fa-bolt"></i>
                        @endif
                    </div>
                    <h2>{{ $brandName }}</h2>
                </div>

                <div class="auth-card">
                    <div class="form-header text-center">
                        <h2 class="form-title">Bienvenue 👋</h2>
                        <p class="form-subtitle">Créez votre compte</p>
                    </div>

                    <form method="POST" action="{{ route('register.store') }}" class="auth-form">
                        @csrf

                        <!-- Nom -->
                        <div class="form-group">
                            <label class="form-label">Nom <span class="text-info">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-user input-icon"></i>
                                <input type="text" name="name" value="{{ old('name') }}"
                                    class="form-control @error('name') is-invalid @enderror" required>
                            </div>

                            @error('name')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Téléphone -->
                        <div class="form-group">
                            <label class="form-label">Téléphone <span class="text-info">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fa-solid fa-phone input-icon"></i>
                                <input type="text" name="telephone" value="{{ old('telephone') }}"
                                    class="form-control @error('telephone') is-invalid @enderror" required>
                            </div>

                            @error('telephone')
                                <div class="invalid-feedback d-block">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <!-- Mot de passe -->
                        <div class="form-group">
                            <label class="form-label">Mot de passe <span class="text-info">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
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

                        <!-- Confirmation -->
                        <div class="form-group">
                            <label class="form-label">Confirmer le mot de passe <span class="text-info">*</span></label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" name="password_confirmation" class="form-control" required>
                            </div>
                        </div>

                        <button class="btn btn-auth">
                            Créer le compte
                        </button>

                        <div class="register-link">
                            Allez au dashboard ?
                            <a href="{{ route('dashboard') }}">Cliquez ici</a>
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
