@extends('adminlte::page')

@section('template_title')
  {!! trans('hyplast.showing-employee', ['name' => $employee->EMPLEADO]) !!}
@endsection

@section('content')

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-white @if ($employee->ESTADO_EMPLEADO == "ACT") bg-success @else bg-warning @endif">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            {!! trans('hyplast.showing-employee-title', ['name' => $employee->NOMBRE]) !!}
                            <div class="pull-right">
                                <a href="{{ route('employees') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="left" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 align-self-start">
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td>{!! Form::label('code', trans('forms.create_product_label_code')); !!} </td>
                                                <td>{{$employee->EMPLEADO }}</td>
                                                <td>{!! Form::label('name', trans('forms.create_product_label_name')); !!}</td>
                                                <td>{{ $employee->NOMBRE }}</td>
                                                <td rowspan="4" align="right">
                                                    <img style="width: 150px;" src="data:image/png;base64,{{ chunk_split(base64_encode($employee->FOTOGRAFIA)) }}"  alt="{{ $employee->NOMBRE }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>{!! Form::label('sex', trans('hyplast.machines-table.sex')); !!}</td>
                                                <td>
                                                    @if($employee->SEXO == "M")
                                                        <span class="badge badge-success">MASCULINO</span>
                                                    @else
                                                        <span class="badge badge-warning">FEMENINO</span>
                                                    @endif
                                                </td>
                                                <td>{!! Form::label('init_date', trans('hyplast.machines-table.init_date')); !!}</td>
                                                <td>{{ \Carbon\Carbon::parse($employee->FECHA_INGRESO)->format('d-m-Y') }}</td>
                                            </tr>
                                            <tr>
                                                <td>{!! Form::label('department', trans('hyplast.machines-table.departament')); !!}</td>
                                                <td>{{ $employee->DEPARTAMENTO }}</td>
                                                <td>{!! Form::label('position', trans('hyplast.machines-table.position')); !!}</td>
                                                <td>{{ $employee->PUESTO }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4 mb-3 bg-primary text-white p-2 rounded">Datos Financieros</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td><strong>Salario</strong></td>
                                                <td>
                                                    @if($employee->SALARIO_REFERENCIA)
                                                        RD$ {{ number_format($employee->SALARIO_REFERENCIA, 2) }}
                                                    @else
                                                        <span class="text-muted">N/D</span>
                                                    @endif
                                                </td>
                                                <td><strong>Hora Nómina</strong></td>
                                                <td>
                                                    @if($employee->SALARIO_REFERENCIA)
                                                        @php
                                                            $valorNomina = $employee->SALARIO_REFERENCIA / 23.83 / 8;
                                                        @endphp
                                                        RD$ {{ number_format($valorNomina, 2) }}
                                                    @else
                                                        <span class="text-muted">N/D</span>
                                                    @endif
                                                </td>
                                                <td><strong>Hora Ponchador</strong></td>
                                                <td>
                                                    @if($employee->valor_hora)
                                                        RD$ {{ $employee->valor_hora }}
                                                    @else
                                                        <span class="text-muted">N/D</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <h5 class="mt-4 mb-3 bg-info text-white p-2 rounded">Datos de Asistencia (Ponchador)</h5>
                                <div class="table-responsive">
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                <td style="width: 20%;"><strong>Planilla</strong></td>
                                                <td style="width: 30%;">
                                                    @if($employee->nombre_planilla)
                                                        {{ $employee->nombre_planilla }}
                                                        @if($employee->id_planilla)
                                                            <span class="text-muted">(ID: {{ $employee->id_planilla }})</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No asignada</span>
                                                    @endif
                                                </td>
                                                <td style="width: 20%;"><strong>Grupo</strong></td>
                                                <td style="width: 30%;">
                                                    @if($employee->nombre_grupo)
                                                        {{ $employee->nombre_grupo }}
                                                        @if($employee->id_grupo)
                                                            <span class="text-muted">(ID: {{ $employee->id_grupo }})</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No asignado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Horario</strong></td>
                                                <td colspan="3">
                                                    @if($employee->nombre_horario)
                                                        {{ $employee->nombre_horario }}
                                                        @if($employee->id_horario)
                                                            <span class="text-muted">(ID: {{ $employee->id_horario }})</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">No asignado</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix"></div>
                        <div class="border-bottom"></div>

                        {{-- Sección de Registros de Asistencia Procesados --}}
                        <h5 class="mt-4 mb-3 bg-success text-white p-2 rounded">Registros de Asistencia Procesados</h5>

                        {{-- Filtro de Período --}}
                        <div class="row mb-3">
                            <div class="col-12 col-md-3 col-lg-2">
                                <label for="periodo_filter"><strong>Filtrar por Período:</strong></label>
                                <select id="periodo_filter" class="form-control">
                                    <option value="">-- Todos los períodos --</option>
                                    @foreach($periodos as $periodo)
                                        <option value="{{ $periodo->idperiodo }}">{{ $periodo->periodo_label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Tarjetas de Resumen de Horas y Costos --}}
                        <div class="row mb-3">
                            {{-- Tarjeta: Horas Extras 35% --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="small-box" style="background: linear-gradient(to bottom right, #8e44ad, #9b59b6);">
                                    <div class="inner text-white">
                                        <h4 id="horas_extras_35_display">0.00 h</h4>
                                        <p>Horas Extras 35%</p>
                                        <h5 id="costo_extras_35_display" class="mb-0">RD$ 0.00</h5>
                                        <small>Costo: Hora Nómina × 1.35</small>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Tarjeta: Horas Extras 50% --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="small-box" style="background: linear-gradient(to bottom right, #17a2b8, #20c9e3);">
                                    <div class="inner text-white">
                                        <h4 id="horas_extras_50_display">0.00 h</h4>
                                        <p>Horas Extras 50%</p>
                                        <h5 id="costo_extras_50_display" class="mb-0">RD$ 0.00</h5>
                                        <small>Costo: Hora Nómina × 1.50</small>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-business-time"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Tarjeta: Domingo/Feriado 100% --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="small-box" style="background: linear-gradient(to bottom right, #dc3545, #f56c7a);">
                                    <div class="inner text-white">
                                        <h4 id="horas_domingo_display">0.00 h</h4>
                                        <p>Domingo/Feriado 100%</p>
                                        <h5 id="costo_domingo_display" class="mb-0">RD$ 0.00</h5>
                                        <small>Costo: Hora Nómina × 2.00</small>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-calendar-day"></i>
                                    </div>
                                </div>
                            </div>

                            {{-- Tarjeta: Total Costo Horas Extras --}}
                            <div class="col-12 col-sm-6 col-md-3">
                                <div class="small-box" style="background: linear-gradient(to bottom right, #28a745, #34ce57);">
                                    <div class="inner text-white">
                                        <h4 id="total_horas_extras_display">0.00 h</h4>
                                        <p>Total Horas Extras</p>
                                        <h5 id="total_costo_extras_display" class="mb-0">RD$ 0.00</h5>
                                        <small>Costo Total de Extras</small>
                                    </div>
                                    <div class="icon">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Resumen Detallado (colapsable) --}}
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">
                                            <a data-toggle="collapse" href="#collapseResumen" role="button" aria-expanded="false" aria-controls="collapseResumen">
                                                <i class="fas fa-chart-line"></i> Ver Resumen Detallado de Horas
                                                <i class="fas fa-chevron-down float-right"></i>
                                            </a>
                                        </h6>
                                    </div>
                                    <div class="collapse" id="collapseResumen">
                                        <div class="card-body p-2">
                                            <div class="row d-flex justify-content-between">
                                                <div class="col">
                                                    <small class="text-muted d-block">T. Legal:</small>
                                                    <strong id="resumen_tiempo_legal" class="text-primary">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">Base:</small>
                                                    <strong id="resumen_tiempo_ord" class="text-success">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">Ex 35%:</small>
                                                    <strong id="resumen_horas_extras_35" style="color: #6f42c1;">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">Ex 50%:</small>
                                                    <strong id="resumen_horas_extras_50" style="color: #17a2b8;">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">Domingo:</small>
                                                    <strong id="resumen_horas_domingo" class="text-danger">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">Especial:</small>
                                                    <strong id="resumen_horas_especiales" class="text-info">0.00 h</strong>
                                                </div>
                                                <div class="col">
                                                    <small class="text-muted d-block">No Trab:</small>
                                                    <strong id="resumen_otros_dias" class="text-warning">0.00 h</strong>
                                                </div>
                                                <div class="col-auto">
                                                    <div id="total_trabajado_container" class="p-2 rounded" style="background-color: #d4edda; min-width: 120px;">
                                                        <small class="text-muted d-block">Total:</small>
                                                        <strong id="resumen_total_trabajado">0.00 h</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table id="processed-marks-table" class="table table-striped table-bordered table-hover" style="width:100%">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Entrada</th>
                                        <th>Salida</th>
                                        <th>Turno</th>
                                        <th>Ordinario</th>
                                        <th>Extras</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <br />


                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

  <style>
    /* Estilos para las tarjetas pequeñas de AdminLTE */
    .small-box {
        border-radius: 0.5rem;
        position: relative;
        display: block;
        margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        transition: all 0.3s ease;
    }

    .small-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 25px rgba(0,0,0,0.2);
    }

    .small-box > .inner {
        padding: 15px;
    }

    .small-box .icon {
        position: absolute;
        top: 10px;
        right: 15px;
        z-index: 0;
        font-size: 70px;
        color: rgba(255, 255, 255, 0.2);
    }

    .small-box h3, .small-box h4, .small-box h5 {
        font-weight: bold;
        margin: 0 0 5px 0;
    }

    .small-box p {
        font-size: 14px;
        margin: 0 0 10px 0;
    }

    .small-box small {
        font-size: 12px;
        opacity: 0.8;
    }

    /* Degradado rojo para filas de domingo */
    table#processed-marks-table tbody tr.domingo-row {
        background: linear-gradient(to right, #ffcccc, #ff9999) !important;
    }

    table#processed-marks-table tbody tr.domingo-row:hover {
        background: linear-gradient(to right, #ffb3b3, #ff8080) !important;
    }

    table#processed-marks-table tbody tr.domingo-row td {
        background: transparent !important;
    }

    /* Comprimir altura de filas - padding reducido */
    table#processed-marks-table tbody td,
    table#processed-marks-table thead th {
        padding: 4px 8px !important;
        line-height: 1.2 !important;
        vertical-align: middle !important;
    }

    table#processed-marks-table {
        font-size: 14px;
    }

    /* Tooltip para turno */
    .turno-tooltip {
        position: relative;
        cursor: pointer;
        color: #007bff;
        text-decoration: underline dotted;
    }

    .turno-tooltip .tooltiptext {
        visibility: hidden;
        width: 220px;
        background-color: #333;
        color: #fff;
        text-align: left;
        border-radius: 6px;
        padding: 10px;
        position: absolute;
        z-index: 1;
        bottom: 125%;
        left: 50%;
        margin-left: -110px;
        opacity: 0;
        transition: opacity 0.3s;
        font-size: 13px;
        line-height: 1.5;
    }

    .turno-tooltip .tooltiptext::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #333 transparent transparent transparent;
    }

    .turno-tooltip:hover .tooltiptext {
        visibility: visible;
        opacity: 1;
    }
  </style>

  <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
  <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

  <script type="text/javascript">
    $(document).ready(function() {
        $.noConflict();

        // Obtener valor de hora nómina desde PHP
        var valorHoraNomina = 0;
        @if($employee->SALARIO_REFERENCIA)
            valorHoraNomina = {{ $employee->SALARIO_REFERENCIA }} / 23.83 / 8;
        @endif

        // Función para calcular y actualizar costos
        function actualizarCostos(totales) {
            var horasExtras35 = parseFloat(totales.horas_extras_35) || 0;
            var horasExtras50 = parseFloat(totales.horas_extras_50) || 0;
            var horasDomingo = parseFloat(totales.horas_domingo) || 0;

            // Calcular costos
            var costoExtras35 = valorHoraNomina * 1.35 * horasExtras35;
            var costoExtras50 = valorHoraNomina * 1.50 * horasExtras50;
            var costoDomingo = valorHoraNomina * 2.00 * horasDomingo;
            var totalCostoExtras = costoExtras35 + costoExtras50 + costoDomingo;
            var totalHorasExtras = horasExtras35 + horasExtras50 + horasDomingo;

            // Actualizar tarjetas de horas extras
            $('#horas_extras_35_display').text(horasExtras35.toFixed(2) + ' h');
            $('#costo_extras_35_display').text('RD$ ' + costoExtras35.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            $('#horas_extras_50_display').text(horasExtras50.toFixed(2) + ' h');
            $('#costo_extras_50_display').text('RD$ ' + costoExtras50.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            $('#horas_domingo_display').text(horasDomingo.toFixed(2) + ' h');
            $('#costo_domingo_display').text('RD$ ' + costoDomingo.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

            $('#total_horas_extras_display').text(totalHorasExtras.toFixed(2) + ' h');
            $('#total_costo_extras_display').text('RD$ ' + totalCostoExtras.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }

        // Inicializar DataTable
        var table = $('#processed-marks-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('datatables.employees.processed-marks', $employee->EMPLEADO) }}",
                data: function(d) {
                    d.periodo_id = $('#periodo_filter').val();
                },
                dataSrc: function(json) {
                    // Actualizar resumen con los totales
                    if (json.totales) {
                        console.log('Totales recibidos:', json.totales);

                        $('#resumen_tiempo_legal').text(json.totales.tiempo_legal + ' h');
                        $('#resumen_tiempo_ord').text(json.totales.tiempo_ord + ' h');              // Base Ordinaria
                        $('#resumen_horas_extras_35').text(json.totales.horas_extras_35 + ' h');    // H. Extras 35%
                        $('#resumen_horas_extras_50').text(json.totales.horas_extras_50 + ' h');    // H. Extras 50%
                        $('#resumen_horas_domingo').text(json.totales.horas_domingo + ' h');        // Domingo 100%
                        $('#resumen_horas_especiales').text(json.totales.horas_especiales + ' h');  // H. Especiales
                        $('#resumen_otros_dias').text(json.totales.otros_dias + ' h');              // No Trabajado

                        // Calcular total trabajado: Base Ordinaria + Extras 35% + Extras 50% + Domingo + H. Especiales
                        // NO incluir "No Trabajado" porque restan horas
                        var totalTrabajado = parseFloat(json.totales.tiempo_ord) +
                                           parseFloat(json.totales.horas_extras_35) +
                                           parseFloat(json.totales.horas_extras_50) +
                                           parseFloat(json.totales.horas_domingo) +
                                           parseFloat(json.totales.horas_especiales);
                        $('#resumen_total_trabajado').text(totalTrabajado.toFixed(2) + ' h');

                        // Cambiar color de fondo según el límite
                        var container = $('#total_trabajado_container');
                        container.removeClass('bg-success bg-warning bg-danger');

                        console.log('Color a aplicar:', json.totales.color_total);

                        if (json.totales.color_total === 'danger') {
                            container.css('background-color', '#f8d7da'); // Rojo claro
                            console.log('Aplicando ROJO');
                        } else if (json.totales.color_total === 'warning') {
                            container.css('background-color', '#fff3cd'); // Naranja claro
                            console.log('Aplicando NARANJA');
                        } else {
                            container.css('background-color', '#d4edda'); // Verde claro
                            console.log('Aplicando VERDE');
                        }

                        // Actualizar costos de horas extras
                        actualizarCostos(json.totales);
                    }
                    return json.data;
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'fecha_formato', name: 'fecha_formato', orderable: false },
                { data: 'hora_entra', name: 'hora_entra', orderable: false, className: 'text-center' },
                { data: 'hora_sale', name: 'hora_sale', orderable: false, className: 'text-center' },
                {
                    data: 'idturno',
                    name: 'idturno',
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (!data || data === '-' || data === 'null') return '-';

                        var tooltipHtml = '<div class="turno-tooltip">';
                        tooltipHtml += data;
                        tooltipHtml += '<span class="tooltiptext">';

                        if (row.turno_descripcion) {
                            tooltipHtml += '<strong>' + row.turno_descripcion + '</strong><br>';
                        } else {
                            tooltipHtml += '<strong>Turno ' + data + '</strong><br>';
                        }

                        if (row.turno_hentra && row.turno_hsale) {
                            tooltipHtml += 'Entrada: ' + row.turno_hentra + '<br>';
                            tooltipHtml += 'Salida: ' + row.turno_hsale;
                        } else {
                            tooltipHtml += 'Sin horario definido';
                        }

                        tooltipHtml += '</span></div>';
                        return tooltipHtml;
                    }
                },
                { data: 'ordinario', name: 'ordinario', className: 'text-right', orderable: false },
                { data: 'extras', name: 'extras', className: 'text-right', orderable: false },
            ],
            language: {
                url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json'
            },
            ordering: false,
            searching: false,
            lengthChange: false,
            pageLength: 25,
            responsive: true,
            rowCallback: function(row, data) {
                // Aplicar clase domingo-row si es domingo
                if (data.fecha_formato && typeof data.fecha_formato === 'string') {
                    var fechaLower = data.fecha_formato.toLowerCase();
                    if (fechaLower.startsWith('domingo')) {
                        $(row).addClass('domingo-row');
                    }
                }
            }
        });

        // Recargar tabla al cambiar el período
        $('#periodo_filter').on('change', function() {
            console.log('Período seleccionado:', $(this).val());
            table.ajax.reload();
        });
    });
  </script>

@endsection
