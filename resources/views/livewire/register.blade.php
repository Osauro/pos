<div>
    <h1 class="auth-title">Crear cuenta</h1>
    <p class="auth-subtitle">Empieza tu prueba gratuita de 30 días. Sin tarjeta.</p>

    <form wire:submit.prevent="register">

        {{-- Nombre --}}
        <div class="mb-4">
            <label for="nombre" class="form-label">
                <i class="fa-solid fa-user me-2"></i>Tu nombre
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                <input id="nombre"
                       type="text"
                       class="form-control @error('nombre') is-invalid @enderror"
                       wire:model="nombre"
                       autofocus
                       placeholder="Juan Pérez"
                       maxlength="100">
            </div>
            @error('nombre')
                <div class="text-danger mt-2" style="font-size:.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Celular --}}
        <div class="mb-4">
            <label for="celular" class="form-label">
                <i class="fa-solid fa-phone me-2"></i>Celular <small class="text-muted">(será tu usuario)</small>
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-mobile-screen-button"></i></span>
                <input id="celular"
                       type="text"
                       class="form-control @error('celular') is-invalid @enderror"
                       wire:model="celular"
                       maxlength="8"
                       placeholder="76543210"
                       inputmode="numeric">
            </div>
            @error('celular')
                <div class="text-danger mt-2" style="font-size:.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                    @if(str_contains($message, 'registrado'))
                        <a href="{{ route('login') }}" class="ms-1 fw-bold" style="color:inherit;">Ingresar →</a>
                    @endif
                </div>
            @enderror
        </div>

        {{-- PIN --}}
        <div class="mb-4">
            <label for="pin" class="form-label">
                <i class="fa-solid fa-lock me-2"></i>PIN de acceso <small class="text-muted">(4 dígitos)</small>
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                <input id="pin"
                       type="password"
                       class="form-control @error('pin') is-invalid @enderror"
                       wire:model="pin"
                       maxlength="4"
                       placeholder="••••"
                       inputmode="numeric">
            </div>
            @error('pin')
                <div class="text-danger mt-2" style="font-size:.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Confirmar PIN --}}
        <div class="mb-4">
            <label for="pin_confirmation" class="form-label">
                <i class="fa-solid fa-lock me-2"></i>Confirmar PIN
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-check"></i></span>
                <input id="pin_confirmation"
                       type="password"
                       class="form-control @error('pin_confirmation') is-invalid @enderror"
                       wire:model="pin_confirmation"
                       maxlength="4"
                       placeholder="••••"
                       inputmode="numeric">
            </div>
            @error('pin_confirmation')
                <div class="text-danger mt-2" style="font-size:.875rem;">
                    <i class="fa-solid fa-circle-exclamation me-1"></i>{{ $message }}
                </div>
            @enderror
        </div>

        {{-- Submit --}}
        <div class="mb-4">
            <button type="submit" class="btn-primary-custom" wire:loading.attr="disabled">
                <span wire:loading.remove>
                    <i class="fa-solid fa-store me-2"></i>CREAR MI CUENTA GRATIS
                </span>
                <span wire:loading>
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Creando cuenta...
                </span>
            </button>
        </div>

    </form>

    <div class="text-center mt-3">
        <p class="text-muted" style="font-size:.88rem;">
            ¿Ya tienes cuenta?
            <a href="{{ route('login') }}" class="fw-bold" style="color:var(--theme-default, #29adb2); text-decoration:none;">
                Ingresar →
            </a>
        </p>
    </div>
</div>
