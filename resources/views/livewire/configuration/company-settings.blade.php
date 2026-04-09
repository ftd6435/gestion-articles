<div class="container-fluid py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div>
            <h1 class="h4 fw-bold mb-1">Paramètres entreprise</h1>
            <p class="text-muted mb-0">Configurer le nom, logo et coordonnées</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            @include('components.shared.alerts')

            <div class="row g-4">
                <div class="col-12 col-lg-4">
                    <div class="d-flex flex-column align-items-start gap-3">
                        <div class="fw-semibold">Logo</div>

                        <div class="w-100">
                            <div class="company-dropzone @error('logo') is-invalid @enderror" id="companyLogoDropzone"
                                tabindex="0" role="button" aria-label="Téléverser le logo">
                                <input type="file" id="companyLogoInput" class="d-none" wire:model="logo"
                                    accept="image/*">

                                @if ($logoPreview)
                                    <img src="{{ $logoPreview }}" alt="Logo" class="company-logo-preview">
                                @else
                                    <div class="company-dropzone-empty">
                                        <i class="fas fa-cloud-upload-alt fa-2x"></i>
                                        <div class="fw-semibold mt-2">Glissez-déposez le logo</div>
                                        <div class="small text-muted">ou cliquez pour sélectionner</div>
                                    </div>
                                @endif

                                <div class="company-dropzone-hint">
                                    JPG, PNG, GIF (Max 2MB)
                                </div>

                                <div class="company-dropzone-loading" wire:loading wire:target="logo">
                                    Téléchargement...
                                </div>
                            </div>

                            @error('logo')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="removeLogo">
                                Retirer
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                wire:model.defer="name" placeholder="Ex: PK Company">
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Nom court (Sidebar)</label>
                            <input type="text" class="form-control @error('short_name') is-invalid @enderror"
                                wire:model.defer="short_name" placeholder="Ex: PK">
                            @error('short_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Téléphone</label>
                            <input type="text" class="form-control @error('telephone') is-invalid @enderror"
                                wire:model.defer="telephone" placeholder="Ex: 620000000">
                            @error('telephone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                wire:model.defer="email" placeholder="Ex: contact@exemple.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" class="form-control @error('adresse') is-invalid @enderror"
                                wire:model.defer="adresse" placeholder="Ex: Conakry">
                            @error('adresse')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-primary" wire:click="save">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        .company-dropzone {
            width: 100%;
            height: 180px;
            border: 2px dashed rgba(78, 84, 200, 0.45);
            background: rgba(78, 84, 200, 0.04);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
            overflow: hidden;
            outline: none;
        }

        .company-dropzone:hover {
            border-color: rgba(78, 84, 200, 0.8);
            background: rgba(78, 84, 200, 0.06);
            transform: translateY(-1px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .company-dropzone.is-dragover {
            border-color: rgba(78, 84, 200, 1);
            background: rgba(78, 84, 200, 0.10);
            transform: translateY(-1px);
            box-shadow: 0 10px 28px rgba(78, 84, 200, 0.15);
        }

        .company-dropzone.is-invalid {
            border-color: rgba(220, 53, 69, 0.7);
            background: rgba(220, 53, 69, 0.04);
        }

        .company-logo-preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            padding: 14px;
            user-select: none;
            pointer-events: none;
        }

        .company-dropzone-empty {
            color: #4e54c8;
            text-align: center;
            padding: 12px;
            user-select: none;
        }

        .company-dropzone-hint {
            position: absolute;
            left: 12px;
            bottom: 10px;
            font-size: 0.78rem;
            color: rgba(0, 0, 0, 0.55);
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(0, 0, 0, 0.06);
            padding: 4px 8px;
            border-radius: 999px;
            backdrop-filter: blur(6px);
            pointer-events: none;
        }

        .company-dropzone-loading {
            position: absolute;
            right: 12px;
            bottom: 10px;
            font-size: 0.78rem;
            color: #4e54c8;
            background: rgba(255, 255, 255, 0.75);
            border: 1px solid rgba(0, 0, 0, 0.06);
            padding: 4px 8px;
            border-radius: 999px;
            backdrop-filter: blur(6px);
        }
    </style>

    <script>
        (function() {
            function initCompanyLogoDropzone() {
                const dropzone = document.getElementById('companyLogoDropzone');
                const input = document.getElementById('companyLogoInput');

                if (!dropzone || !input) return;
                if (dropzone.dataset.bound === '1') return;
                dropzone.dataset.bound = '1';

                const prevent = (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                };

                const setDrag = (isOver) => {
                    if (isOver) dropzone.classList.add('is-dragover');
                    else dropzone.classList.remove('is-dragover');
                };

                dropzone.addEventListener('click', () => input.click());
                dropzone.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        prevent(e);
                        input.click();
                    }
                });

                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach((evt) => {
                    dropzone.addEventListener(evt, prevent);
                });

                dropzone.addEventListener('dragenter', () => setDrag(true));
                dropzone.addEventListener('dragover', () => setDrag(true));
                dropzone.addEventListener('dragleave', () => setDrag(false));
                dropzone.addEventListener('drop', (e) => {
                    setDrag(false);
                    const files = e.dataTransfer?.files;
                    if (!files || files.length === 0) return;
                    input.files = files;
                    input.dispatchEvent(new Event('change', {
                        bubbles: true
                    }));
                });
            }

            document.addEventListener('livewire:initialized', initCompanyLogoDropzone);
            document.addEventListener('livewire:navigated', initCompanyLogoDropzone);
            initCompanyLogoDropzone();
        })();
    </script>
</div>
