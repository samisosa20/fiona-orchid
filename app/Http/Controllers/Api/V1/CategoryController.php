<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Category;
use App\Models\Movement;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $categories = Category::withTrashed()
            ->where([
                ['user_id', $request->user()->id],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')]
            ])
            ->with(['group', 'categoryFather'])
            ->addSelect([
                'sub_categories' => DB::table('categories as b')
                    ->selectRaw('count(*)')
                    ->whereNull('b.deleted_at')
                    ->whereColumn('categories.id', 'b.category_id')
            ])
            ->when($request->query('category_father'), function ($query) use ($request) {
                $query->where('category_id', $request->query('category_father'));
            })
            ->when(!$request->query('category_father'), function ($query) use ($request) {
                $query->whereNull('category_id');
            })
            ->get();

        return response()->json($categories);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'group_id' => [
                    'required'
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = auth()->user();

            $category = Category::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Categoria creada exitosamente',
                'data' => $category,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        $user = auth()->user();
        $currency = $request->query('badge_id') ?? auth()->user()->badge_id;

        $data = Category::withTrashed()
            ->where([
                ['user_id', $user->id],
                ['id', $id]
            ])
            ->with(['group', 'categoryFather'])
            ->first();

        $data->categories = array();
        if ($data->id) {
            $data->categories = Category::withTrashed()
                ->where([
                    ['user_id', auth()->user()->id],
                    ['category_id', $data->id]
                ])
                ->with(['group', 'categoryFather'])
                ->addSelect([
                    'sub_categories' => DB::table('categories as b')
                        ->selectRaw('count(*)')
                        ->whereNull('b.deleted_at')
                        ->whereColumn('categories.id', 'b.category_id')
                ])
                ->get();

            $startDate = Carbon::now()->subYear();

            $subcategories = Category::where([
                ['user_id', auth()->user()->id],
                ['category_id', $data->id]
            ])->pluck('id')->toArray();
            $subcategories[] = $data->id;

            $averageMonthlyAmounts = Movement::where([
                ['date_purchase', '>=', $startDate],
                ['currencies.id', $currency],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')],
            ])
                ->whereIn('movements.category_id', $subcategories)
                ->select(
                    DB::raw('YEAR(date_purchase) as year'),
                    DB::raw('MONTH(date_purchase) as month'),
                    DB::raw('ROUND(SUM(amount), 2) as sum_amount')
                )
                ->join('categories', 'movements.category_id', 'categories.id')
                ->join('accounts', 'account_id', 'accounts.id')
                ->join('currencies', 'badge_id', 'currencies.id')
                ->groupBy('year', 'month')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            // Calcular el promedio de sum_amount
            $totalAmount = $averageMonthlyAmounts->sum('sum_amount');
            $count = $averageMonthlyAmounts->count();

            $data->avg = round($count > 0 ? abs($totalAmount) / $count : 0, 2);


            // Calcular la desviación estándar
            $variance = 0;
            foreach ($averageMonthlyAmounts as $value) {
                $variance += pow(abs($value['sum_amount']) - abs($data->avg), 2);
            }
            if(count($averageMonthlyAmounts) > 0) {
                $variance /= count($averageMonthlyAmounts);
            }
            $data->stdDeviation = round(sqrt($variance), 2);

            // Definir el factor de multiplicación para el intervalo (por ejemplo, ±1 desviación estándar)
            $intervalFactor = 1;

            // Calcular el rango (promedio ± intervalFactor * desviación estándar)
            $data->lowerBound = round($data->avg - $intervalFactor * $data->stdDeviation, 2);
            $data->upperBound = round($data->avg + $intervalFactor * $data->stdDeviation, 2);

            // Asegurar que siempre haya 12 elementos completando los que faltan
            $currentDate = Carbon::now();
            for ($i = 0; $i < 12; $i++) {
                $year = $currentDate->copy()->subMonths($i)->year;
                $month = $currentDate->copy()->subMonths($i)->month;

                // Verificar si el elemento actual existe en los resultados
                $exists = $averageMonthlyAmounts->contains(function ($item) use ($year, $month) {
                    return $item['year'] == $year && $item['month'] == $month;
                });

                // Si no existe, agregar un elemento con promedio 0
                if (!$exists) {
                    $averageMonthlyAmounts->push([
                        'year' => $year,
                        'month' => $month,
                        'sum_amount' => 0 // Promedio 0 para los meses faltantes
                    ]);
                }
            }

            $data->history = $averageMonthlyAmounts->sortBy(function ($item) {
                return $item['year'] * 100 + $item['month'];
            })->values();
        }

        if ($data) {
            return response()->json($data);
        }
        return response([
            'message' =>  'Datos no encontrados',
            'detail' => 'La información no existe'
        ], 400);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'group_id' => [
                    'required'
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $category->fill($request->input())->save();

            return response()->json([
                'message' => 'Categoria editada exitosamente',
                'data' => $category,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        try {
            $category->delete();
            return response()->json([
                'message' => 'Categoria eliminada exitosamente',
                'data' => $category,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Restore the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        try {
            $category = Category::withTrashed()->find($id)->restore();
            return response()->json([
                'message' => 'Categoria Activada exitosamente',
                'data' => $category,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    /**
     * Restore the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function listCategories()
    {
        try {
            $user = auth()->user();
            $categories = Category::where([
                ['categories.user_id', $user->id],
                ['categories.group_id', '<>', env('GROUP_TRANSFER_ID')]
            ])
                ->selectRaw('categories.id, if(categories.category_id is null, categories.name, concat(categories.name, " (", b.name, ")")) as title, categories.category_id as category_father')
                ->leftJoin('categories as b', 'b.id', 'categories.category_id')
                ->orderBy('categories.name')
                ->get();
            return response()->json($categories);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Error al conseguir los datos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}
