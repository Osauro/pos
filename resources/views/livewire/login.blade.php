<div>
    <h1 class="auth-title">Entrar</h1>
    <p class="auth-subtitle" style="display: none;">Ingresa tus credenciales para acceder</p>

    <!-- Session Status -->
    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>{{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="login">
        <!-- Celular -->
        <div class="mb-4">
            <label for="celular" class="form-label">
                <i class="fa-solid fa-phone me-2"></i>Celular
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fa-solid fa-mobile-screen-button"></i>
                </span>
                <input
                    id="celular"
                    type="text"
                    class="form-control @error('celular') is-invalid @enderror"
                    wire:model="celular"
                    autofocus
                    maxlength="8"
                    placeholder="12345678"
                    inputmode="numeric"
                />
            </div>
            @error('celular')
                <div class="text-danger mt-2" style="font-size: 0.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <!-- PIN -->
        <div class="mb-4">
            <label for="pin" class="form-label">
                <i class="fa-solid fa-lock me-2"></i>Contraseña
            </label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fa-solid fa-key"></i>
                </span>
                <input
                    id="pin"
                    type="password"
                    class="form-control @error('pin') is-invalid @enderror"
                    wire:model="pin"
                    maxlength="4"
                    placeholder="••••"
                    inputmode="numeric"
                />
            </div>
            @error('pin')
                <div class="text-danger mt-2" style="font-size: 0.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        <!-- Remember Me -->
        <div class="mb-4">
            <div class="form-check">
                <input
                    id="remember"
                    type="checkbox"
                    class="form-check-input"
                    wire:model="remember"
                >
                <label class="form-check-label" for="remember">
                    Recuérdame
                </label>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="mb-4">
            <button type="submit" class="btn-primary-custom" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    ENTRAR
                </span>
                <span wire:loading>
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Ingresando...
                </span>
            </button>
        </div>
    </form>

    <div class="text-center mt-3">
        <p class="text-muted" style="font-size:.88rem;">
            ¿No tienes cuenta?
            <a href="{{ route('register') }}" class="fw-bold" style="color:var(--theme-default, #29adb2); text-decoration:none;">
                Crear cuenta gratis →
            </a>
        </p>
    </div>

    <div class="text-center mt-4" style="display: none;">
        <p class="text-muted" style="font-size: 0.9rem;">
            <i class="fa-solid fa-shield-halved me-1"></i>
            Sistema de Punto de Venta Seguro
        </p>
    </div>
</div>
