@extends('adminlte::page')

@section('template_title')
    Análisis de Horas Trabajadas
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar"></i> Análisis de Horas Trabajadas
                        </h3>
                    </div>
                </div>

                <div class="card-body">
                    {{-- Advertencia --}}
                    <div class="alert alert-warning alert-dismissible fade show mb-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Importante:</strong> Estos valores están basados en el último Cálculo de marcajes del ponchador desde el Sistema Softland CA, debe solicitar la actualización a RRHH en el módulo de Ponchador Opción: <strong>Procesos → Calcular Periodo de Marcas</strong>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    {{-- Filtros --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="periodo_filter">Período</label>
                            <select id="periodo_filter" class="form-control form-control-sm">
                                <option value="">Seleccione período</option>
                                @foreach($periodos as $per)
                                    <option value="{{ $per->idperiodo }}"
                                            data-inicio="{{ $per->inicio }}"
                                            data-fin="{{ $per->fin }}">
                                        {{ $per->periodo_label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="departamento_filter">Departamento</label>
                            <select id="departamento_filter" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($departamentos as $dept)
                                    <option value="{{ $dept->DEPARTAMENTO }}">{{ $dept->DESCRIPCION }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="empleado_filter">Empleado</label>
                            <select id="empleado_filter" class="form-control form-control-sm">
                                <option value="">Todos</option>
                                @foreach($empleados as $emp)
                                    <option value="{{ $emp->EMPLEADO }}">{{ $emp->EMPLEADO }} - {{ $emp->NOMBRE }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label for="pageLength">Registros a mostrar</label>
                            <select id="pageLength" class="form-control form-control-sm">
                                <option value="10">10 registros</option>
                                <option value="25" selected>25 registros</option>
                                <option value="50">50 registros</option>
                                <option value="100">100 registros</option>
                                <option value="-1">Todos</option>
                            </select>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button id="btn_limpiar" class="btn btn-secondary btn-sm btn-block">
                                <i class="fas fa-eraser"></i> Limpiar
                            </button>
                        </div>
                    </div>

                    {{-- Resumen de Horas - Dashboard --}}
                    <div class="row mb-4" id="resumen_container" style="display: none;">
                        <div class="col-12 mb-2">
                            <h5 class="text-muted">
                                <i class="fas fa-chart-line"></i> Resumen General del Período
                            </h5>
                        </div>

                        {{-- Ex 35% --}}
                        <div class="col-12 col-sm-6 col-md col-lg">
                            <div class="small-box bg-purple">
                                <div class="inner">
                                    <h3 id="resumen_horas_extras_35">0.00</h3>
                                    <p>Horas Extras 35%</p>
                                    <h5 id="costo_extras_35_display" class="mb-0">RD$ 0.00</h5>
                                    <small>Hora Nómina × 1.35</small>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Ex 50% --}}
                        <div class="col-12 col-sm-6 col-md col-lg">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3 id="resumen_horas_extras_50">0.00</h3>
                                    <p>Horas Extras 50%</p>
                                    <h5 id="costo_extras_50_display" class="mb-0">RD$ 0.00</h5>
                                    <small>Hora Nómina × 1.50</small>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-business-time"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Domingo --}}
                        <div class="col-12 col-sm-6 col-md col-lg">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3 id="resumen_horas_domingo">0.00</h3>
                                    <p>Horas Domingo</p>
                                    <h5 id="costo_domingo_display" class="mb-0">RD$ 0.00</h5>
                                    <small>Hora Nómina × 2.00</small>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Especial --}}
                        <div class="col-12 col-sm-6 col-md col-lg">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3 id="resumen_horas_especiales">0.00</h3>
                                    <p>Horas Especiales</p>
                                    <h5 id="costo_especiales_display" class="mb-0">RD$ --</h5>
                                    <small>Hora Nómina × 2.00</small>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Total --}}
                        <div class="col-12 col-sm-6 col-md col-lg">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3 id="resumen_total">0.00</h3>
                                    <p>Total Horas Extra</p>
                                    <h5 id="total_costo_extras_display" class="mb-0">RD$ 0.00</h5>
                                    <small>Costo Total de Extras</small>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- DataTable --}}
                    <div class="table-responsive">
                        <table id="hours-analysis-table" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead class="thead-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Departamento</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>T. Legal</th>
                                    <th>Base</th>
                                    <th>Ex 35%</th>
                                    <th>Ex 50%</th>
                                    <th>Domingo</th>
                                    <th>Especial</th>
                                    <th>No Trab</th>
                                    <th>Total</th>
                                    <th>Costo H. Extras</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ficha Individual --}}
    <div class="modal fade" id="modal_ficha_individual" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-id-card"></i> Ficha Individual - <span id="modal_empleado_nombre"></span>
                    </h5>
                    <button type="button" id="btn_cerrar_modal_x" class="close text-white" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{-- Datos del Empleado --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <table class="table table-sm table-bordered">
                                <tbody>
                                    <tr>
                                        <td><strong>Código:</strong></td>
                                        <td id="modal_empleado_codigo"></td>
                                        <td><strong>Nombre:</strong></td>
                                        <td id="modal_empleado_nombre_full"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Departamento:</strong></td>
                                        <td id="modal_empleado_departamento"></td>
                                        <td><strong>Período:</strong></td>
                                        <td id="modal_empleado_periodo"></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Salario:</strong></td>
                                        <td id="modal_empleado_salario"><span class="text-muted">N/D</span></td>
                                        <td><strong>Hora Nómina:</strong></td>
                                        <td id="modal_empleado_hora_nomina"><span class="text-muted">N/D</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Horario:</strong></td>
                                        <td id="modal_empleado_horario"><span class="text-muted">No asignado</span></td>
                                        <td><strong>Planilla:</strong></td>
                                        <td id="modal_empleado_planilla"><span class="text-muted">No asignada</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-4 d-flex justify-content-end align-items-start">
                            <div>
                                <img id="modal_empleado_foto" src="" alt="Foto" class="img-thumbnail" style="max-width: 150px; display: none;">
                                <div id="modal_empleado_foto_placeholder" class="bg-light p-4 rounded">
                                    <i class="fas fa-user fa-5x text-muted"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tarjetas de Resumen de Horas y Costos --}}
                    <div class="row mb-3">
                        {{-- Tarjeta: Horas Extras 35% --}}
                        <div class="col-12 col-sm-6 col-md-3">
                            <div class="small-box" style="background: linear-gradient(to bottom right, #8e44ad, #9b59b6);">
                                <div class="inner text-white">
                                    <h4 id="modal_horas_extras_35_display">0.00 h</h4>
                                    <p>Horas Extras 35%</p>
                                    <h5 id="modal_costo_extras_35_display" class="mb-0">RD$ 0.00</h5>
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
                                    <h4 id="modal_horas_extras_50_display">0.00 h</h4>
                                    <p>Horas Extras 50%</p>
                                    <h5 id="modal_costo_extras_50_display" class="mb-0">RD$ 0.00</h5>
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
                                    <h4 id="modal_horas_domingo_display">0.00 h</h4>
                                    <p>Domingo/Feriado 100%</p>
                                    <h5 id="modal_costo_domingo_display" class="mb-0">RD$ 0.00</h5>
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
                                    <h4 id="modal_total_horas_extras_display">0.00 h</h4>
                                    <p>Total Horas Extras</p>
                                    <h5 id="modal_total_costo_extras_display" class="mb-0">RD$ 0.00</h5>
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
                                        <a data-toggle="collapse" href="#collapseResumenModal" role="button" aria-expanded="false" aria-controls="collapseResumenModal">
                                            <i class="fas fa-chart-line"></i> Ver Resumen Detallado de Horas
                                            <i class="fas fa-chevron-down float-right"></i>
                                        </a>
                                    </h6>
                                </div>
                                <div class="collapse" id="collapseResumenModal">
                                    <div class="card-body p-2">
                                        <div class="row d-flex justify-content-between">
                                            <div class="col">
                                                <small class="text-muted d-block">T. Legal:</small>
                                                <strong id="modal_tiempo_legal" class="text-primary">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">Base:</small>
                                                <strong id="modal_tiempo_ord" class="text-success">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">Ex 35%:</small>
                                                <strong id="modal_horas_extras_35" style="color: #6f42c1;">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">Ex 50%:</small>
                                                <strong id="modal_horas_extras_50" style="color: #17a2b8;">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">Domingo:</small>
                                                <strong id="modal_horas_domingo" class="text-danger">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">Especial:</small>
                                                <strong id="modal_horas_especiales" class="text-info">0.00 h</strong>
                                            </div>
                                            <div class="col">
                                                <small class="text-muted d-block">No Trab:</small>
                                                <strong id="modal_otros_dias" class="text-warning">0.00 h</strong>
                                            </div>
                                            <div class="col-auto">
                                                <div class="p-2 rounded bg-success" id="modal_total_container" style="min-width: 120px;">
                                                    <small class="text-white d-block">Total:</small>
                                                    <strong id="modal_total_trabajado" class="text-white">0.00 h</strong>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Tabla de Registros Procesados --}}
                    <h6 class="bg-info text-white p-2 rounded">Registros de Asistencia Procesados</h6>
                    <div class="table-responsive">
                        <table id="modal-processed-marks-table" class="table table-striped table-bordered table-hover table-sm" style="width:100%">
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
                            <tbody id="modal_registros_body">
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Cargando...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="btn_cerrar_modal" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">

    <style>
        /* Estilos para Dashboard de Resumen */
        .small-box {
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .small-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
        }

        .small-box > .inner {
            padding: 15px;
        }

        .small-box > .inner > h3,
        .small-box > .inner > h2 {
            font-weight: 700;
            margin: 5px 0px;
        }

        .small-box > .inner > h4,
        .small-box > .inner > h5 {
            font-weight: bold;
            margin: 0 0 5px 0;
            color: #fff;
        }

        .small-box > .inner > p {
            font-size: 14px;
            font-weight: 500;
            margin: 0 0 10px 0;
        }

        .small-box > .inner > small {
            font-size: 12px;
            opacity: 0.9;
            color: #fff;
        }

        .small-box .icon {
            font-size: 70px;
        }

        /* Color Purple personalizado */
        .bg-purple {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
        }

        .bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }

        .bg-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%) !important;
        }

        .bg-warning {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%) !important;
            color: #fff !important;
        }

        .bg-success {
            background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
        }

        /* Ajustar altura de filas */
        table#hours-analysis-table tbody td,
        table#hours-analysis-table thead th {
            padding: 4px 8px !important;
            line-height: 1.2 !important;
            vertical-align: middle !important;
        }

        table#hours-analysis-table {
            font-size: 13px;
        }

        /* Alinear columnas numéricas a la derecha */
        table#hours-analysis-table tbody td:nth-child(n+5) {
            text-align: right;
        }

        table#hours-analysis-table thead th:nth-child(n+5) {
            text-align: center;
        }

        /* Estilos para Modal de Ficha Individual */
        #modal-processed-marks-table tbody tr.domingo-row {
            background: linear-gradient(to right, #ffcccc, #ff9999) !important;
        }

        #modal-processed-marks-table tbody tr.domingo-row:hover {
            background: linear-gradient(to right, #ffb3b3, #ff8080) !important;
        }

        #modal-processed-marks-table tbody tr.domingo-row td {
            background: transparent !important;
        }

        #modal-processed-marks-table tbody td,
        #modal-processed-marks-table thead th {
            padding: 6px 8px !important;
            font-size: 13px;
        }

        #modal-processed-marks-table tbody td:nth-child(n+5) {
            text-align: right;
        }
    </style>

    <script type="text/javascript" src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap4.min.js"></script>

    <script type="text/javascript">
        $(document).ready(function() {
            $.noConflict();

            var table = null;

            // Función para inicializar/recargar DataTable
            function loadTable() {
                var periodoId = $('#periodo_filter').val();

                // Validar que haya período seleccionado
                if (!periodoId) {
                    return;
                }

                // Si la tabla ya existe, solo recargar los datos
                if (table) {
                    table.ajax.reload();
                    return;
                }

                // Inicializar DataTable por primera vez
                table = $('#hours-analysis-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: "{{ route('datatables.employees.hours-analysis') }}",
                        type: 'GET',
                        data: function(d) {
                            d.departamento = $('#departamento_filter').val();
                            d.empleado = $('#empleado_filter').val();
                            d.periodo = $('#periodo_filter').val();
                        },
                        dataSrc: function(json) {
                            // Actualizar resumen si vienen totales
                            if (json.totales) {
                                $('#resumen_horas_extras_35').text(json.totales.horas_extras_35);
                                $('#resumen_horas_extras_50').text(json.totales.horas_extras_50);
                                $('#resumen_horas_domingo').text(json.totales.horas_domingo);
                                $('#resumen_horas_especiales').text(json.totales.horas_especiales);

                                // Calcular total: Ex 35% + Ex 50% + Domingo + Especial
                                var total = parseFloat(json.totales.horas_extras_35) +
                                           parseFloat(json.totales.horas_extras_50) +
                                           parseFloat(json.totales.horas_domingo) +
                                           parseFloat(json.totales.horas_especiales);
                                $('#resumen_total').text(total.toFixed(2));

                                // Actualizar costos en las tarjetas (sin parseFloat que causa el error)
                                // Los valores vienen con formato desde el backend, hay que limpiar las comas antes de formatear
                                var costo35 = json.totales.costo_extras_35.replace(/,/g, '');
                                var costo50 = json.totales.costo_extras_50.replace(/,/g, '');
                                var costoDom = json.totales.costo_domingo.replace(/,/g, '');
                                var costoEsp = json.totales.costo_especiales.replace(/,/g, '');
                                var costoTotal = json.totales.costo_total_extras.replace(/,/g, '');

                                $('#costo_extras_35_display').text('RD$ ' + Number(costo35).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                                $('#costo_extras_50_display').text('RD$ ' + Number(costo50).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                                $('#costo_domingo_display').text('RD$ ' + Number(costoDom).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                                // Mostrar costo de horas especiales o "RD$ --" si no hay datos
                                if (Number(costoEsp) > 0) {
                                    $('#costo_especiales_display').text('RD$ ' + Number(costoEsp).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                                } else {
                                    $('#costo_especiales_display').text('RD$ --');
                                }

                                $('#total_costo_extras_display').text('RD$ ' + Number(costoTotal).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                                // Mostrar resumen con animación
                                $('#resumen_container').slideDown('fast');
                            }
                            return json.data;
                        },
                        error: function(xhr, error, code) {
                            console.log('Error DataTable:', xhr, error, code);
                            alert('Error al cargar los datos. Por favor, intente nuevamente.');
                        }
                    },
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'departamento', name: 'departamento' },
                        { data: 'codigo', name: 'codigo' },
                        { data: 'nombre', name: 'nombre' },
                        { data: 'tiempo_legal', name: 'tiempo_legal', className: 'text-right' },
                        { data: 'base_ordinaria', name: 'base_ordinaria', className: 'text-right' },
                        { data: 'horas_extras_35', name: 'horas_extras_35', className: 'text-right' },
                        { data: 'horas_extras_50', name: 'horas_extras_50', className: 'text-right' },
                        { data: 'horas_domingo', name: 'horas_domingo', className: 'text-right' },
                        { data: 'horas_especiales', name: 'horas_especiales', className: 'text-right' },
                        { data: 'no_trabajado', name: 'no_trabajado', className: 'text-right' },
                        { data: 'total_trabajado', name: 'total_trabajado', className: 'text-right' },
                        { data: 'costo_total_empleado', name: 'costo_total_empleado', className: 'text-right' },
                        { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Cargando...</span>'
                    },
                    pageLength: parseInt($('#pageLength').val()),
                    ordering: true,
                    searching: false,  // Quitar cuadro de búsqueda
                    lengthChange: false,  // Ocultar el selector por defecto
                    responsive: true,
                    destroy: true,
                    stateSave: false
                });
            }

            // Cambiar cantidad de registros a mostrar
            $('#pageLength').on('change', function() {
                if (table) {
                    table.page.len(parseInt($(this).val())).draw();
                }
            });

            // Al seleccionar período, cargar tabla automáticamente
            $('#periodo_filter').on('change', function() {
                var selectedOption = $(this).find('option:selected');

                if (selectedOption.val()) {
                    // Cargar tabla automáticamente
                    loadTable();
                }
            });

            // Filtro de departamento - recargar tabla si ya existe
            $('#departamento_filter').on('change', function() {
                var departamento = $(this).val();

                // Cargar empleados según departamento seleccionado
                $.ajax({
                    url: "{{ route('employees.by-department', '') }}/" + (departamento || ''),
                    type: 'GET',
                    success: function(empleados) {
                        // Limpiar y repoblar el select de empleados
                        var empleadoSelect = $('#empleado_filter');
                        empleadoSelect.empty();
                        empleadoSelect.append('<option value="">Todos</option>');

                        $.each(empleados, function(index, emp) {
                            empleadoSelect.append(
                                '<option value="' + emp.EMPLEADO + '">' +
                                emp.EMPLEADO + ' - ' + emp.NOMBRE +
                                '</option>'
                            );
                        });
                    },
                    error: function() {
                        console.log('Error al cargar empleados');
                    }
                });

                // Recargar tabla si ya existe
                if (table) {
                    table.ajax.reload();
                }
            });

            // Filtro de empleado - recargar tabla si ya existe
            $('#empleado_filter').on('change', function() {
                if (table) {
                    table.ajax.reload();
                }
            });

            // Botón limpiar
            $('#btn_limpiar').on('click', function() {
                // Limpiar filtros
                $('#periodo_filter').val('');
                $('#departamento_filter').val('');
                $('#empleado_filter').val('');
                $('#pageLength').val('25');

                // Resetear valores del resumen de horas
                $('#resumen_horas_extras_35').text('0.00');
                $('#resumen_horas_extras_50').text('0.00');
                $('#resumen_horas_domingo').text('0.00');
                $('#resumen_horas_especiales').text('0.00');
                $('#resumen_total').text('0.00');

                // Resetear valores de costos
                $('#costo_extras_35_display').text('RD$ 0.00');
                $('#costo_extras_50_display').text('RD$ 0.00');
                $('#costo_domingo_display').text('RD$ 0.00');
                $('#costo_especiales_display').text('RD$ --');
                $('#total_costo_extras_display').text('RD$ 0.00');

                // Ocultar resumen con animación
                $('#resumen_container').slideUp('fast');

                // Destruir tabla si existe
                if (table) {
                    table.clear().destroy();
                    table = null;
                }
            });

            // Botón Ver Ficha Individual (delegado para botones dinámicos)
            $(document).on('click', '.btn-ver-ficha', function(e) {
                e.preventDefault();

                var empleadoCodigo = $(this).data('empleado');
                var periodoId = $('#periodo_filter').val();

                // Validar que haya período seleccionado
                if (!periodoId) {
                    alert('Error: No se pudo obtener el período seleccionado.');
                    return;
                }

                // Cargar datos del empleado
                cargarFichaEmpleado(empleadoCodigo, periodoId);
            });

            // Función para cargar ficha del empleado
            function cargarFichaEmpleado(empleadoCodigo, periodoId) {
                $.ajax({
                    url: "{{ route('employees.ficha-individual') }}",
                    type: 'GET',
                    data: {
                        empleado: empleadoCodigo,
                        periodo: periodoId
                    },
                    beforeSend: function() {
                        $('#modal_registros_body').html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>');
                    },
                    success: function(response) {
                        // Llenar datos del empleado
                        $('#modal_empleado_codigo').text(response.empleado.codigo);
                        $('#modal_empleado_nombre').text(response.empleado.nombre);
                        $('#modal_empleado_nombre_full').text(response.empleado.nombre);
                        $('#modal_empleado_departamento').text(response.empleado.departamento);
                        $('#modal_empleado_periodo').text(response.periodo_label);

                        // Datos financieros
                        if (response.empleado.salario) {
                            $('#modal_empleado_salario').html('RD$ ' + parseFloat(response.empleado.salario).toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                            var valorHoraNomina = response.empleado.salario / 23.83 / 8;
                            $('#modal_empleado_hora_nomina').html('RD$ ' + valorHoraNomina.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        } else {
                            $('#modal_empleado_salario').html('<span class="text-muted">N/D</span>');
                            $('#modal_empleado_hora_nomina').html('<span class="text-muted">N/D</span>');
                        }

                        // Horario
                        if (response.empleado.nombre_horario) {
                            var horarioHtml = response.empleado.nombre_horario;
                            if (response.empleado.id_horario) {
                                horarioHtml += ' <span class="text-muted">(ID: ' + response.empleado.id_horario + ')</span>';
                            }
                            $('#modal_empleado_horario').html(horarioHtml);
                        } else {
                            $('#modal_empleado_horario').html('<span class="text-muted">No asignado</span>');
                        }

                        // Planilla
                        if (response.empleado.nombre_planilla) {
                            var planillaHtml = response.empleado.nombre_planilla;
                            if (response.empleado.id_planilla) {
                                planillaHtml += ' <span class="text-muted">(ID: ' + response.empleado.id_planilla + ')</span>';
                            }
                            $('#modal_empleado_planilla').html(planillaHtml);
                        } else {
                            $('#modal_empleado_planilla').html('<span class="text-muted">No asignada</span>');
                        }

                        // Foto del empleado
                        if (response.empleado.foto) {
                            $('#modal_empleado_foto').attr('src', 'data:image/png;base64,' + response.empleado.foto).show();
                            $('#modal_empleado_foto_placeholder').hide();
                        } else {
                            $('#modal_empleado_foto').hide();
                            $('#modal_empleado_foto_placeholder').show();
                        }

                        // Llenar resumen detallado
                        $('#modal_tiempo_legal').text(response.resumen.tiempo_legal + ' h');
                        $('#modal_tiempo_ord').text(response.resumen.base_ordinaria + ' h');
                        $('#modal_horas_extras_35').text(response.resumen.horas_extras_35 + ' h');
                        $('#modal_horas_extras_50').text(response.resumen.horas_extras_50 + ' h');
                        $('#modal_horas_domingo').text(response.resumen.horas_domingo + ' h');
                        $('#modal_horas_especiales').text(response.resumen.horas_especiales + ' h');
                        $('#modal_otros_dias').text(response.resumen.no_trabajado + ' h');
                        $('#modal_total_trabajado').text(response.resumen.total_trabajado + ' h');

                        // Aplicar color dinámico al total trabajado
                        var $totalContainer = $('#modal_total_container');
                        $totalContainer.removeClass('bg-success bg-warning bg-danger');

                        // Mapear color del backend a clases Bootstrap
                        var bgColorClass = 'bg-success'; // Verde por defecto
                        if (response.resumen.color_total === 'danger') {
                            bgColorClass = 'bg-danger';
                        } else if (response.resumen.color_total === 'warning') {
                            bgColorClass = 'bg-warning';
                        }

                        $totalContainer.addClass(bgColorClass);

                        // Calcular costos del modal
                        var horasExtras35 = parseFloat(response.resumen.horas_extras_35) || 0;
                        var horasExtras50 = parseFloat(response.resumen.horas_extras_50) || 0;
                        var horasDomingo = parseFloat(response.resumen.horas_domingo) || 0;

                        // Calcular hora nómina para costos (ya calculado arriba pero lo reutilizamos)
                        var valorHoraNomina = 0;
                        if (response.empleado.salario) {
                            valorHoraNomina = response.empleado.salario / 23.83 / 8;
                        }

                        // Calcular costos
                        var costoExtras35 = valorHoraNomina * 1.35 * horasExtras35;
                        var costoExtras50 = valorHoraNomina * 1.50 * horasExtras50;
                        var costoDomingo = valorHoraNomina * 2.00 * horasDomingo;
                        var totalCostoExtras = costoExtras35 + costoExtras50 + costoDomingo;
                        var totalHorasExtras = horasExtras35 + horasExtras50 + horasDomingo;

                        // Actualizar tarjetas de horas extras del modal
                        $('#modal_horas_extras_35_display').text(horasExtras35.toFixed(2) + ' h');
                        $('#modal_costo_extras_35_display').text('RD$ ' + costoExtras35.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        $('#modal_horas_extras_50_display').text(horasExtras50.toFixed(2) + ' h');
                        $('#modal_costo_extras_50_display').text('RD$ ' + costoExtras50.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        $('#modal_horas_domingo_display').text(horasDomingo.toFixed(2) + ' h');
                        $('#modal_costo_domingo_display').text('RD$ ' + costoDomingo.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        $('#modal_total_horas_extras_display').text(totalHorasExtras.toFixed(2) + ' h');
                        $('#modal_total_costo_extras_display').text('RD$ ' + totalCostoExtras.toLocaleString('es-DO', {minimumFractionDigits: 2, maximumFractionDigits: 2}));

                        // Llenar tabla de registros
                        var tbody = '';
                        if (response.registros.length > 0) {
                            $.each(response.registros, function(index, reg) {
                                var rowClass = reg.es_domingo ? 'domingo-row' : '';
                                tbody += '<tr class="' + rowClass + '">';
                                tbody += '<td>' + (index + 1) + '</td>';
                                tbody += '<td>' + reg.fecha + '</td>';
                                tbody += '<td>' + reg.entrada + '</td>';
                                tbody += '<td>' + reg.salida + '</td>';
                                tbody += '<td>' + reg.turno + '</td>';
                                tbody += '<td class="text-right">' + reg.ordinario + '</td>';
                                tbody += '<td class="text-right">' + reg.extras + '</td>';
                                tbody += '</tr>';
                            });
                        } else {
                            tbody = '<tr><td colspan="7" class="text-center text-muted">No hay registros procesados para este período.</td></tr>';
                        }
                        $('#modal_registros_body').html(tbody);

                        // Mostrar modal
                        $('#modal_ficha_individual').modal('show');
                    },
                    error: function(xhr) {
                        console.log('Error al cargar ficha:', xhr);
                        console.log('Status:', xhr.status);
                        console.log('Response:', xhr.responseText);

                        var errorMsg = 'Error al cargar la ficha del empleado. Intente nuevamente.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            errorMsg = xhr.responseJSON.error;
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }

                        alert(errorMsg);
                    }
                });
            }

            // Botón cerrar modal
            $('#btn_cerrar_modal').on('click', function() {
                $('#modal_ficha_individual').modal('hide');
            });

            // Botón X del header
            $('#btn_cerrar_modal_x').on('click', function() {
                $('#modal_ficha_individual').modal('hide');
            });
        });
    </script>
@endsection
