<?php

namespace App\Http\Controllers;

use Auth;
use DB;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Validator;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use App\Helpers\SchemaHelper;
use App\Helpers\ButtonHelper;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schema = SchemaHelper::getSchema();

        // Obtener departamentos únicos
        $departamentos = DB::connection('softland')
            ->table("{$schema}.DEPARTAMENTO")
            ->select('DEPARTAMENTO', 'DESCRIPCION')
            ->orderBy('DESCRIPCION')
            ->get();

        // Obtener estados únicos
        $estados = DB::connection('softland')
            ->table("{$schema}.ESTADO_EMPLEADO")
            ->select('ESTADO_EMPLEADO', 'DESCRIPCION')
            ->orderBy('DESCRIPCION')
            ->get();

        return View('employees.home', compact('departamentos', 'estados'));
    }

    public function employeeData(Request $request)
    {
        $schema = SchemaHelper::getSchema();

        // Usar DB::table directamente para permitir JOINs entre diferentes conexiones
        $query = DB::connection('softland')
                        ->table("{$schema}.EMPLEADO")
                        ->join("{$schema}.ESTADO_EMPLEADO","ESTADO_EMPLEADO.ESTADO_EMPLEADO","=","EMPLEADO.ESTADO_EMPLEADO")
                        ->join("{$schema}.DEPARTAMENTO","DEPARTAMENTO.DEPARTAMENTO","=","EMPLEADO.DEPARTAMENTO")
                        ->join("{$schema}.PUESTO","PUESTO.PUESTO","=","EMPLEADO.PUESTO")
                        // LEFT JOINs con tablas de SOFTLANDCA (ponchador)
                        // Nota: SQL Server permite JOINs entre bases de datos en el mismo servidor
                        ->leftJoin("SOFTLANDCA.{$schema}.empleados as emp_ponchador", 'EMPLEADO.EMPLEADO', '=', 'emp_ponchador.idnumero')
                        ->leftJoin("SOFTLANDCA.{$schema}.ph_planilla as planilla", 'emp_ponchador.idplanilla', '=', 'planilla.idplanilla')
                        ->leftJoin("SOFTLANDCA.{$schema}.ph_horarios as horario", 'emp_ponchador.idhorario', '=', 'horario.idhorario')
                        ->leftJoin("SOFTLANDCA.{$schema}.ph_grupos as grupo", 'emp_ponchador.idgrupo', '=', 'grupo.idgrupo')
                        ->select(
                            "EMPLEADO.EMPLEADO",
                            "EMPLEADO.NOMBRE",
                            "EMPLEADO.SEXO",
                            "ESTADO_EMPLEADO.DESCRIPCION AS ESTADO",
                            "EMPLEADO.FECHA_INGRESO",
                            DB::raw("REPLACE(REPLACE(DEPARTAMENTO.DESCRIPCION, 'Departamento de ', ''), 'Departamento ', '') AS DEPARTAMENTO"),
                            "PUESTO.DESCRIPCION AS PUESTO",
                            // Campos de SOFTLAND
                            "EMPLEADO.SALARIO_REFERENCIA",
                            // Campos de SOFTLANDCA
                            "emp_ponchador.idnumero AS id_ponchador",
                            DB::raw("CAST(emp_ponchador.valor_hora AS DECIMAL(10,2)) AS valor_hora"),
                            "emp_ponchador.idplanilla",
                            "planilla.planilla AS nombre_planilla",
                            "emp_ponchador.idgrupo",
                            "grupo.descripcion AS nombre_grupo",
                            "emp_ponchador.idhorario",
                            "horario.descripcion AS nombre_horario"
                        )
                        ->where("EMPLEADO.ACTIVO", "=", "S")
                        ->whereNotIn('EMPLEADO.PUESTO', ['P002', 'P004', 'P005']);

        // Aplicar filtros si existen
        if ($request->filled('departamento') && $request->departamento != '') {
            $query->where('EMPLEADO.DEPARTAMENTO', $request->departamento);
        }

        if ($request->filled('sexo') && $request->sexo != '') {
            $query->where('EMPLEADO.SEXO', $request->sexo);
        }

        if ($request->filled('estado') && $request->estado != '') {
            $query->where('EMPLEADO.ESTADO_EMPLEADO', $request->estado);
        }

        $data = $query->get();

        return Datatables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function ($data) {
                return '<button class="btn btn-primary btn-sm mr-1" onclick="modalEmployee(' . $data->EMPLEADO . ')"><i class="fas fa-cogs"></i> Máquina</button> ' .
                       '<a href="' . url('binnacles-emp/' . $data->EMPLEADO) . '" class="btn btn-warning btn-sm mr-1"><i class="fas fa-timeline"></i> Bitácora</a> ' .
                       '<a href="' . url('employees/' . $data->EMPLEADO) . '" class="btn btn-info btn-sm"><i class="fas fa-eye"></i> Ver</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $schema = SchemaHelper::getSchema();

        // Obtener datos básicos del empleado desde SOFTLAND
        $employee = DB::connection('softland')
            ->table("{$schema}.EMPLEADO")
            ->join("{$schema}.ESTADO_EMPLEADO", "ESTADO_EMPLEADO.ESTADO_EMPLEADO", "=", "EMPLEADO.ESTADO_EMPLEADO")
            ->join("{$schema}.DEPARTAMENTO", "DEPARTAMENTO.DEPARTAMENTO", "=", "EMPLEADO.DEPARTAMENTO")
            ->join("{$schema}.PUESTO", "PUESTO.PUESTO", "=", "EMPLEADO.PUESTO")
            ->select(
                "EMPLEADO.EMPLEADO",
                "EMPLEADO.NOMBRE",
                "EMPLEADO.ESTADO_EMPLEADO",
                "EMPLEADO.FOTOGRAFIA",
                "EMPLEADO.SEXO",
                "ESTADO_EMPLEADO.DESCRIPCION AS ESTADO_DES",
                "EMPLEADO.FECHA_INGRESO",
                "DEPARTAMENTO.DESCRIPCION AS DEPARTAMENTO",
                "PUESTO.DESCRIPCION AS PUESTO",
                "EMPLEADO.SALARIO_REFERENCIA"
            )
            ->where("EMPLEADO.ACTIVO", "=", "S")
            ->where("EMPLEADO.EMPLEADO", "=", $id)
            ->first();

        // Obtener datos complementarios del empleado desde SOFTLANDCA (ponchador)
        $empleadoPonchador = DB::connection('ponchador')
            ->table("{$schema}.empleados as emp")
            ->leftJoin("{$schema}.ph_planilla as planilla", "emp.idplanilla", "=", "planilla.idplanilla")
            ->leftJoin("{$schema}.ph_horarios as horario", "emp.idhorario", "=", "horario.idhorario")
            ->leftJoin("{$schema}.ph_grupos as grupo", "emp.idgrupo", "=", "grupo.idgrupo")
            ->select(
                DB::raw("CAST(emp.valor_hora AS DECIMAL(10,2)) AS valor_hora"),
                "planilla.planilla AS nombre_planilla",
                "emp.idplanilla as id_planilla",
                "grupo.descripcion AS nombre_grupo",
                "emp.idgrupo as id_grupo",
                "horario.descripcion AS nombre_horario",
                "emp.idhorario as id_horario"
            )
            ->where("emp.idnumero", "=", $id)
            ->first();

        // Combinar datos del ponchador con el empleado
        if ($employee && $empleadoPonchador) {
            $employee->valor_hora = $empleadoPonchador->valor_hora;
            $employee->nombre_planilla = $empleadoPonchador->nombre_planilla;
            $employee->id_planilla = $empleadoPonchador->id_planilla;
            $employee->nombre_grupo = $empleadoPonchador->nombre_grupo;
            $employee->id_grupo = $empleadoPonchador->id_grupo;
            $employee->nombre_horario = $empleadoPonchador->nombre_horario;
            $employee->id_horario = $empleadoPonchador->id_horario;
        }

        // Si el empleado no existe, retornar 404
        if (!$employee) {
            abort(404, 'Empleado no encontrado');
        }

        // Obtener períodos de SOFTLANDCA ordenados del más reciente al más antiguo
        $periodos = DB::connection('ponchador')
            ->table("{$schema}.ph_periodos")
            ->select(
                'idperiodo',
                'inicio',
                'fin',
                DB::raw("CAST(idperiodo AS varchar) + ' - (' + CONVERT(varchar, inicio, 103) + ' - ' + CONVERT(varchar, fin, 103) + ')' as periodo_label")
            )
            ->whereNotNull('idperiodo')
            ->where('idperiodo', '!=', '')
            ->orderByRaw("CAST(idperiodo AS INT) DESC")
            ->get();

        $data = [
            'employee' => $employee,
            'periodos' => $periodos,
        ];

        return view('employees.show')->with($data);
    }

    public function getProcessedMarksData(Request $request, $employeeId)
    {
        $schema = SchemaHelper::getSchema();
        $periodoId = $request->input('periodo_id');

        $periodo = null;
        if ($periodoId) {
            $periodo = DB::connection('ponchador')
                ->table("{$schema}.ph_periodos")
                ->where('idperiodo', $periodoId)
                ->first();
        }

        // PARTIR DE marcas_proceso para obtener TODOS los días (incluso sin distribuciones)
        $query = DB::connection('ponchador')
            ->table("{$schema}.marcas_proceso as mp")
            ->leftJoin("{$schema}.marcas_distribuciones as md", function($join) {
                $join->on('mp.idnumero', '=', 'md.idnumero')
                     ->on('mp.fecha_entra', '=', 'md.fecha');
            })
            ->leftJoin("{$schema}.ph_conceptos as c", 'md.idconcepto', '=', 'c.id')
            ->leftJoin("{$schema}.ph_turnos as t", 'mp.idturno', '=', 't.idturno')
            ->select(
                'mp.fecha_entra as fecha',
                DB::raw("CASE DATEPART(WEEKDAY, mp.fecha_entra)
                    WHEN 1 THEN 'Domingo'
                    WHEN 2 THEN 'Lunes'
                    WHEN 3 THEN 'Martes'
                    WHEN 4 THEN 'Miércoles'
                    WHEN 5 THEN 'Jueves'
                    WHEN 6 THEN 'Viernes'
                    WHEN 7 THEN 'Sábado'
                END + ', ' + CONVERT(varchar, mp.fecha_entra, 103) as fecha_formato"),
                DB::raw("DATEPART(WEEKDAY, mp.fecha_entra) as dia_numero"),
                'mp.hora_entra',
                'mp.hora_sale',
                'mp.idturno',
                't.descripcion as turno_descripcion',
                't.hentra as turno_hentra',
                't.hsale as turno_hsale',
                'md.idconcepto',
                'c.concepto',
                'c.descripcion as concepto_desc',
                'c.tipo_h',
                'c.columnar',
                'md.cantidad'
            )
            ->where('mp.idnumero', $employeeId);

        // Filtrar por período si se proporciona
        if ($periodo) {
            $query->whereBetween('mp.fecha_entra', [$periodo->inicio, $periodo->fin]);
        }

        // Ordenar ASC para agrupar
        $distribuciones = $query->orderBy('mp.fecha_entra', 'ASC')->orderBy('md.idconcepto', 'ASC')->get();

// Agrupar por fecha y calcular totales
        $porFecha = [];
        $baseOrdinaria = 0;
        $horasExtras35 = 0;
        $horasExtras50 = 0;
        $horasDomingo = 0;
        $horasEspeciales = 0;
        $nocturnidad = 0;
        $descuentos = 0;

        foreach ($distribuciones as $dist) {
            $fecha = substr($dist->fecha, 0, 10); // Solo la fecha, sin hora

            if (!isset($porFecha[$fecha])) {
                $porFecha[$fecha] = [
                    'fecha' => $fecha,
                    'fecha_formato' => $dist->fecha_formato,
                    'dia_numero' => $dist->dia_numero,
                    'hora_entra' => $dist->hora_entra ?? '00:00',
                    'hora_sale' => $dist->hora_sale ?? '00:00',
                    'idturno' => $dist->idturno ?? '-',
                    'turno_descripcion' => $dist->turno_descripcion ?? '',
                    'turno_hentra' => $dist->turno_hentra ?? '',
                    'turno_hsale' => $dist->turno_hsale ?? '',
                    'ordinario' => 0,
                    'extras' => 0,
                ];
            }

            // Si no hay distribución/concepto para este día, skip la categorización
            if (!$dist->idconcepto || !$dist->cantidad) {
                continue;
            }

            $cantidad = floatval($dist->cantidad);

            // Categorizar según tipo_h y descripción (mismo criterio que el resumen)
            // 1. Base Ordinaria: concepto = '100' o descripción contiene "Base Ordinaria"
            if ($dist->concepto == '100' || stripos($dist->concepto_desc, 'Base Ordinaria') !== false) {
                $porFecha[$fecha]['ordinario'] += $cantidad;
                $baseOrdinaria += $cantidad;
            }
            // 2. Horas Extras 50%: descripción contiene "50"
            elseif (stripos($dist->concepto_desc, '50') !== false && stripos($dist->concepto_desc, 'Extra') !== false) {
                $porFecha[$fecha]['extras'] += $cantidad;
                $horasExtras50 += $cantidad;
            }
            // 3. Horas Extras 35%: tipo_h=2 (EVALUAR ANTES que Domingo para evitar conflicto)
            elseif ($dist->tipo_h == 2) {
                $porFecha[$fecha]['extras'] += $cantidad;
                $horasExtras35 += $cantidad;
            }
            // 4. Hora Extra 100% (Domingo): tipo_h=4 con "100" en descripción
            elseif ($dist->tipo_h == 4 && stripos($dist->concepto_desc, '100') !== false) {
                $porFecha[$fecha]['extras'] += $cantidad;
                $horasDomingo += $cantidad;
            }
            // 5. Horas Especiales: tipo_h = 3 (Feriados, Días Libres)
            elseif ($dist->tipo_h == 3) {
                $porFecha[$fecha]['extras'] += $cantidad;
                $horasEspeciales += $cantidad;
            }
            // 6. Nocturnidad: descripción contiene "Nocturnidad"
            elseif (stripos($dist->concepto_desc, 'Nocturnidad') !== false) {
                $porFecha[$fecha]['extras'] += $cantidad;
                $nocturnidad += $cantidad;
            }
            // 7. Descuentos: tipo_h = 4 y descripción contiene "Desc"
            elseif ($dist->tipo_h == 4 && stripos($dist->concepto_desc, 'Desc') !== false) {
                // Los descuentos NO se muestran en el DataTable por día
                $descuentos += $cantidad;
            }
        }

        // Convertir a colección
        $data = collect(array_values($porFecha));

        // Calcular tiempo legal según días en el DataTable
        $tiempoLegalTotal = 0;
        foreach ($data as $item) {
            $tiempoLegalDia = 0;
            if ($item['dia_numero'] == 1) { // Domingo
                $tiempoLegalDia = 0;
            } elseif ($item['dia_numero'] == 7) { // Sábado
                $tiempoLegalDia = 4;
            } else { // Lunes a Viernes
                $tiempoLegalDia = 8;
            }
            $tiempoLegalTotal += $tiempoLegalDia;
        }

        // Calcular días del período y límite máximo
        $diasPeriodo = 0;
        $limiteMaximo = 0;
        $colorTotal = 'success'; // Verde por defecto

        if ($periodoId && isset($periodo)) {
            $inicio = \Carbon\Carbon::parse($periodo->inicio);
            $fin = \Carbon\Carbon::parse($periodo->fin);
            $diasPeriodo = $inicio->diffInDays($fin) + 1;

            // Límite máximo: (68h / 7 días) × días_del_período
            $limiteMaximo = (68 / 7) * $diasPeriodo;

            // Calcular total trabajado real: Base Ordinaria + H. Extras 35% + H. Extras 50% + Domingo + H. Especiales + Nocturnidad
            // NO incluir "Descuentos" porque restan horas
            $totalTrabajadoReal = $baseOrdinaria + $horasExtras35 + $horasExtras50 + $horasDomingo + $horasEspeciales + $nocturnidad;

            // Determinar color según el total trabajado real
            if ($totalTrabajadoReal > $limiteMaximo) {
                $colorTotal = 'danger'; // Rojo
            } elseif ($totalTrabajadoReal > $tiempoLegalTotal) {
                $colorTotal = 'warning'; // Naranja
            } else {
                $colorTotal = 'success'; // Verde
            }
        }

        // Invertir para mostrar DESC (más reciente primero)
        $data = $data->reverse()->values();

        return Datatables::of($data)
            ->addIndexColumn()
            ->editColumn('ordinario', function($row) {
                return number_format($row['ordinario'], 2);
            })
            ->editColumn('extras', function($row) {
                return number_format($row['extras'], 2);
            })
            ->with([
                'totales' => [
                    'tiempo_legal' => number_format($tiempoLegalTotal, 2, '.', ''),
                    'tiempo_ord' => number_format($baseOrdinaria, 2, '.', ''),           // Base Ordinaria (concepto='100')
                    'horas_extras_35' => number_format($horasExtras35, 2, '.', ''),      // Horas Extras 35% (tipo_h=2)
                    'horas_extras_50' => number_format($horasExtras50, 2, '.', ''),      // Horas Extras 50% (descripción '50%')
                    'horas_domingo' => number_format($horasDomingo, 2, '.', ''),         // Hora Extra 100% - Domingo (columnar=2)
                    'otros_dias' => number_format($descuentos, 2, '.', ''),              // Descuentos (tipo_h=4 con 'Desc')
                    'horas_especiales' => number_format($horasEspeciales + $nocturnidad, 2, '.', ''), // Especiales + Nocturnidad
                    'color_total' => $colorTotal,
                    'limite_maximo' => number_format($limiteMaximo, 2, '.', '')
                ]
            ])
            ->make(true);
    }

    /**
     * Vista de análisis de horas trabajadas para todos los empleados
     */
    public function hoursAnalysis(Request $request)
    {
        $schema = SchemaHelper::getSchema();

        // Obtener departamentos únicos
        $departamentos = DB::connection('softland')
            ->table("{$schema}.DEPARTAMENTO")
            ->select('DEPARTAMENTO', 'DESCRIPCION')
            ->orderBy('DESCRIPCION')
            ->get();

        // Obtener empleados activos
        $empleados = DB::connection('softland')
            ->table("{$schema}.EMPLEADO")
            ->select('EMPLEADO', 'NOMBRE')
            ->where('ACTIVO', 'S')
            ->whereNotIn('PUESTO', ['P002', 'P004', 'P005'])
            ->orderBy('EMPLEADO')
            ->get();

        // Obtener períodos disponibles
        $periodos = DB::connection('ponchador')
            ->table("{$schema}.ph_periodos")
            ->select(
                'idperiodo',
                'inicio',
                'fin',
                DB::raw("CAST(idperiodo AS varchar) + ' - (' + CONVERT(varchar, inicio, 103) + ' - ' + CONVERT(varchar, fin, 103) + ')' as periodo_label")
            )
            ->whereNotNull('idperiodo')
            ->where('idperiodo', '!=', '')
            ->orderByRaw("CAST(idperiodo AS INT) DESC")
            ->get();

        return view('employees.hours-analysis', compact('departamentos', 'empleados', 'periodos'));
    }

    /**
     * DataTable para análisis de horas trabajadas
     */
    public function hoursAnalysisData(Request $request)
    {
        $schema = SchemaHelper::getSchema();

        // Obtener filtros
        $departamento = $request->input('departamento');
        $empleadoCodigo = $request->input('empleado');
        $periodoId = $request->input('periodo');
        $fechaInicio = $request->input('fecha_inicio');
        $fechaFin = $request->input('fecha_fin');

        // Determinar rango de fechas
        if ($periodoId) {
            $periodo = DB::connection('ponchador')
                ->table("{$schema}.ph_periodos")
                ->where('idperiodo', $periodoId)
                ->first();

            if ($periodo) {
                $fechaInicio = $periodo->inicio;
                $fechaFin = $periodo->fin;
            }
        }

        // Si no hay rango de fechas, no retornar datos
        if (!$fechaInicio || !$fechaFin) {
            return DataTables::of(collect([]))->make(true);
        }

        // Obtener empleados según filtros
        $queryEmpleados = DB::connection('softland')
            ->table("{$schema}.EMPLEADO")
            ->select('EMPLEADO', 'NOMBRE', 'DEPARTAMENTO', 'SALARIO_REFERENCIA')
            ->where('ACTIVO', 'S')
            ->whereNotIn('PUESTO', ['P002', 'P004', 'P005']);

        if ($departamento) {
            $queryEmpleados->where('DEPARTAMENTO', $departamento);
        }

        if ($empleadoCodigo) {
            $queryEmpleados->where('EMPLEADO', $empleadoCodigo);
        }

        $empleados = $queryEmpleados->orderBy('EMPLEADO')->get();

        // Calcular resumen para cada empleado
        $resultados = [];
        $costoTotal35 = 0;
        $costoTotal50 = 0;
        $costoTotalDomingo = 0;
        $costoTotalEspeciales = 0;

        foreach ($empleados as $empleado) {
            // Obtener valor de hora nómina (salario / 23.83 / 8)
            $valorHoraNomina = 0;
            if ($empleado->SALARIO_REFERENCIA) {
                $valorHoraNomina = $empleado->SALARIO_REFERENCIA / 23.83 / 8;
            }

            // Obtener distribuciones del empleado en el rango de fechas
            $distribuciones = DB::connection('ponchador')
                ->table("{$schema}.marcas_distribuciones as md")
                ->join("{$schema}.ph_conceptos as pc", 'md.idconcepto', '=', 'pc.id')
                ->select(
                    'md.idconcepto',
                    'pc.descripcion',
                    'pc.tipo_h',
                    'pc.columnar',
                    'pc.concepto',
                    DB::raw('SUM(CAST(md.cantidad AS FLOAT)) as total')
                )
                ->where('md.idnumero', $empleado->EMPLEADO)
                ->whereBetween('md.fecha', [$fechaInicio, $fechaFin])
                ->groupBy('md.idconcepto', 'pc.descripcion', 'pc.tipo_h', 'pc.columnar', 'pc.concepto')
                ->get();

            // Categorización
            $baseOrdinaria = 0;
            $horasExtras35 = 0;
            $horasExtras50 = 0;
            $horasDomingo = 0;
            $horasEspeciales = 0;
            $nocturnidad = 0;
            $descuentos = 0;

            foreach ($distribuciones as $dist) {
                $total = floatval($dist->total);

                // Aplicar misma lógica de categorización
                if ($dist->concepto == '100' || stripos($dist->descripcion, 'Base Ordinaria') !== false) {
                    $baseOrdinaria += $total;
                }
                elseif (stripos($dist->descripcion, '50') !== false && stripos($dist->descripcion, 'Extra') !== false) {
                    $horasExtras50 += $total;
                }
                elseif ($dist->tipo_h == 2) {
                    $horasExtras35 += $total;
                }
                elseif ($dist->tipo_h == 4 && stripos($dist->descripcion, '100') !== false) {
                    $horasDomingo += $total;
                }
                elseif ($dist->tipo_h == 3) {
                    $horasEspeciales += $total;
                }
                elseif (stripos($dist->descripcion, 'Nocturnidad') !== false) {
                    $nocturnidad += $total;
                }
                elseif ($dist->tipo_h == 4 && stripos($dist->descripcion, 'Desc') !== false) {
                    $descuentos += $total;
                }
            }

            // Calcular tiempo legal del período
            $tiempoLegal = 0;
            if ($periodoId && isset($periodo)) {
                $inicio = \Carbon\Carbon::parse($periodo->inicio);
                $fin = \Carbon\Carbon::parse($periodo->fin);

                for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                    $diaSemana = $fecha->dayOfWeek; // 0=Domingo, 6=Sábado
                    if ($diaSemana == 0) { // Domingo
                        $tiempoLegal += 0;
                    } elseif ($diaSemana == 6) { // Sábado
                        $tiempoLegal += 4;
                    } else { // Lunes a Viernes
                        $tiempoLegal += 8;
                    }
                }
            } else {
                // Si no hay período, calcular por fechas
                $inicio = \Carbon\Carbon::parse($fechaInicio);
                $fin = \Carbon\Carbon::parse($fechaFin);

                for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                    $diaSemana = $fecha->dayOfWeek;
                    if ($diaSemana == 0) {
                        $tiempoLegal += 0;
                    } elseif ($diaSemana == 6) {
                        $tiempoLegal += 4;
                    } else {
                        $tiempoLegal += 8;
                    }
                }
            }

            // Total trabajado
            $totalTrabajado = $baseOrdinaria + $horasExtras35 + $horasExtras50 + $horasDomingo + $horasEspeciales + $nocturnidad;

            // Calcular límite máximo: (68h / 7 días) × días_del_período
            $limiteMaximo = 0;
            if ($periodoId && isset($periodo)) {
                $inicio = \Carbon\Carbon::parse($periodo->inicio);
                $fin = \Carbon\Carbon::parse($periodo->fin);
                $diasPeriodo = $inicio->diffInDays($fin) + 1;
                $limiteMaximo = (68 / 7) * $diasPeriodo;
            } else {
                $inicio = \Carbon\Carbon::parse($fechaInicio);
                $fin = \Carbon\Carbon::parse($fechaFin);
                $diasPeriodo = $inicio->diffInDays($fin) + 1;
                $limiteMaximo = (68 / 7) * $diasPeriodo;
            }

            // Calcular costos por tipo de hora extra
            $costoExtras35 = $valorHoraNomina * 1.35 * $horasExtras35;
            $costoExtras50 = $valorHoraNomina * 1.50 * $horasExtras50;
            $costoDomingo = $valorHoraNomina * 2.00 * $horasDomingo;
            $costoEspeciales = $valorHoraNomina * 2.00 * ($horasEspeciales + $nocturnidad);

            // Calcular costo total del empleado
            $costoTotalEmpleado = $costoExtras35 + $costoExtras50 + $costoDomingo + $costoEspeciales;

            // Acumular costos totales
            $costoTotal35 += $costoExtras35;
            $costoTotal50 += $costoExtras50;
            $costoTotalDomingo += $costoDomingo;
            $costoTotalEspeciales += $costoEspeciales;

            // Obtener nombre del departamento
            $deptInfo = DB::connection('softland')
                ->table("{$schema}.DEPARTAMENTO")
                ->where('DEPARTAMENTO', $empleado->DEPARTAMENTO)
                ->first();

            $resultados[] = [
                'departamento' => $deptInfo ? $deptInfo->DESCRIPCION : $empleado->DEPARTAMENTO,
                'codigo' => $empleado->EMPLEADO,
                'nombre' => $empleado->NOMBRE,
                'tiempo_legal' => $tiempoLegal,
                'base_ordinaria' => $baseOrdinaria,
                'horas_extras_35' => $horasExtras35,
                'horas_extras_50' => $horasExtras50,
                'horas_domingo' => $horasDomingo,
                'horas_especiales' => $horasEspeciales + $nocturnidad,
                'no_trabajado' => $descuentos,
                'total_trabajado' => $totalTrabajado,
                'costo_total_empleado' => $costoTotalEmpleado,
                'limite_maximo' => $limiteMaximo,
            ];
        }

        // Calcular totales generales
        $totalHorasExtras = array_sum(array_column($resultados, 'horas_extras_35')) +
                           array_sum(array_column($resultados, 'horas_extras_50')) +
                           array_sum(array_column($resultados, 'horas_domingo'));

        $costoTotalExtras = $costoTotal35 + $costoTotal50 + $costoTotalDomingo + $costoTotalEspeciales;

        $totales = [
            'horas_extras_35' => number_format(array_sum(array_column($resultados, 'horas_extras_35')), 2),
            'horas_extras_50' => number_format(array_sum(array_column($resultados, 'horas_extras_50')), 2),
            'horas_domingo' => number_format(array_sum(array_column($resultados, 'horas_domingo')), 2),
            'horas_especiales' => number_format(array_sum(array_column($resultados, 'horas_especiales')), 2),
            'total_horas_extras' => number_format($totalHorasExtras, 2),
            'costo_extras_35' => number_format($costoTotal35, 2),
            'costo_extras_50' => number_format($costoTotal50, 2),
            'costo_domingo' => number_format($costoTotalDomingo, 2),
            'costo_especiales' => number_format($costoTotalEspeciales, 2),
            'costo_total_extras' => number_format($costoTotalExtras, 2),
        ];

        return DataTables::of(collect($resultados))
            ->addIndexColumn()
            ->editColumn('tiempo_legal', function($row) {
                return number_format($row['tiempo_legal'], 2);
            })
            ->editColumn('base_ordinaria', function($row) {
                return number_format($row['base_ordinaria'], 2);
            })
            ->editColumn('horas_extras_35', function($row) {
                return number_format($row['horas_extras_35'], 2);
            })
            ->editColumn('horas_extras_50', function($row) {
                return number_format($row['horas_extras_50'], 2);
            })
            ->editColumn('horas_domingo', function($row) {
                return '<span class="text-danger font-weight-bold">' . number_format($row['horas_domingo'], 2) . '</span>';
            })
            ->editColumn('horas_especiales', function($row) {
                return number_format($row['horas_especiales'], 2);
            })
            ->editColumn('no_trabajado', function($row) {
                return '<span class="text-warning">' . number_format($row['no_trabajado'], 2) . '</span>';
            })
            ->editColumn('total_trabajado', function($row) {
                $color = 'success';
                if ($row['total_trabajado'] > $row['limite_maximo']) {
                    $color = 'danger';
                } elseif ($row['total_trabajado'] > $row['tiempo_legal']) {
                    $color = 'warning';
                }
                return '<span class="badge badge-' . $color . '">' . number_format($row['total_trabajado'], 2) . '</span>';
            })
            ->editColumn('costo_total_empleado', function($row) {
                $color = 'success';
                if ($row['total_trabajado'] > $row['limite_maximo']) {
                    $color = 'danger';
                } elseif ($row['total_trabajado'] > $row['tiempo_legal']) {
                    $color = 'warning';
                }
                return '<span class="text-' . $color . ' font-weight-bold">RD$ ' . number_format($row['costo_total_empleado'], 2) . '</span>';
            })
            ->addColumn('action', function($row) {
                return '<button class="btn btn-info btn-sm btn-ver-ficha" data-empleado="' . $row['codigo'] . '" title="Ver Ficha Individual">
                            <i class="fas fa-eye"></i>
                        </button>';
            })
            ->rawColumns(['horas_domingo', 'no_trabajado', 'total_trabajado', 'costo_total_empleado', 'action'])
            ->with('totales', $totales)
            ->make(true);
    }

    /**
     * Obtener empleados filtrados por departamento para AJAX
     */
    public function getEmployeesByDepartment($departamento = null)
    {
        $schema = SchemaHelper::getSchema();

        $query = DB::connection('softland')
            ->table("{$schema}.EMPLEADO")
            ->select('EMPLEADO', 'NOMBRE')
            ->where('ACTIVO', 'S')
            ->whereNotIn('PUESTO', ['P002', 'P004', 'P005']);

        if ($departamento) {
            $query->where('DEPARTAMENTO', $departamento);
        }

        $empleados = $query->orderBy('EMPLEADO')->get();

        return response()->json($empleados);
    }

    /**
     * Obtener ficha individual del empleado con registros procesados
     */
    public function fichaIndividual(Request $request)
    {
        try {
            $schema = SchemaHelper::getSchema();
            $empleadoCodigo = $request->input('empleado');
            $periodoId = $request->input('periodo');

            // Obtener datos del empleado
            $empleado = DB::connection('softland')
                ->table("{$schema}.EMPLEADO as e")
                ->leftJoin("{$schema}.DEPARTAMENTO as d", 'e.DEPARTAMENTO', '=', 'd.DEPARTAMENTO')
                ->select(
                    'e.EMPLEADO as codigo',
                    'e.NOMBRE as nombre',
                    'd.DESCRIPCION as departamento',
                    'e.FOTOGRAFIA as foto',
                    'e.SALARIO_REFERENCIA as salario'
                )
                ->where('e.EMPLEADO', $empleadoCodigo)
                ->first();

        // Obtener datos del ponchador (horario y planilla)
        $empleadoPonchador = DB::connection('ponchador')
            ->table("{$schema}.empleados as emp")
            ->leftJoin("{$schema}.ph_horarios as horario", "emp.idhorario", "=", "horario.idhorario")
            ->leftJoin("{$schema}.ph_planilla as planilla", "emp.idplanilla", "=", "planilla.idplanilla")
            ->select(
                "horario.descripcion AS nombre_horario",
                "emp.idhorario as id_horario",
                "planilla.planilla AS nombre_planilla",
                "emp.idplanilla as id_planilla"
            )
            ->where("emp.idnumero", "=", $empleadoCodigo)
            ->first();

        // Combinar datos
        if ($empleado && $empleadoPonchador) {
            $empleado->nombre_horario = $empleadoPonchador->nombre_horario;
            $empleado->id_horario = $empleadoPonchador->id_horario;
            $empleado->nombre_planilla = $empleadoPonchador->nombre_planilla;
            $empleado->id_planilla = $empleadoPonchador->id_planilla;
        }

        // Codificar foto en base64 si existe
        if ($empleado->foto) {
            $empleado->foto = base64_encode($empleado->foto);
        }

        // Obtener período
        $periodo = DB::connection('ponchador')
            ->table("{$schema}.ph_periodos")
            ->where('idperiodo', $periodoId)
            ->first();

        if (!$periodo) {
            return response()->json(['error' => 'Período no encontrado'], 404);
        }

        $fechaInicio = $periodo->inicio;
        $fechaFin = $periodo->fin;
        $periodoLabel = $periodoId . ' - (' . \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') . ')';

        // PARTIR DE marcas_proceso para obtener TODOS los días
        $distribuciones = DB::connection('ponchador')
            ->table("{$schema}.marcas_proceso as mp")
            ->leftJoin("{$schema}.marcas_distribuciones as md", function($join) {
                $join->on('mp.idnumero', '=', 'md.idnumero')
                     ->on('mp.fecha_entra', '=', 'md.fecha');
            })
            ->leftJoin("{$schema}.ph_conceptos as pc", 'md.idconcepto', '=', 'pc.id')
            ->leftJoin("{$schema}.ph_turnos as t", 'mp.idturno', '=', 't.idturno')
            ->select(
                'mp.fecha_entra as fecha',
                'mp.hora_entra as entrada',
                'mp.hora_sale as salida',
                't.descripcion as turno',
                'md.idconcepto',
                'pc.descripcion',
                'pc.tipo_h',
                'pc.columnar',
                'pc.concepto',
                DB::raw('CAST(md.cantidad AS FLOAT) as cantidad')
            )
            ->where('mp.idnumero', $empleadoCodigo)
            ->whereBetween('mp.fecha_entra', [$fechaInicio, $fechaFin])
            ->orderBy('mp.fecha_entra')
            ->get();

        // Agrupar por fecha
        $registrosPorFecha = [];
        foreach ($distribuciones as $dist) {
            $fecha = \Carbon\Carbon::parse($dist->fecha)->format('Y-m-d');
            if (!isset($registrosPorFecha[$fecha])) {
                $registrosPorFecha[$fecha] = [
                    'fecha' => $fecha,
                    'entrada' => $dist->entrada ?: '00:00',
                    'salida' => $dist->salida ?: '00:00',
                    'turno' => $dist->turno ?: '',
                    'ordinario' => 0,
                    'extras' => 0,
                    'es_domingo' => \Carbon\Carbon::parse($fecha)->dayOfWeek == 0,
                ];
            }

            // Si no hay distribución, skip categorización
            if (!$dist->idconcepto || !$dist->cantidad) {
                continue;
            }

            $cantidad = floatval($dist->cantidad);

            // Categorizar horas
            if ($dist->concepto == '100' || stripos($dist->descripcion, 'Base Ordinaria') !== false) {
                $registrosPorFecha[$fecha]['ordinario'] += $cantidad;
            } elseif (stripos($dist->descripcion, '50') !== false && stripos($dist->descripcion, 'Extra') !== false) {
                $registrosPorFecha[$fecha]['extras'] += $cantidad;
            } elseif ($dist->tipo_h == 2 || $dist->tipo_h == 3 || $dist->tipo_h == 4) {
                $registrosPorFecha[$fecha]['extras'] += $cantidad;
            }
        }

        // Convertir a array y formatear
        $registros = [];
        foreach ($registrosPorFecha as $registro) {
            $registros[] = [
                'fecha' => \Carbon\Carbon::parse($registro['fecha'])->format('d/m/Y'),
                'entrada' => $registro['entrada'],
                'salida' => $registro['salida'],
                'turno' => $registro['turno'],
                'ordinario' => number_format($registro['ordinario'], 2),
                'extras' => number_format($registro['extras'], 2),
                'es_domingo' => $registro['es_domingo'],
            ];
        }

        // Calcular resumen
        $baseOrdinaria = 0;
        $horasExtras35 = 0;
        $horasExtras50 = 0;
        $horasDomingo = 0;
        $horasEspeciales = 0;
        $nocturnidad = 0;
        $descuentos = 0;

        $distribucionesEmpleado = DB::connection('ponchador')
            ->table("{$schema}.marcas_distribuciones as md")
            ->join("{$schema}.ph_conceptos as pc", 'md.idconcepto', '=', 'pc.id')
            ->select(
                'md.idconcepto',
                'pc.descripcion',
                'pc.tipo_h',
                'pc.concepto',
                DB::raw('SUM(CAST(md.cantidad AS FLOAT)) as total')
            )
            ->where('md.idnumero', $empleadoCodigo)
            ->whereBetween('md.fecha', [$fechaInicio, $fechaFin])
            ->groupBy('md.idconcepto', 'pc.descripcion', 'pc.tipo_h', 'pc.concepto')
            ->get();

        foreach ($distribucionesEmpleado as $dist) {
            $total = floatval($dist->total);

            if ($dist->concepto == '100' || stripos($dist->descripcion, 'Base Ordinaria') !== false) {
                $baseOrdinaria += $total;
            } elseif (stripos($dist->descripcion, '50') !== false && stripos($dist->descripcion, 'Extra') !== false) {
                $horasExtras50 += $total;
            } elseif ($dist->tipo_h == 2) {
                $horasExtras35 += $total;
            } elseif ($dist->tipo_h == 4 && stripos($dist->descripcion, '100') !== false) {
                $horasDomingo += $total;
            } elseif ($dist->tipo_h == 3) {
                $horasEspeciales += $total;
            } elseif (stripos($dist->descripcion, 'Nocturnidad') !== false) {
                $nocturnidad += $total;
            } elseif ($dist->tipo_h == 4 && stripos($dist->descripcion, 'Desc') !== false) {
                $descuentos += $total;
            }
        }

        // Calcular tiempo legal
        $tiempoLegal = 0;
        $inicio = \Carbon\Carbon::parse($fechaInicio);
        $fin = \Carbon\Carbon::parse($fechaFin);

        for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
            $diaSemana = $fecha->dayOfWeek;
            if ($diaSemana == 0) {
                $tiempoLegal += 0;
            } elseif ($diaSemana == 6) {
                $tiempoLegal += 4;
            } else {
                $tiempoLegal += 8;
            }
        }

        // Calcular total trabajado real (incluye nocturnidad)
        $totalTrabajado = $baseOrdinaria + $horasExtras35 + $horasExtras50 + $horasDomingo + $horasEspeciales + $nocturnidad;

        // Calcular límite máximo y determinar color
        $diasPeriodo = $inicio->diffInDays($fin) + 1;
        $limiteMaximo = (68 / 7) * $diasPeriodo;
        $colorTotal = 'success'; // Verde por defecto

        if ($totalTrabajado > $limiteMaximo) {
            $colorTotal = 'danger'; // Rojo
        } elseif ($totalTrabajado > $tiempoLegal) {
            $colorTotal = 'warning'; // Naranja
        } else {
            $colorTotal = 'success'; // Verde
        }

        $resumen = [
            'tiempo_legal' => number_format($tiempoLegal, 2),
            'base_ordinaria' => number_format($baseOrdinaria, 2),
            'horas_extras_35' => number_format($horasExtras35, 2),
            'horas_extras_50' => number_format($horasExtras50, 2),
            'horas_domingo' => number_format($horasDomingo, 2),
            'horas_especiales' => number_format($horasEspeciales, 2),
            'no_trabajado' => number_format($descuentos, 2),
            'total_trabajado' => number_format($totalTrabajado, 2),
            'limite_maximo' => number_format($limiteMaximo, 2),
            'color_total' => $colorTotal,
        ];

        return response()->json([
            'empleado' => $empleado,
            'periodo_label' => $periodoLabel,
            'resumen' => $resumen,
            'registros' => $registros,
        ]);

        } catch (\Exception $e) {
            Log::error('Error en fichaIndividual: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Error al cargar la ficha: ' . $e->getMessage(),
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => basename($e->getFile())
            ], 500);
        }
    }
}
