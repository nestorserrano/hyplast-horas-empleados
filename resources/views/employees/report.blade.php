@extends('adminlte::page')


@section('template_fastload_css')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/cargando.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/switch.css') }}">
@endsection

@section('template_title')
    {!! trans('hyplast.showing-all-machines2') !!}
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
    </style>
@endsection


@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <div class="pull-right">
                                <a href="{{ route('machines') }}" class="btn btn-light btn-sm float-right" data-toggle="tooltip" data-placement="top" title="{{ trans('hyplast.tooltips.back-machines') }}">
                                    <i class="fa fa-fw fa-reply-all" aria-hidden="true"></i>
                                    {!! trans('hyplast.buttons.back-to-machines') !!}
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                      <div class="table-responsive machine-table">
                            <table id="data-table" class="table table-striped table-bordered shadow-lg table-sm data-table" style="width:100%">
                                <thead class="thead">
                                    <tr>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.id') !!}</th>
                                        <th>{!! trans('hyplast.machines-table.code') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.name') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.category') !!}</th>
                                        <th>{!! trans('hyplast.machines-table.status') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.locations') !!}</th>
                                        <th >{!! trans('hyplast.machines-table.incident') !!}</th>
                                        <th class="hidden-xs">{!! trans('hyplast.machines-table.updated') !!}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection

@section('footer_scripts')
    @include('scripts.datatables.datatables-report-machine')
    @include('scripts.save-modal-script')
    @if(config('hyplast.tooltipsEnabled'))
        @include('scripts.tooltips')
    @endif



    <script type="text/javascript">


    </script>
@endsection
