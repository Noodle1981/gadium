<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\Budget;
use App\Models\HourDetail;
use App\Models\PurchaseDetail;
use App\Models\BoardDetail;
use App\Models\AutomationProject;
use App\Models\ClientSatisfactionResponse;
use App\Models\StaffSatisfactionResponse;

class PublicDataController extends Controller
{
    /**
     * Apply date range filter compatible with SQLite and MySQL.
     * Uses where >= fecha_inicio and where < (fecha_fin + 1 day)
     * to correctly handle dates stored with or without time component.
     */
    private function applyDateFilter($query, string $column, Request $request, string $fromParam = 'fecha_inicio', string $toParam = 'fecha_fin')
    {
        if ($request->filled($fromParam)) {
            $query->where($column, '>=', $request->input($fromParam));
        }

        if ($request->filled($toParam)) {
            $endDate = \Carbon\Carbon::parse($request->input($toParam))->addDay()->format('Y-m-d');
            $query->where($column, '<', $endDate);
        }

        return $query;
    }

    /**
     * Get all sales data
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam cliente string Filter by client name (partial match)
     */
    public function sales(Request $request)
    {
        $query = Sale::with('client')
            ->select([
                'id', 'fecha', 'client_id', 'cliente_nombre', 'monto', 'moneda',
                'comprobante', 'cod_cli', 'n_remito', 't_comp', 'cond_vta',
                'porc_desc', 'cotiz', 'cod_transp', 'nom_transp', 'cod_articu',
                'descripcio', 'cod_dep', 'um', 'cantidad', 'precio', 'tot_s_imp',
                'created_at', 'updated_at'
            ])
            ->orderBy('fecha', 'desc');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('cliente')) {
            $query->where('cliente_nombre', 'like', '%' . $request->cliente . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all budgets data
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam cliente string Filter by client name (partial match)
     * @queryParam estado string Filter by status
     */
    public function budgets(Request $request)
    {
        $query = Budget::with(['client', 'project', 'costCenter'])
            ->select([
                'id', 'fecha', 'client_id', 'cliente_nombre', 'monto', 'moneda',
                'comprobante', 'centro_costo', 'cost_center_id', 'nombre_proyecto',
                'project_id', 'fecha_oc', 'fecha_estimada_culminacion', 'estado_proyecto_dias',
                'fecha_culminacion_real', 'estado', 'enviado_facturar', 'nro_factura',
                'porc_facturacion', 'saldo', 'horas_ponderadas',
                'created_at', 'updated_at'
            ])
            ->orderBy('fecha', 'desc');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('cliente')) {
            $query->where('cliente_nombre', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all hours data
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam personal string Filter by employee name (partial match)
     * @queryParam proyecto string Filter by project name (partial match)
     * @queryParam ano int Filter by year
     * @queryParam mes int Filter by month
     */
    public function hours(Request $request)
    {
        $query = HourDetail::with(['user', 'jobFunction', 'guardia', 'project'])
            ->select([
                'id', 'dia', 'fecha', 'ano', 'mes', 'personal', 'funcion',
                'proyecto', 'project_id', 'horas_ponderadas', 'ponderador',
                'hs', 'hs_comun', 'hs_50', 'hs_100', 'hs_viaje', 'hs_pernoctada',
                'hs_adeudadas', 'vianda', 'observacion', 'programacion',
                'user_id', 'job_function_id', 'guardia_id',
                'created_at', 'updated_at'
            ])
            ->orderBy('fecha', 'desc');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('personal')) {
            $query->where('personal', 'like', '%' . $request->personal . '%');
        }

        if ($request->filled('proyecto')) {
            $query->where('proyecto', 'like', '%' . $request->proyecto . '%');
        }

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all purchases data
     *
     * @queryParam ano int Filter by specific year
     * @queryParam ano_desde int Filter from year
     * @queryParam ano_hasta int Filter to year
     * @queryParam fecha_inicio date Filter from created date (Y-m-d)
     * @queryParam fecha_fin date Filter to created date (Y-m-d)
     * @queryParam empresa string Filter by company (partial match)
     * @queryParam cc string Filter by cost center code (partial match)
     */
    public function purchases(Request $request)
    {
        $query = PurchaseDetail::with(['supplier', 'costCenter'])
            ->select([
                'id', 'moneda', 'cc', 'ano', 'empresa', 'descripcion',
                'materiales_presupuestados', 'materiales_comprados',
                'resto_valor', 'resto_porcentaje', 'porcentaje_facturacion',
                'supplier_id', 'cost_center_id',
                'created_at', 'updated_at'
            ])
            ->orderBy('ano', 'desc');

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('ano_desde')) {
            $query->where('ano', '>=', $request->ano_desde);
        }

        if ($request->filled('ano_hasta')) {
            $query->where('ano', '<=', $request->ano_hasta);
        }

        $this->applyDateFilter($query, 'created_at', $request);

        if ($request->filled('empresa')) {
            $query->where('empresa', 'like', '%' . $request->empresa . '%');
        }

        if ($request->filled('cc')) {
            $query->where('cc', 'like', '%' . $request->cc . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all boards data
     *
     * @queryParam ano int Filter by specific year
     * @queryParam ano_desde int Filter from year
     * @queryParam ano_hasta int Filter to year
     * @queryParam fecha_inicio date Filter from created date (Y-m-d)
     * @queryParam fecha_fin date Filter to created date (Y-m-d)
     * @queryParam cliente string Filter by client (partial match)
     * @queryParam proyecto_numero string Filter by project number (partial match)
     */
    public function boards(Request $request)
    {
        $query = BoardDetail::with(['project', 'client'])
            ->select([
                'id', 'ano', 'proyecto_numero', 'cliente', 'descripcion_proyecto',
                'project_id', 'client_id', 'columnas', 'gabinetes', 'potencia',
                'pot_control', 'control', 'intervencion', 'documento_correccion_fallas',
                'created_at', 'updated_at'
            ])
            ->orderBy('ano', 'desc');

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('ano_desde')) {
            $query->where('ano', '>=', $request->ano_desde);
        }

        if ($request->filled('ano_hasta')) {
            $query->where('ano', '<=', $request->ano_hasta);
        }

        $this->applyDateFilter($query, 'created_at', $request);

        if ($request->filled('cliente')) {
            $query->where('cliente', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('proyecto_numero')) {
            $query->where('proyecto_numero', 'like', '%' . $request->proyecto_numero . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all automation projects data
     *
     * @queryParam fecha_inicio date Filter from created date (Y-m-d)
     * @queryParam fecha_fin date Filter to created date (Y-m-d)
     * @queryParam cliente string Filter by client (partial match)
     * @queryParam proyecto_id string Filter by project ID (partial match)
     */
    public function automationProjects(Request $request)
    {
        $query = AutomationProject::with(['project', 'client'])
            ->select([
                'id', 'proyecto_id', 'cliente', 'proyecto_descripcion',
                'project_id', 'client_id', 'fat', 'pem',
                'created_at', 'updated_at'
            ])
            ->orderBy('created_at', 'desc');

        $this->applyDateFilter($query, 'created_at', $request);

        if ($request->filled('cliente')) {
            $query->where('cliente', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('proyecto_id')) {
            $query->where('proyecto_id', 'like', '%' . $request->proyecto_id . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all client satisfaction responses
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam cliente string Filter by client name (partial match)
     * @queryParam proyecto string Filter by project (partial match)
     */
    public function clientSatisfaction(Request $request)
    {
        $query = ClientSatisfactionResponse::with('client')
            ->select([
                'id', 'fecha', 'client_id', 'cliente_nombre', 'proyecto',
                'pregunta_1', 'pregunta_2', 'pregunta_3', 'pregunta_4',
                'created_at', 'updated_at'
            ])
            ->orderBy('fecha', 'desc');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('cliente')) {
            $query->where('cliente_nombre', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('proyecto')) {
            $query->where('proyecto', 'like', '%' . $request->proyecto . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get all staff satisfaction responses
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam personal string Filter by employee name (partial match)
     */
    public function staffSatisfaction(Request $request)
    {
        $query = StaffSatisfactionResponse::select([
                'id', 'personal', 'fecha',
                'p1_mal', 'p1_normal', 'p1_bien',
                'p2_mal', 'p2_normal', 'p2_bien',
                'p3_mal', 'p3_normal', 'p3_bien',
                'p4_mal', 'p4_normal', 'p4_bien',
                'created_at', 'updated_at'
            ])
            ->orderBy('fecha', 'desc');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('personal')) {
            $query->where('personal', 'like', '%' . $request->personal . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->get(),
        ]);
    }

    /**
     * Get a single sale by ID
     */
    public function showSale($id)
    {
        $sale = Sale::with('client')->find($id);

        if (!$sale) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $sale]);
    }

    /**
     * Get a single budget by ID
     */
    public function showBudget($id)
    {
        $budget = Budget::with(['client', 'project', 'costCenter'])->find($id);

        if (!$budget) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $budget]);
    }

    /**
     * Get a single hour detail by ID
     */
    public function showHour($id)
    {
        $hour = HourDetail::with(['user', 'jobFunction', 'guardia', 'project'])->find($id);

        if (!$hour) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $hour]);
    }

    /**
     * Get a single purchase detail by ID
     */
    public function showPurchase($id)
    {
        $purchase = PurchaseDetail::with(['supplier', 'costCenter'])->find($id);

        if (!$purchase) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $purchase]);
    }

    /**
     * Get a single board detail by ID
     */
    public function showBoard($id)
    {
        $board = BoardDetail::with(['project', 'client'])->find($id);

        if (!$board) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $board]);
    }

    /**
     * Get a single automation project by ID
     */
    public function showAutomationProject($id)
    {
        $project = AutomationProject::with(['project', 'client'])->find($id);

        if (!$project) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $project]);
    }

    /**
     * Get a single client satisfaction response by ID
     */
    public function showClientSatisfaction($id)
    {
        $response = ClientSatisfactionResponse::with('client')->find($id);

        if (!$response) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $response]);
    }

    /**
     * Get a single staff satisfaction response by ID
     */
    public function showStaffSatisfaction($id)
    {
        $response = StaffSatisfactionResponse::find($id);

        if (!$response) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        return response()->json(['success' => true, 'data' => $response]);
    }

    // ========================================
    // METRICS / ANALYTICS ENDPOINTS
    // ========================================

    /**
     * Get sales totals grouped by client with percentages
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function salesByClient(Request $request)
    {
        $query = Sale::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $salesByClient = $query
            ->selectRaw('cliente_nombre, SUM(monto) as total')
            ->groupBy('cliente_nombre')
            ->orderByDesc('total')
            ->get();

        $grandTotal = $salesByClient->sum('total');

        $result = $salesByClient->map(function ($item) use ($grandTotal) {
            return [
                'cliente_nombre' => $item->cliente_nombre,
                'total' => round($item->total, 2),
                'porcentaje' => $grandTotal > 0 ? round(($item->total / $grandTotal) * 100, 2) : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'clientes' => $result,
                'total_general' => round($grandTotal, 2),
            ],
        ]);
    }

    /**
     * Get percentage of sales from top 20% clients (Pareto analysis)
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function salesTop20Clients(Request $request)
    {
        $query = Sale::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $salesByClient = $query
            ->selectRaw('cliente_nombre, SUM(monto) as total')
            ->groupBy('cliente_nombre')
            ->orderByDesc('total')
            ->get();

        $totalClients = $salesByClient->count();
        $grandTotal = $salesByClient->sum('total');

        if ($totalClients === 0 || $grandTotal === 0) {
            return response()->json([
                'success' => true,
                'data' => [
                    'porcentaje_ventas_top_20' => 0,
                    'total_clientes' => 0,
                    'clientes_top_20' => 0,
                    'ventas_top_20' => 0,
                    'ventas_totales' => 0,
                ],
            ]);
        }

        // Calculate top 20% of clients
        $top20Count = max(1, (int) ceil($totalClients * 0.20));
        $top20Clients = $salesByClient->take($top20Count);
        $top20Total = $top20Clients->sum('total');

        $percentageTop20 = round(($top20Total / $grandTotal) * 100, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'porcentaje_ventas_top_20' => $percentageTop20,
                'total_clientes' => $totalClients,
                'clientes_top_20' => $top20Count,
                'ventas_top_20' => round($top20Total, 2),
                'ventas_totales' => round($grandTotal, 2),
                'clientes' => $top20Clients->map(fn($c) => [
                    'cliente_nombre' => $c->cliente_nombre,
                    'total' => round($c->total, 2),
                ]),
            ],
        ]);
    }

    /**
     * Get percentage of approved budgets
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function budgetsApprovedPercentage(Request $request)
    {
        $query = Budget::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $totalBudgets = (clone $query)->count();
        $approvedBudgets = (clone $query)->where('estado', 'Aprobado')->count();

        $percentage = $totalBudgets > 0 ? round(($approvedBudgets / $totalBudgets) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'porcentaje_aprobados' => $percentage,
                'total_presupuestos' => $totalBudgets,
                'presupuestos_aprobados' => $approvedBudgets,
            ],
        ]);
    }

    /**
     * Get deadline deviations for budgets
     * Compares estimated (fecha_estimada_culminacion - fecha_oc) vs actual (fecha_culminacion_real - fecha_oc)
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function budgetsDeadlineDeviations(Request $request)
    {
        $query = Budget::query()
            ->whereNotNull('fecha_oc')
            ->whereNotNull('fecha_estimada_culminacion')
            ->whereNotNull('fecha_culminacion_real');

        $this->applyDateFilter($query, 'fecha', $request);

        $budgets = $query->get();

        $deviations = $budgets->map(function ($budget) {
            $fechaOc = \Carbon\Carbon::parse($budget->fecha_oc);
            $fechaEstimada = \Carbon\Carbon::parse($budget->fecha_estimada_culminacion);
            $fechaReal = \Carbon\Carbon::parse($budget->fecha_culminacion_real);

            $plazoEstimado = $fechaOc->diffInDays($fechaEstimada);
            $plazoReal = $fechaOc->diffInDays($fechaReal);

            $desvioDias = $plazoReal - $plazoEstimado;
            $desvioPorcentaje = $plazoEstimado > 0
                ? round((($plazoReal - $plazoEstimado) / $plazoEstimado) * 100, 2)
                : ($plazoReal > 0 ? 100 : 0);

            return [
                'id' => $budget->id,
                'cliente_nombre' => $budget->cliente_nombre,
                'nombre_proyecto' => $budget->nombre_proyecto,
                'comprobante' => $budget->comprobante,
                'fecha_oc' => $budget->fecha_oc?->format('Y-m-d'),
                'fecha_estimada_culminacion' => $budget->fecha_estimada_culminacion?->format('Y-m-d'),
                'fecha_culminacion_real' => $budget->fecha_culminacion_real?->format('Y-m-d'),
                'plazo_estimado_dias' => $plazoEstimado,
                'plazo_real_dias' => $plazoReal,
                'desvio_dias' => $desvioDias,
                'desvio_porcentaje' => $desvioPorcentaje,
            ];
        });

        // Calculate averages
        $avgDeviationDays = $deviations->count() > 0 ? round($deviations->avg('desvio_dias'), 2) : 0;
        $avgDeviationPercentage = $deviations->count() > 0 ? round($deviations->avg('desvio_porcentaje'), 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'desvios' => $deviations,
                'promedio_desvio_dias' => $avgDeviationDays,
                'promedio_desvio_porcentaje' => $avgDeviationPercentage,
                'total_presupuestos_analizados' => $deviations->count(),
            ],
        ]);
    }

    /**
     * Get total weighted hours from budgets
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function budgetsTotalWeightedHours(Request $request)
    {
        $query = Budget::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $totalHours = $query->sum('horas_ponderadas');

        return response()->json([
            'success' => true,
            'data' => [
                'total_horas_ponderadas' => round($totalHours, 2),
            ],
        ]);
    }

    /**
     * Get percentage of weighted hours for projects with numeric value < 1001
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam ano int Filter by year
     * @queryParam mes int Filter by month
     */
    public function hoursProjectsUnder1001(Request $request)
    {
        $query = HourDetail::query();

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        $totalHours = (clone $query)->sum('horas_ponderadas');

        // Filter projects with numeric value < 1001 (compatible with MySQL and SQLite)
        $hoursUnder1001 = (clone $query)
            ->get()
            ->filter(function ($item) {
                // Check if proyecto is numeric and less than 1001
                return is_numeric($item->proyecto) && (int) $item->proyecto < 1001;
            })
            ->sum('horas_ponderadas');

        $percentage = $totalHours > 0 ? round(($hoursUnder1001 / $totalHours) * 100, 2) : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'porcentaje_proyectos_menor_1001' => $percentage,
                'horas_proyectos_menor_1001' => round($hoursUnder1001, 2),
                'horas_totales' => round($totalHours, 2),
            ],
        ]);
    }

    /**
     * Get total weighted hours for project 606
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     * @queryParam ano int Filter by year
     * @queryParam mes int Filter by month
     */
    public function hoursProject606(Request $request)
    {
        $query = HourDetail::query()->where('proyecto', '606');

        $this->applyDateFilter($query, 'fecha', $request);

        if ($request->filled('ano')) {
            $query->where('ano', $request->ano);
        }

        if ($request->filled('mes')) {
            $query->where('mes', $request->mes);
        }

        $totalHours = $query->sum('horas_ponderadas');

        return response()->json([
            'success' => true,
            'data' => [
                'horas_ponderadas_proyecto_606' => round($totalHours, 2),
            ],
        ]);
    }

    /**
     * Get budget count grouped by status
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function budgetsByStatus(Request $request)
    {
        $query = Budget::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $budgetsByStatus = $query
            ->selectRaw('COALESCE(estado, "Sin estado") as estado, COUNT(*) as cantidad')
            ->groupBy('estado')
            ->orderByDesc('cantidad')
            ->get();

        $total = $budgetsByStatus->sum('cantidad');

        $result = $budgetsByStatus->map(function ($item) use ($total) {
            return [
                'estado' => $item->estado,
                'cantidad' => $item->cantidad,
                'porcentaje' => $total > 0 ? round(($item->cantidad / $total) * 100, 2) : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'estados' => $result,
                'total' => $total,
            ],
        ]);
    }

    /**
     * Get processed staff satisfaction metrics.
     * Returns an average satisfaction score (0-100) for each of the 4 questions
     * and a general average across all questions.
     *
     * Scale: mal=0, normal=50, bien=100
     *
     * @queryParam fecha_inicio date Filter from date (Y-m-d)
     * @queryParam fecha_fin date Filter to date (Y-m-d)
     */
    public function staffSatisfactionMetrics(Request $request)
    {
        $query = StaffSatisfactionResponse::query();

        $this->applyDateFilter($query, 'fecha', $request);

        $responses = $query->select([
            'p1_mal', 'p1_normal', 'p1_bien',
            'p2_mal', 'p2_normal', 'p2_bien',
            'p3_mal', 'p3_normal', 'p3_bien',
            'p4_mal', 'p4_normal', 'p4_bien',
        ])->get();

        $total = $responses->count();

        if ($total === 0) {
            return response()->json([
                'success' => true,
                'data' => [
                    'total_respuestas' => 0,
                    'pregunta_1' => 0,
                    'pregunta_2' => 0,
                    'pregunta_3' => 0,
                    'pregunta_4' => 0,
                    'promedio_general' => 0,
                ],
            ]);
        }

        $scores = [0, 0, 0, 0];

        foreach ($responses as $r) {
            for ($i = 1; $i <= 4; $i++) {
                if ($r->{"p{$i}_bien"}) {
                    $scores[$i - 1] += 100;
                } elseif ($r->{"p{$i}_normal"}) {
                    $scores[$i - 1] += 50;
                }
                // mal = 0, no se suma nada
            }
        }

        $avgScores = array_map(fn($s) => round($s / $total, 2), $scores);
        $promedioGeneral = round(array_sum($avgScores) / 4, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'total_respuestas' => $total,
                'pregunta_1' => $avgScores[0],
                'pregunta_2' => $avgScores[1],
                'pregunta_3' => $avgScores[2],
                'pregunta_4' => $avgScores[3],
                'promedio_general' => $promedioGeneral,
            ],
        ]);
    }
}
