@extends('adminlte::page')


@section('template_fastload_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/cargando.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/switch.css') }}">
@endsection

@section('template_title')
    {!! trans('hyplast.showing-employees') !!}
@endsection

@section('template_linked_css')
    @if(config('hyplast.enabledDatatablesJs'))
        <link rel="stylesheet" type="text/css" href="{{ config('hyplast.datatablesCssCDN') }}">
    @endif
    <style type="text/css" media="screen">
        .machine-table {
          border: 0;
        }
        .machine-table tr td:first-child {
            padding-left: 15px;
        }
        .machine-table tr td:last-child {
            padding-right: 15px;
        }
        .machine-table.table-responsive,
        .machine-table.table-responsive table {
            margin-bottom: 0;
        }
        /* Fondo negro para thead */
        #data-table thead.thead {
            background-color: #343a40 !important;
        }
        #data-table thead.thead th {
            background-color: #343a40 !important;
            color: white !important;
            padding: 6px 8px !important;
            line-height: 1.2 !important;
            font-size: 13px !important;
        }
        /* Botones en línea y tabla compacta */
        #data-table td {
            white-space: nowrap;
            padding: 4px 8px !important;
            line-height: 1.3 !important;
            font-size: 13px !important;
            vertical-align: middle !important;
        }
        #data-table tbody tr {
            height: auto !important;
        }
        /* Botones más compactos */
        #data-table .btn-sm {
            padding: 2px 6px !important;
            font-size: 12px !important;
            line-height: 1.3 !important;
        }
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <div class="card-header">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <span id="card_title">
                                                {!! trans('hyplast.showing-all-employees') !!}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <a class="btn btn-info" href="">
                                            {!! trans('hyplast.buttons.report') !!}
                                        </a>
                                    </div>



                                    <!-- Filtros -->
                                    <div class="card-body bg-light py-2">
                                        <div class="row align-items-end">
                                            <div class="col-md-2">
                                                <div class="form-group mb-0">
                                                    <label for="filter_length" class="font-weight-bold small mb-1">
                                                        <i class="fas fa-list"></i> Mostrar Registros
                                                    </label>
                                                    <div id="data-table_length_container"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label for="filter_departamento" class="font-weight-bold small mb-1">
                                                        <i class="fas fa-building"></i> Departamento
                                                    </label>
                                                    <select id="filter_departamento" class="form-control form-control-sm">
                                                        <option value="">Todos los departamentos</option>
                                                        @foreach($departamentos as $depto)
                                                            <option value="{{ $depto->DEPARTAMENTO }}">{{ $depto->DESCRIPCION }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-0">
                                                    <label for="filter_sexo" class="font-weight-bold small mb-1">
                                                        <i class="fas fa-venus-mars"></i> Género
                                                    </label>
                                                    <select id="filter_sexo" class="form-control form-control-sm">
                                                        <option value="">Todos</option>
                                                        <option value="M">Masculino</option>
                                                        <option value="F">Femenino</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group mb-0">
                                                    <label for="filter_estado" class="font-weight-bold small mb-1">
                                                        <i class="fas fa-toggle-on"></i> Estado
                                                    </label>
                                                    <select id="filter_estado" class="form-control form-control-sm">
                                                        <option value="">Todos los estados</option>
                                                        @foreach($estados as $estado)
                                                            <option value="{{ $estado->ESTADO_EMPLEADO }}">{{ $estado->DESCRIPCION }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group mb-0">
                                                    <button id="btn_filter" class="btn btn-primary btn-sm" style="width: 49%; height: 31px;">
                                                        <i class="fas fa-filter"></i> Aplicar
                                                    </button>
                                                    <button id="btn_clear_filter" class="btn btn-secondary btn-sm" style="width: 49%; height: 31px;">
                                                        <i class="fas fa-times"></i> Limpiar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="table-responsive machine-table">
                                <table id="data-table" class="table table-striped table-bordered table-hover data-table" style="width:100%">
                                    <thead class="thead" style="background-color: #343a40 !important; color: white !important;">
                                        <tr>
                                            <th>{!! trans('hyplast.machines-table.code') !!}</th>
                                            <th>{!! trans('hyplast.machines-table.name') !!}</th>
                                            <th>{!! trans('hyplast.machines-table.sex') !!}</th>
                                            <th>{!! trans('hyplast.machines-table.status') !!}</th>
                                            <th>{!! trans('hyplast.machines-table.init_date') !!}</th>
                                            <th class="hidden-xs">{!! trans('hyplast.machines-table.departament') !!}</th>
                                            <th class="hidden-xs">{!! trans('hyplast.machines-table.position') !!}</th>
                                            <th class="hidden-xs">Salario</th>
                                            <th class="hidden-xs">Hora</th>
                                            <th class="hidden-xs">Horario</th>
                                            <th class="hidden-xs no-search no-sort text-center">{!! trans('hyplast.machines-table.actions') !!}</th>
                                        </tr>
                                    </thead>
                                </table>
                                @include('modals.modal-machineEmployee')
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('footer_scripts')
    @include('scripts.datatables.datatables-employee')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif



    <script type="text/javascript">
        let operation = '';


        function deleteattach(id,product) {
            swal({
                title: "Eliminar?",
                text: "Por Favor, asegurese y luego Confirme!",
                type: "warning",
                showCancelButton: !0,
                confirmButtonText: "Si, Eliminar Registro!",
                cancelButtonText: "No, cancelar!",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    $.ajax({
                        type: 'GET',
                        url: "{{url('/machines/detachproduct')}}/" + id + "/" + product,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("Done!", results.message, "success");
                                modalProduct(id);
                            } else {
                                swal("Error!", results.message, "error");
                            }
                        }
                    });
                } else {
                    e.dismiss;
                }
            }, function (dismiss) {
                return false;
            })
        }


        function attachConfirmation(id) {
            swal({
                title: "Agregar?",
                text: "Se agregará el producto a la Máquina!",
                type: "info",
                showCancelButton: !0,
                confirmButtonText: "Si",
                cancelButtonText: "No",
                reverseButtons: !0
            }).then(function (e) {
                if (e.value === true) {
                    var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
                    var product =  $("#product2 :selected").val();
                    $.ajax({
                        type: 'GET',
                        url: "{{url('/machines/attachproduct')}}/" + id + "/" + product,
                        data: {_token: CSRF_TOKEN},
                        dataType: 'JSON',
                        success: function (results) {
                            if (results.success === true) {
                                swal("Done!", results.message, "success");
                                modalProduct(id);

                            } else {
                                swal("Error!", results.message, "error");
                            }
                        }
                    });
                } else {
                    e.dismiss;
                }
            }, function (dismiss) {
                return false;
            })
        }

        function modalProduct(id) {
            let resultsContainer = $('#modal_result2');
            let title = $('#modal-title');
            let btn01 = $('#btn1');
            let noResulsHtml ='<tr>' +
                                '<td>{!! trans("hyplast.machines-products-no") !!}</td>' +
                                '<tr>';
            resultsContainer.html("");
            title.html("");
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            $.ajax({
                type:'POST',
                url: "{{ url('machinesproducts') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    resultsContainer.html("");
                    title.html("");
                    btn01.html("");
                    btn01.html('<button class="btn  btn-sm btn-primary btn-block" data-bs-target="#" data-bs-toggle="modal" data-toggle="tooltip" onclick="attachConfirmation(' + result.id + ')" type="submit"> {!! trans("hyplast.buttons.create-new6") !!}</button>');
                    title.html('<h5><strong>Máquina: ' + result.name + '</strong></h5>');
                    if (result.products.length != 0) {
                        var i = 0;
                        $.each(result, function() {
                            resultsContainer.append(

                                    '<tr>' +
                                    '<td>' + result.products[i].code + '</td>' +
                                    '<td>' + result.products[i].name + '</td>' +
                                    '<td><button class="btn  btn-sm btn-danger btn-block" type="submit" ' +
                                    'onclick="deleteattach(' + result.id + ',' + result.products[i].id + ')"> {!! trans("hyplast.buttons.delete") !!}</button></td>' +
                                    '<tr>'
                            );
                                i += 1;
                        });

                    } else {
                        resultsContainer.append(noResulsHtml);
                    };

                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        resultsContainer.append(noResulsHtml);
                        title.html('<h5><strong>Máquina: ' + result.name + '</strong></h5>');
                        btn01.html('<button class="btn  btn-sm btn-primary btn-block" data-bs-target="#" data-bs-toggle="modal" data-toggle="tooltip" onclick="attachConfirmation(' + result.id + ')" type="submit"> {!! trans("hyplast.buttons.create-new6") !!}</button>');
                    };
                },
            });

        };

        cancelbutton3.click(function(e) {
            resultsContainer.html('');
        });

        cancelbutton4.click(function(e) {
            resultsContainer.html('');
        });

        function productos(id) {
            let resultsContainer2 = $('#select-result');
            let title2 = $('#modal-title2');
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            resultsContainer2.html("");
            var model2 = '';
            operation = "<strong>Producto</strong>";
            title2.html("");
            $.ajax({
                type:'POST',
                url: "{{ url('reqproduct') }}/" + id,
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    model2 = '';
                    if (result.length != 0) {
                        $('#saveproduct').prop("disabled", false);
                        var i = 0;
                        model2 = '<div class="form-group has-feedback row {{ $errors->has("product") ? " has-error " : "" }}">{!! Form::label("product", trans("forms.create_extruder_label_product"), array("class" => "col-sm-3 control-label")); !!}<div class="col-sm-9"><div class="input-group"><select id="swal2-select" name="swal2-select" class="custom-select form-control" required><option value="">{{ trans("forms.create_extruder_label_selectproduct") }}</option>';
                        $.each(result, function() {
                            model2 = model2 + '<option value=' + result[i].id + '>' + result[i].name + '</option>';
                            i++;
                        });
                        model2 = model2 + '</select><div class="input-group-append overflow-hidden"><label class="input-group-text" for="role"><i class="{{ trans("forms.create_extruder_icon_product") }}" aria-hidden="true"></i></label></div></div>@if ($errors->has("product"))<span class="help-block"><strong>{{ $errors->first("product") }}</strong></span>@endif</div></div>';
                        resultsContainer2.append(model2);
                        title2.append("<h4><strong>SELECCIONE UN PRODUCTO</strong></h4>");
                    } else {
                        model2 = '<div class="form-group overflow-hidden">Error: No Hay Productos registrados para esta máquina</div>';
                        $('#saveproduct').prop("disabled", true);
                        resultsContainer2.append(model2);
                        title2.append("<h4><strong>SELECCIONE UN PRODUCTO</strong></h4>");
                    };
                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        model2 = '';
                        resultsContainer2.html = '';
                    };
                },
            });
        };

        function incidentes() {
            let resultsContainer2 = $('#select-result');
            let title2 = $('#modal-title2');
            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
            resultsContainer2.html("");
            var model2 = '';
            operation = "<strong>Incidente</strong>";
            title2.html("");
            $.ajax({
                type:'POST',
                url: "{{ url('reqincident') }}",
                data: {_token: CSRF_TOKEN},
                success: function (result) {
                    if (result.length != 0) {
                        $('#saveproduct').prop("disabled", false);
                        var i = 0;
                        model2 = '<div class="col-md-11 align-self-center"><div class="input-group"><select id="swal2-select" name="swal2-select" class="custom-select form-control" required><option value="">{{ trans("forms.create_incident_ph_incident") }}</option>';
                        $.each(result, function() {
                            model2 = model2 + '<option value=' + result[i].id + '>' + result[i].name + '</option>';
                            i++;
                        });
                        model2 = model2 + '</select><div class="input-group-append overflow-hidden"><label class="input-group-text" for="role"><i class="{{ trans("forms.create_incident_icon_incident") }}" aria-hidden="true"></i></label></div></div>@if ($errors->has("product"))<span class="help-block"><strong>{{ $errors->first("product") }}</strong></span>@endif';
                        resultsContainer2.append(model2);
                        title2.append("<h4><strong>SELECCIONE UNA INCIDENCIA</strong></h4>");
                    } else {
                        model = model + '<div class="form-group">Error: No Hay Registros de Incidencias. Llame al Administrador de Sistema</div>';
                        $('#saveproduct').prop("disabled", true);
                        resultsContainer2.append(model2);
                        title2.append("<h4><strong>SELECCIONE UNA INCIDENCIA</strong></h4>");
                    };
                },

                error: function (response, status, error) {
                    if (response.status === 422) {
                        resultsContainer2.html='';
                    };
                },
            });
        };

        function cleanModal()
        {
            let resultsContainer2 = $('#select-result');
            resultsContainer2.html('');
            Swal.fire({
                title: 'Error! Operación Cancelada',
                text: 'Cancelada la Operación, la Máquina vuelve a su estado Original!',
                icon: 'error',
                showCancelButton: false,
                showLoaderOnConfirm: false,

            });
            table.draw();
        };

        function saveProductModal()
        {
            if (document.getElementById('swal2-select').value) {
                Swal.fire({
                    title: "¿Está seguro?",
                    html: "Se cambiará el estado de la máquina con la información seleccionada. Pulse Aceptar si está seguro.",
                    showCancelButton: true,
                }).then((result) => {
                    if (result.value) {
                        if (document.getElementById('swal2-select').value) {
                            if(status2==1) {
                                $.ajax({
                                    type: "GET",
                                    dataType: "json",
                                    url: '{{ route('UpdateStatusMachine') }}',
                                    data: {'status2': status2, 'id': data['id'], 'incident_id': 1, 'product': document.getElementById('swal2-select').value},
                                    success: function(data){
                                        table.draw();
                                    }
                                });

                            } else {

                                $.ajax({
                                    type: "GET",
                                    dataType: "json",
                                    url: '{{ route('UpdateStatusMachine') }}',
                                    data: {'status2': status2, 'id': data['id'], 'product': '', 'incident_id': document.getElementById('swal2-select').value},
                                    success: function(data){
                                        table.draw();
                                    }
                                });
                            }
                        } else {
                            Swal.fire({
                                title: 'Error! Operación cancelada',
                                html: '<i class="fa fa-info-circle"></i> Debe Seleccionar un ' +  operation + '<br><br>Cancelada la Operación, la Máquina vuelve a su estado Original!',
                                icon: 'error',
                                showCancelButton: false,
                                showLoaderOnConfirm: false,
                            });
                            table.draw();
                        }
                    } else{
                        Swal.fire({
                            title: 'Error! Operación Cancelada',
                            text: 'Cancelada la Operación, la Máquina vuelve a su estado Original!',
                            icon: 'error',
                            showCancelButton: false,
                            showLoaderOnConfirm: false,

                        });
                        table.draw();
                    }
                });
            } else {
                Swal.fire({
                    title: 'Error! Operación Cancelada',
                    html: 'Debe seleccionar una opción para poder cambiar el estado de la máquina. No hubo cambios. <br>La Máquina vuelve a su estado Original!',
                    icon: 'error',
                    showCancelButton: false,
                    showLoaderOnConfirm: false,

                });
                table.draw();
            }
        }
    </script>
@endsection
