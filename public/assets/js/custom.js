/* ========================================
   JAVASCRIPT PERSONALIZADO DEL PROYECTO
   ======================================== */

// Funciones para Drag & Drop de imagen
function handleDragOver(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#007bff';
    e.currentTarget.style.backgroundColor = '#e7f3ff';
}

function handleDragLeave(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#ccc';
    e.currentTarget.style.backgroundColor = '#f8f9fa';
}

function handleDrop(e) {
    e.preventDefault();
    e.stopPropagation();
    e.currentTarget.style.borderColor = '#ccc';
    e.currentTarget.style.backgroundColor = '#f8f9fa';

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        const fileInput = document.getElementById('imagen');
        fileInput.files = files;
        fileInput.dispatchEvent(new Event('change', {
            bubbles: true
        }));
    }
}

// Inicializar SweetAlert Listeners para Livewire
function initSweetAlertListeners() {
    document.addEventListener('livewire:init', () => {

        // SweetAlert - Listener genérico
        Livewire.on('swal', (data) => {
            const d = Array.isArray(data) ? data[0] : data;
            Swal.fire({
                icon: d.icon || 'info',
                title: d.title || '',
                text: d.text || '',
                html: d.html || '',
                timer: d.timer || 3000,
                showConfirmButton: d.showConfirmButton !== false,
                confirmButtonText: d.confirmButtonText || 'Aceptar'
            });
        });

        // SweetAlert - Alertas de éxito
        Livewire.on('swal:success', ({title, text}) => {
            Swal.fire({
                icon: 'success',
                title: title,
                html: text,
                showConfirmButton: false,
                timer: 2000
            })
        })

        // SweetAlert - Alertas de error
        Livewire.on('swal:error', ({title, text}) => {
            Swal.fire({
                icon: 'error',
                title: title,
                html: text,
                confirmButtonText: 'Aceptar'
            })
        })

        // SweetAlert - Alertas de advertencia
        Livewire.on('swal:warning', ({title, text}) => {
            Swal.fire({
                icon: 'warning',
                title: title,
                html: text,
                confirmButtonText: 'Aceptar'
            })
        })

        // SweetAlert - Alertas de información
        Livewire.on('swal:info', ({title, text}) => {
            Swal.fire({
                icon: 'info',
                title: title,
                html: text,
                confirmButtonText: 'Aceptar'
            })
        })

        // SweetAlert - Confirmación de eliminación
        Livewire.on('swal:confirm', (data) => {
            const d = data[0] || data;
            Swal.fire({
                title: d.title,
                html: d.text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: d.confirmButtonColor || '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: d.confirmButtonText || 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const eventName = d.event || 'delete';
                    const recordId = d.id;
                    Livewire.dispatch(eventName, { id: recordId })
                }
            })
        })

        // SweetAlert - PIN generado para usuario
        Livewire.on('swal:pin', (data) => {
            const d = Array.isArray(data) ? data[0] : data;
            Swal.fire({
                title: 'PIN generado',
                html: `
                    <i class="fa-solid fa-key fa-3x mb-3" style="color:#884A39"></i>
                    <p class="mb-2">Usuario: <strong>${d.nombre}</strong></p>
                    <div style="display:flex;align-items:center;justify-content:center;margin-top:12px">
                        <span id="swal-pin" style="font-size:2.5rem;font-weight:700;letter-spacing:0.4rem;color:#884A39;background:#fdf3f0;padding:10px 24px;border-radius:12px;border:2px dashed #884A39">${d.pin}</span>
                    </div>
                    <p class="mt-3 mb-4 text-muted" style="font-size:0.85rem">Guarda este PIN, no volverá a mostrarse.</p>
                    <div style="display:flex;gap:10px;justify-content:center">
                        <button id="btn-copiar-pin" style="background:#884A39;color:#fff;border:none;padding:10px 22px;border-radius:8px;cursor:pointer;font-weight:600;font-size:0.95rem">
                            📋 Copiar PIN
                        </button>
                    </div>
                `,
                showConfirmButton: false,
                showCancelButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    document.getElementById('btn-copiar-pin').addEventListener('click', function() {
                        navigator.clipboard.writeText(d.pin).catch(() => {
                            const el = document.getElementById('swal-pin');
                            const range = document.createRange();
                            range.selectNode(el);
                            window.getSelection().removeAllRanges();
                            window.getSelection().addRange(range);
                            document.execCommand('copy');
                            window.getSelection().removeAllRanges();
                        });
                        this.innerHTML = '✅ ¡Copiado!';
                        this.style.background = '#28a745';
                        setTimeout(() => Swal.close(), 1200);
                    });
                }
            });
        })

        // SweetAlert - Toast notifications
        Livewire.on('swal:toast', (data) => {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer)
                    toast.addEventListener('mouseleave', Swal.resumeTimer)
                }
            })

            Toast.fire({
                icon: data.type || data[0].type,
                title: data.message || data[0].message
            })
        })
    })
}

// Auto-inicializar listeners
initSweetAlertListeners();

// Poner foco en el input de búsqueda al cargar la página
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.focus();
    }
});

// Mantener foco después de actualizaciones de Livewire
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el, component }) => {
        const searchInput = document.getElementById('searchInput');
        if (searchInput && document.activeElement !== searchInput) {
            searchInput.focus();
        }
    });
});

// Dropdown del usuario - Click para toggle
document.addEventListener('DOMContentLoaded', function() {
    // Toggle del sidebar de perfil de usuario
    const toggleProfileBtn = document.getElementById('toggleProfileSidebar');
    if (toggleProfileBtn) {
        toggleProfileBtn.addEventListener('click', function() {
            Livewire.dispatch('togglePerfilSidebar');
        });
    }
});
