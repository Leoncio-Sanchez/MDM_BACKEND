{{-- Informe mensual de licencias de funcionamiento emitidas (formato definido por el equipo). --}}
@php
    /** @var \App\Modules\Configuracion\Domain\ValueObjects\ConfiguracionInforme $config */
    /** @var \App\Modules\Reportes\Domain\ValueObjects\ResumenPadron $resumen */
    /** @var list<\App\Modules\Reportes\Domain\ValueObjects\LicenciaPadron> $licencias */
    $escudo = 'data:image/png;base64,'.base64_encode((string) file_get_contents(resource_path('images/escudo.png')));
    $fecha = fn (string $iso) => implode('/', array_reverse(explode('-', substr($iso, 0, 10))));
    $numero = fn (?float $n) => $n === null ? '—' : number_format($n, 1, ',', '.');
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Informe N° {{ $codigo }}</title>
<style>
    @page { margin: 26px 32px 46px 32px; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1f2937; }
    .encabezado { width: 100%; border-bottom: 2px solid #0B3C5D; padding-bottom: 6px; }
    .encabezado td { vertical-align: middle; }
    .escudo { width: 46px; }
    .entidad { font-size: 11px; font-weight: bold; color: #0B3C5D; text-align: center; }
    .anio { font-size: 8px; font-style: italic; text-align: center; margin-top: 2px; }
    .gerencia { font-size: 9px; text-align: center; margin-top: 2px; }
    h1 { font-size: 11px; text-align: center; margin: 10px 0 2px; }
    .subtitulo { text-align: center; font-size: 9px; font-weight: bold; color: #0B3C5D; }
    .lugar { text-align: right; margin: 6px 0 4px; }
    h2 { font-size: 9.5px; color: #0B3C5D; border-bottom: 1px solid #cbd5e1; padding-bottom: 2px; margin: 10px 0 5px; }
    table.datos { width: 100%; border-collapse: collapse; }
    table.datos th { background: #0B3C5D; color: #fff; font-size: 7.5px; padding: 4px 3px; text-align: left; }
    table.datos td { border-bottom: 1px solid #e5e7eb; padding: 3px; font-size: 7.5px; vertical-align: top; }
    table.datos tr:nth-child(even) td { background: #f8fafc; }
    table.datos.resumen { width: 40%; }
    .resumen td.n, .resumen th.n { text-align: right; }
    .total td { font-weight: bold; border-top: 1px solid #0B3C5D; }
    .indicadores { margin: 6px 0 0; }
    .indicadores td { padding: 2px 10px 2px 0; font-size: 8.5px; }
    .fuera { color: #b45309; }
    .vacio { padding: 10px; text-align: center; color: #6b7280; }
    .firma { margin-top: 45px; width: 260px; margin-left: auto; margin-right: auto; text-align: center; page-break-inside: avoid; }
    .firma .linea { border-top: 1px solid #1f2937; padding-top: 3px; font-weight: bold; }
    .firma .sello { margin-top: 2px; color: #6b7280; font-size: 7.5px; }
    .pie { position: fixed; bottom: -30px; left: 0; right: 0; font-size: 7px; color: #6b7280; }
</style>
</head>
<body>
    <div class="pie">
        Generado por el Sistema LF-ITSE el {{ $generado }}@if ($informe->esReimpresion) · Reimpresión del informe emitido el {{ $informe->fechaEmision->setTimezone(new DateTimeZone('America/Lima'))->format('d/m/Y') }}@endif
    </div>

    <table class="encabezado">
        <tr>
            <td style="width: 60px;"><img class="escudo" src="{{ $escudo }}" alt="Escudo"></td>
            <td>
                <div class="entidad">{{ $config->entidad }}</div>
                @if ($config->nombreAnio !== '')
                    <div class="anio">“{{ $config->nombreAnio }}”</div>
                @endif
                <div class="gerencia">{{ $config->gerencia }}</div>
            </td>
            <td style="width: 60px;"></td>
        </tr>
    </table>

    <h1>INFORME N° {{ $codigo }}</h1>
    <div class="subtitulo">INFORME MENSUAL DE LICENCIAS DE FUNCIONAMIENTO EMITIDAS — MES DE {{ $mes }}</div>
    <div class="lugar">Morales, {{ $fechaEmision }}</div>

    <h2>1. Resumen ejecutivo</h2>
    <table class="datos resumen">
        <thead><tr><th>Nivel de riesgo</th><th class="n">Licencias</th></tr></thead>
        <tbody>
            @foreach ($resumen->porNivelRiesgo as $nivel => $total)
                <tr><td>{{ $nivel }}</td><td class="n">{{ $total }}</td></tr>
            @endforeach
            <tr class="total"><td>TOTAL</td><td class="n">{{ $resumen->total }}</td></tr>
        </tbody>
    </table>
    <table class="indicadores">
        <tr>
            <td><strong>Cumplimiento del plazo TUPA:</strong>
                @if ($resumen->porcentajeCumplimiento === null)
                    —
                @else
                    {{ $numero($resumen->porcentajeCumplimiento) }} % ({{ $resumen->dentroDePlazo }} de {{ $resumen->total }} licencias dentro del plazo de su procedimiento)
                @endif
            </td>
        </tr>
        <tr>
            <td><strong>Tiempo promedio de atención:</strong>
                {{ $resumen->tiempoPromedioDiasHabiles === null ? '—' : $numero($resumen->tiempoPromedioDiasHabiles).' días hábiles' }}
                (desde la recepción del expediente hasta la emisión)
            </td>
        </tr>
    </table>

    <h2>2. Padrón de licencias emitidas</h2>
    <table class="datos">
        <thead>
            <tr>
                <th>N° Licencia</th><th>N° Expediente</th><th>Nombre comercial</th><th>Titular</th>
                <th>RUC / DNI</th><th>Giro</th><th>Zonificación</th><th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($licencias as $l)
                <tr>
                    <td>{{ $l->numeroLicencia }}</td>
                    <td>{{ $l->numeroExpediente }}</td>
                    <td>{{ $l->nombreComercial }}</td>
                    <td>{{ $l->titular }}</td>
                    <td>{{ $l->documentoTitular }}</td>
                    <td>{{ $l->giros }}</td>
                    <td>{{ $l->zonificacion }}</td>
                    <td>{{ $fecha($l->fechaEmision) }}@unless ($l->dentroDePlazo())<br><span class="fuera">fuera de plazo</span>@endunless</td>
                </tr>
            @empty
                <tr><td colspan="8" class="vacio">No se emitieron licencias de funcionamiento en el periodo.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="firma">
        <div class="linea">{{ $config->firmanteNombre !== '' ? $config->firmanteNombre : '______________________________' }}</div>
        <div>{{ $config->firmanteCargo }}</div>
        <div class="sello">Firma y sello</div>
    </div>
</body>
</html>
