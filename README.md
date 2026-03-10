# Hyplast Horas y Empleados - Sistema de Control de Tiempo

## Descripción
Sistema independiente para control de horas trabajadas y gestión de empleados integrado con Softland RRHH, con análisis de asistencia, turnos y conceptos de pago.

## Características Principales
- ⏱️ Registro de horas trabajadas
- 👥 Gestión de empleados
- 📅 Control de turnos
- 💰 Análisis de conceptos
- 📊 Dashboard de asistencia
- 📈 Reportes de productividad
- 🔄 Integración con Softland RRHH
- 📱 Registro móvil

## Modelos Principales
- **Employee**: Empleados
- **Departamento**: Departamentos
- **PuestoEmpleado**: Puestos de trabajo
- **DepartamentoEmpleado**: Asignación departamental
- **ActionEmployee**: Acciones/ponchadoras
- **TypeActionEmployee**: Tipos de acción

## Funcionalidades
- Registro de entrada/salida
- Cálculo de horas ordinarias
- Horas extras (50%, 100%)
- Análisis de días trabajados
- Control de ausentismo
- Generación de reportes
- Exportación a Softland

## API Endpoints
```
GET    /api/employees              # Listar empleados
GET    /api/employees/{id}         # Ver empleado
GET    /api/employees/{id}/hours   # Horas trabajadas
POST   /api/employees/checkin      # Registrar entrada
POST   /api/employees/checkout     # Registrar salida
GET    /api/reports/attendance     # Reporte de asistencia
GET    /api/reports/hours          # Análisis de horas
```

## Tipos de Conceptos
- Base ordinaria (TO)
- Horas extras 50% (EX)
- Horas extras 100% (TD)
- Días trabajados (DT)
- Ausencias
- Permisos

## Dashboard de Horas
- Resumen diario
- Resumen semanal
- Resumen mensual
- Análisis por departamento
- Análisis por empleado
- Costos de nómina

## Integración Softland
Conexión directa a tablas:
- SLEmpleado
- SLAccionEmpleado
- SLDepartamento
- SLPuestoEmpleado

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x
- SQL Server (Softland)

## Instalación
```bash
composer install
php artisan migrate
php artisan db:seed --class=EmpleadosSeeder
```

## Configuración
```env
SOFTLAND_DB_HOST=servidor_softland
SOFTLAND_DB_DATABASE=softlandca
SOFTLAND_SCHEMA=dbo
```

## Uso
```php
// Registrar entrada
$employee->checkIn($timestamp);

// Calcular horas del día
$hours = $employee->calculateDailyHours($date);

// Generar reporte
$report = EmployeeReport::generate($employee, $period);
```

## Reportes Disponibles
- Asistencia diaria
- Horas por empleado
- Horas por departamento
- Análisis de extras
- Costo de nómina
- Ausentismo

## Licencia
Propietario - Hyplast © 2026
