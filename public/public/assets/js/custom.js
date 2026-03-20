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
