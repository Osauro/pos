
<div class="paginate-bar-fixed" x-data="{
    perPage: <?php echo e($results->perPage()); ?>,
    lsKey: 'paginate<?php echo e(ucfirst($storageKey)); ?>',
    cookieKey: 'paginate<?php echo e(ucfirst($storageKey)); ?>',
    init() {
        const saved = localStorage.getItem(this.lsKey);
        if (saved) {
            const val = parseInt(saved);
            if (val && val > 0) {
                document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
                if (val !== this.perPage) {
                    this.perPage = val;
                    $wire.set('perPage', val);
                }
            }
        }
    },
    applyInput(el) {
        const val = parseInt(el.value);
        if (val && val > 0) {
            this.perPage = val;
            localStorage.setItem(this.lsKey, String(val));
            document.cookie = `${this.cookieKey}=${val}; path=/; max-age=31536000; SameSite=Lax`;
            $wire.set('perPage', val);
        } else {
            el.value = this.perPage;
        }
    }
}">

    <div class="paginate-bar-left">
        <p class="mb-0">Created By <a target="_blank" href="https://dieguitosoft.com">DieguitoSoft.com</a></p>
    </div>

    <div class="paginate-bar-right">
        
        <input type="text" x-bind:value="perPage" @click="$event.target.select()"
            @keydown.enter="applyInput($event.target); $event.target.blur()" @blur="applyInput($event.target)"
            class="paginate-bar-input" title="Registros por página">

        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->lastPage() > 1): ?>
        <div class="paginate-nav"
            <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'paginate-nav-'.e($storageKey).'-'.e($results->currentPage()).''; ?>wire:key="paginate-nav-<?php echo e($storageKey); ?>-<?php echo e($results->currentPage()); ?>"
            x-data="{
            current: <?php echo e($results->currentPage()); ?>,
            last: <?php echo e($results->lastPage()); ?>,
            goToPage(val) {
                const p = parseInt(val);
                if (!p || p < 1) {
                    this.current = <?php echo e($results->currentPage()); ?>;
                    return;
                }
                const target = p > this.last ? 1 : p;
                this.current = target;
                $wire.gotoPage(target);
            }
        }">
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->onFirstPage()): ?>
                <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-left"></i></button>
            <?php else: ?>
                <button class="paginate-btn" wire:click="previousPage" wire:loading.attr="disabled"><i class="fa-solid fa-chevron-left"></i></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <input type="number"
                x-bind:value="current"
                @focus="$event.target.select()"
                @keydown.enter="goToPage($event.target.value); $event.target.blur()"
                @blur="goToPage($event.target.value)"
                class="paginate-page-input"
                min="1" max="<?php echo e($results->lastPage()); ?>"
                title="Ir a página">

            <span class="paginate-separator">/</span>

            
            <input type="text" value="<?php echo e($results->lastPage()); ?>" readonly class="paginate-page-input paginate-total" title="Total de páginas">

            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($results->hasMorePages()): ?>
                <button class="paginate-btn" wire:click="nextPage" wire:loading.attr="disabled"><i class="fa-solid fa-chevron-right"></i></button>
            <?php else: ?>
                <button class="paginate-btn" disabled><i class="fa-solid fa-chevron-right"></i></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>

<style>
    :root { --theme-color: var(--theme-default, #884A39); }

    .footer-wrapper {
        display: none !important;
    }

    /* El card:hover del tema tiene transform: translateY(-2px) que rompe position:fixed */
    .paginate-bar-fixed {
        position: fixed !important;
        bottom: 0 !important;
        left: 265px;
        right: 0;
        top: auto !important;
        z-index: 1050;
        background: #fff;
        border-top: 2px solid #e0e6ed;
        box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.10);
        height: 52px;
        min-height: 52px;
        max-height: 52px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 16px;
        overflow: hidden;
        transform: translateZ(0) !important;
        will-change: transform;
        transition: left 0.3s ease;
    }

    /* Cuando el sidebar está cerrado (clase sidebar-open en pageWrapper) */
    #pageWrapper.sidebar-open .paginate-bar-fixed {
        left: 0;
    }

    .paginate-bar-left p {
        font-size: 0.8rem;
        color: #555;
        margin: 0;
    }

    .paginate-bar-left a {
        color: var(--theme-color);
        text-decoration: none;
    }

    .paginate-bar-left a:hover {
        filter: brightness(0.8);
        text-decoration: underline;
    }

    .paginate-bar-right {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    /* Input registros por página */
    .paginate-bar-input {
        width: 50px;
        height: 30px;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0 4px;
        color: #333;
        background: #fff;
    }

    .paginate-bar-input:focus {
        outline: none;
        border-color: var(--theme-color);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--theme-color) 20%, transparent);
    }

    /* Navegación compacta */
    .paginate-nav {
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .paginate-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.7rem;
        color: #333;
        transition: background-color 0.15s, color 0.15s;
    }

    .paginate-btn:hover:not(:disabled) {
        background: var(--theme-default, #884A39);
        color: #fff;
        border-color: var(--theme-default, #884A39);
    }

    .paginate-btn:disabled {
        opacity: 0.4;
        cursor: default;
    }

    .paginate-page-input {
        width: 44px;
        height: 30px;
        text-align: center;
        font-size: 0.85rem;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 0 4px;
        color: #333;
        background: #fff;
        /* Ocultar flechas spinner en number input */
        -moz-appearance: textfield;
    }

    .paginate-page-input::-webkit-inner-spin-button,
    .paginate-page-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .paginate-page-input:focus {
        outline: none;
        border-color: var(--theme-default, #884A39);
        box-shadow: 0 0 0 2px color-mix(in srgb, var(--theme-default, #884A39) 20%, transparent);
    }

    .paginate-total {
        background: #f8f9fa;
        cursor: default;
        color: #666;
    }

    .paginate-separator {
        font-size: 0.9rem;
        color: #666;
        line-height: 1;
    }

    /* Mobile */
    @media (max-width: 767px) {
        .paginate-bar-left {
            display: none !important;
        }

        .paginate-bar-right {
            width: 100%;
            justify-content: flex-end;
        }

        .paginate-bar-input {
            width: 40px;
            font-size: 0.8rem;
        }
    }
</style>
<?php /**PATH C:\laragon\www\tpv\resources\views/partials/paginate-bar.blade.php ENDPATH**/ ?>