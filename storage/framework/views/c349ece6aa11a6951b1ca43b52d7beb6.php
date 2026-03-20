<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ticket #<?php echo e($venta->numero_venta); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&display=swap" rel="stylesheet">
    <style>
        <?php
            $pageWidth = $width === 58 ? '58mm' : '80mm';
            $cols      = $width === 58 ? 32 : 44;
            $sep       = str_repeat('=', $cols);
            $dash      = str_repeat('-', $cols);
        ?>

        @media print {
            @page {
                size: <?php echo e($pageWidth); ?> auto;
                margin: 1mm 1mm 0;
            }
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Share Tech Mono', 'Courier New', monospace;
            font-size: <?php echo e($width === 58 ? '10pt' : '11pt'); ?>;
            margin: 0;
            padding: 0 1mm 5mm;
            width: 100%;
            color: #000;
            background: #fff;
        }

        .center { text-align: center; }
        .right   { text-align: right; }
        .bold    { font-weight: bold; }

        .negocio {
            font-size: <?php echo e($width === 58 ? '13pt' : '14pt'); ?>;
            font-weight: bold;
            text-align: center;
            letter-spacing: 2px;
            margin-bottom: 1mm;
        }

        .venta-num {
            font-size: <?php echo e($width === 58 ? '12pt' : '13pt'); ?>;
            font-weight: bold;
            text-align: center;
        }

        .sep-igual {
            text-align: center;
            margin: 2mm 0;
            font-size: 8pt;
            overflow: hidden;
            white-space: nowrap;
        }

        .sep-dash {
            text-align: center;
            margin: 2mm 0;
            font-size: 8pt;
            overflow: hidden;
            white-space: nowrap;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            font-size: 9pt;
            margin-bottom: 0.5mm;
        }

        .detalle-titulo {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            letter-spacing: 2px;
            margin: 2mm 0;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1mm;
            font-size: 9pt;
        }

        .item-nombre {
            flex: 1;
            padding-right: 2px;
            line-height: 1.3;
        }

        .item-precio {
            white-space: nowrap;
            font-weight: bold;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            font-size: <?php echo e($width === 58 ? '13pt' : '14pt'); ?>;
            font-weight: bold;
            margin: 2mm 0;
        }

        .gracias {
            font-size: 11pt;
            font-weight: bold;
            text-align: center;
            margin: 3mm 0 1mm;
        }

        /* ── Comanda de cocina (segunda hoja / corte automático) ── */
        .comanda-wrap {
            page-break-before: always;
            padding-top: 0;
        }
        .cmd-titulo {
            font-size: <?php echo e($width === 58 ? '20pt' : '26pt'); ?>;
            font-weight: bold;
            text-align: center;
            letter-spacing: 4px;
            margin-bottom: 1mm;
        }
        .cmd-venta {
            font-size: <?php echo e($width === 58 ? '14pt' : '16pt'); ?>;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2mm;
        }
        .cmd-sep { text-align: center; margin: 2mm 0; }
        .cmd-item {
            margin: 2mm 0;
            page-break-inside: avoid;
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 2mm;
        }
        .cmd-nombre {
            font-size: <?php echo e($width === 58 ? '10pt' : '13pt'); ?>;
            font-weight: bold;
            line-height: 1.3;
            flex: 1;
        }
        .cmd-detalle {
            font-size: <?php echo e($width === 58 ? '10pt' : '13pt'); ?>;
            font-weight: bold;
            white-space: nowrap;
        }

        .encargado {
            text-align: center;
            font-size: 9pt;
            margin-top: 1mm;
            line-height: 1.6;
        }
    </style>
</head>
<body>

    <div class="negocio"><?php echo e(strtoupper($negocio)); ?></div>

    <div class="sep-igual"><?php echo e($sep); ?></div>

    <div class="venta-num">VENTA: <?php echo e($venta->numero_venta); ?></div>

    <div class="info-row">
        <span>Fecha:</span>
        <span><?php echo e($venta->fecha_hora?->format('d/m/Y') ?? now()->format('d/m/Y')); ?></span>
    </div>
    <div class="info-row">
        <span>Hora:</span>
        <span><?php echo e($venta->fecha_hora?->format('H:i') ?? now()->format('H:i')); ?></span>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($venta->usuario): ?>
        <div class="info-row">
            <span>Cajero:</span>
            <span><?php echo e($venta->usuario->nombre); ?></span>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <div class="sep-igual"><?php echo e($sep); ?></div>
    <div class="detalle-titulo">- D E T A L L E -</div>
    <div class="sep-dash"><?php echo e($dash); ?></div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
        <div class="item-row">
            <span class="item-nombre"><?php echo e($item->cantidad); ?> <?php echo e($item->producto->nombre); ?></span>
            <span class="item-precio"><?php echo e(number_format($item->subtotal, 2)); ?></span>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

    <div class="sep-igual"><?php echo e($sep); ?></div>

    <div class="total-row">
        <span>TOTAL:</span>
        <span>Bs. <?php echo e(number_format($venta->total, 2)); ?></span>
    </div>

    <div class="sep-igual"><?php echo e($sep); ?></div>

    <div class="gracias">GRACIAS POR SU COMPRA</div>

    <?php $encargado = $venta->turno?->encargado; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($encargado): ?>
        <div class="encargado">
            Encargado: <?php echo e($encargado->nombre); ?><br>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($encargado->celular): ?>
            Celular: <?php echo e($encargado->celular); ?>

            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php
        $comandaItems = $items->filter(fn($i) => $i->producto->tipo !== 'Refrescos')->values();
        $sepCmd = $width === 58 ? str_repeat('-', 24) : str_repeat('-', 32);
    ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($comandaItems->count() > 0): ?>
    <div class="comanda-wrap">
        <div class="cmd-venta">VENTA #<?php echo e($venta->numero_venta); ?></div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $comandaItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
            <?php
                $arr = 0; $fid = 0; $mix = 0; $tiposUsados = 0;
                if ($item->producto->tipo === 'Platos' && !empty($item->detalle)) {
                    $arr = $item->detalle['arroz'] ?? 0;
                    $fid = $item->detalle['fideo'] ?? 0;
                    $mix = $item->detalle['mixto'] ?? 0;
                    $tiposUsados = ($arr > 0 ? 1 : 0) + ($fid > 0 ? 1 : 0) + ($mix > 0 ? 1 : 0);
                }
                // Nombre corto: split en el primer espacio
                $pos  = strpos($item->producto->nombre, ' ');
                $cad1 = $pos !== false ? substr($item->producto->nombre, 0, $pos) : $item->producto->nombre;
                $cad2 = $pos !== false ? trim(substr($item->producto->nombre, $pos + 1)) : '';
                if ($item->cantidad > 1) {
                    $ult   = mb_strtolower(mb_substr($cad1, -1));
                    $cad1 .= in_array($ult, ['a','e','i','o','u']) ? 's' : 'es';
                }
                $sufijo      = strcasecmp($cad2, 'sin huevo') === 0 ? ' S/H' : '';
                $nombreCorto = strtoupper($cad1 . $sufijo);
            ?>
            <div class="cmd-item">
                <span class="cmd-nombre"><?php echo e($item->cantidad); ?> <?php echo e($nombreCorto); ?></span>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($fid > 0 || $mix > 0): ?>
                    <span class="cmd-detalle"><?php echo e($arr > 0 ? 'A:'.$arr.' ' : ''); ?><?php echo e($fid > 0 ? 'F:'.$fid.' ' : ''); ?><?php echo e($mix > 0 ? 'M:'.$mix : ''); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () {
                window.print();
                window.close();
            }, 350);
        });
    </script>
</body>
</html>
<?php /**PATH C:\laragon\www\tpv\resources\views/tickets/cliente.blade.php ENDPATH**/ ?>