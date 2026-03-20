<?php

namespace App\Traits;

trait WithSwal
{
    /**
     * Mostrar mensaje de éxito
     */
    public function swalSuccess($title = '¡Éxito!', $text = 'Operación realizada correctamente')
    {
        $this->dispatch('swal', [
            'icon' => 'success',
            'title' => $title,
            'text' => $text,
            'timer' => 3000,
            'showConfirmButton' => false
        ]);
    }

    /**
     * Mostrar mensaje de error
     */
    public function swalError($title = 'Error', $text = 'Ocurrió un error al procesar la solicitud')
    {
        $this->dispatch('swal', [
            'icon' => 'error',
            'title' => $title,
            'text' => $text,
            'timer' => 3000,
            'showConfirmButton' => false
        ]);
    }

    /**
     * Mostrar mensaje de advertencia
     */
    public function swalWarning($title = 'Advertencia', $text = 'Por favor, revise la información')
    {
        $this->dispatch('swal', [
            'icon' => 'warning',
            'title' => $title,
            'text' => $text,
            'timer' => 3000,
            'showConfirmButton' => false
        ]);
    }

    /**
     * Mostrar mensaje informativo
     */
    public function swalInfo($title = 'Información', $text = '')
    {
        $this->dispatch('swal', [
            'icon' => 'info',
            'title' => $title,
            'text' => $text,
            'timer' => 3000,
            'showConfirmButton' => false
        ]);
    }

    /**
     * Mostrar confirmación con callback
     */
    public function swalConfirm($title = '¿Estás seguro?', $text = 'Esta acción no se puede deshacer', $confirmButtonText = 'Sí, continuar', $cancelButtonText = 'Cancelar', $eventName = 'confirmed')
    {
        $this->dispatch('swal:confirm', [
            'icon' => 'question',
            'title' => $title,
            'text' => $text,
            'showCancelButton' => true,
            'confirmButtonText' => $confirmButtonText,
            'cancelButtonText' => $cancelButtonText,
            'confirmButtonColor' => '#3085d6',
            'cancelButtonColor' => '#d33',
            'eventName' => $eventName
        ]);
    }

    /**
     * Mostrar diálogo de confirmación para eliminar
     */
    public function swalConfirmDelete($eventName = 'confirmed')
    {
        $this->swalConfirm(
            '¿Eliminar este registro?',
            'Esta acción no se puede deshacer',
            'Sí, eliminar',
            'Cancelar',
            $eventName
        );
    }

    /**
     * Mostrar notificación de éxito (alias)
     */
    public function showSuccessNotification($message)
    {
        $this->swalSuccess('¡Éxito!', $message);
    }

    /**
     * Mostrar notificación de error (alias)
     */
    public function showErrorNotification($message)
    {
        $this->swalError('Error', $message);
    }

    /**
     * Confirmar eliminación
     */
    public function confirmDelete($id, $method)
    {
        $this->dispatch('swal:confirm', [
            'icon' => 'warning',
            'title' => '¿Estás seguro?',
            'text' => 'Esta acción no se puede deshacer',
            'showCancelButton' => true,
            'confirmButtonText' => 'Sí, eliminar',
            'cancelButtonText' => 'Cancelar',
            'confirmButtonColor' => '#d33',
            'cancelButtonColor' => '#3085d6',
            'id' => $id,
            'method' => $method
        ]);
    }

    /**
     * Mostrar mensaje de carga
     */
    public function swalLoading($title = 'Cargando...', $text = 'Por favor espere')
    {
        $this->dispatch('swal:loading', [
            'title' => $title,
            'text' => $text,
            'allowOutsideClick' => false,
            'didOpen' => 'Swal.showLoading()'
        ]);
    }

    /**
     * Cerrar cualquier alerta de Swal abierta
     */
    public function swalClose()
    {
        $this->dispatch('swal:close');
    }
}
