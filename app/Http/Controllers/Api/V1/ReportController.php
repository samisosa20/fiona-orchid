<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use App\Controllers\Reports\ReportController as Report;
use App\Controllers\Reports\HelpersController;

use App\Models\Currency;
use App\Models\Movement;
use App\Models\Category;

class ReportController extends Controller
{
    public function report(Request $request)
    {
        try {
            $data = Report::report($request);
            $init_date = $request->query('start_date') ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->query('end_date') ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->query('badge_id') ?? auth()->user()->badge_id;

            return [
                'incomes' => $data['incomes'],
                'expensives' =>  $data['main_expensive'],
                'balances' => $data['balances'],
                'group_expensive' => $data['group_expensive'],
                'list_expensives' => $data['list_expensives'],
                'list_incomes' => $data['incomes'],
                'credit_carts' => $data['credit_carts'],
                'metrics' => [
                    'open_balance'    => number_format($data['open_close']->open_balance, 2, ',', '.'),
                    'income' => number_format($data['open_close']->income, 2, ',', '.'),
                    'expensive'   => number_format($data['open_close']->expensive, 2, ',', '.'),
                    'utility'    => number_format($data['open_close']->utility, 2, ',', '.'),
                ],
                'init_date' => $init_date,
                'end_date' => $end_date,
                'currency' => Currency::find($currency),
                'movements' => [],
            ];
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex //->errorInfo[0]
            ], 400);
        }
    }

    public function movementsByCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }
            $init_date = $request->all()['start_date'] ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->all()['end_date'] ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->all()['badge_id'] ?? auth()->user()->badge_id;

            $movements = Movement::where([
                ['movements.user_id', auth()->user()->id],
                ['category_id', $request->all()['category_id']],
            ])
                ->with(['account', 'event'])
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->whereHas('account', function ($query) use ($currency) {
                    $query->where([
                        ['badge_id', $currency]
                    ]);
                })
                ->get();

            return $movements;
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function movementsByGroup(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'group_id' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $init_date = $request->all()['start_date'] ?? Carbon::now()->firstOfMonth()->format("Y-m-d");
            $end_date = $request->all()['end_date'] ?? Carbon::now()->lastOfMonth()->format("Y-m-d");
            $currency = $request->all()['badge_id'] ?? auth()->user()->badge_id;

            $movements = Movement::where([
                ['movements.user_id', auth()->user()->id],
            ])
                ->with(['account', 'event'])
                ->with('category', function ($query) {
                    $query->with('categoryFather');
                })
                ->whereDate('date_purchase', '>=', $init_date)
                ->whereDate('date_purchase', '<=', $end_date)
                ->whereHas('category', function ($query) use ($request) {
                    $query->where([
                        ['group_id', $request->all()['group_id']]
                    ]);
                })
                ->whereHas('account', function ($query) use ($currency) {
                    $query->where([
                        ['badge_id', $currency]
                    ]);
                })
                ->selectRaw('category_id, sum(amount) as amount')
                ->groupBy('category_id')
                ->orderByRaw('sum(amount) ASC')
                ->get();

            return $movements;
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }

    public function reportCategory(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $init_date = $request->start_date ?? Carbon::now()->startOfYear();
            $end_date = $request->end_date ?? Carbon::now()->lastOfMonth()->format("Y-m-d");

            $category = Category::where([
                ['id', $request->category_id]
            ])
                ->with(['subCategories'])
                ->first();

            $total = array();

            foreach ($category->subCategories as &$subCategory) {
                $subCategory->amount = Movement::where([
                    ['movements.user_id', auth()->user()->id],
                    ['category_id', $subCategory->id],
                ])
                    ->selectRaw('currencies.code, sum(amount) as amount')
                    ->whereDate('date_purchase', '>=', $init_date)
                    ->whereDate('date_purchase', '<=', $end_date)
                    ->join('accounts', 'accounts.id', '=', 'account_id')
                    ->join('currencies', 'currencies.id', '=', 'badge_id')
                    ->groupBy('currencies.code')
                    ->get();
                array_push($total, $subCategory->amount);
            }

            $category->total_amount = $category->subCategories->reduce(function ($carry, $item) {
                foreach ($item['amount'] as $amount) {
                    if (!isset($carry[$amount['code']])) {
                        $carry[$amount['code']] = 0;
                    }
                    $carry[$amount['code']] += $amount['amount'];
                }
                return $carry;
            }, []);

            return response()->json($category);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex //->errorInfo[0]
            ], 400);
        }
    }

    public function testViabilityProject(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'rate' => [
                    'required',
                ],
                'periods' => [
                    'required',
                ],
                'investment' => [
                    'required',
                ],
                'cash_flow' => [
                    'required',
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $tasaTIR = HelpersController::calcTir($request->investment, $request->cash_flow, $request->periods, $request->end_investement);
            $npv = HelpersController::calcNpv($request->investment, $request->cash_flow, $request->periods, $request->end_investement, $request->rate);
            $costBene = HelpersController::calcCostBene($request->investment, $request->cash_flow, $request->periods, $request->end_investement, $request->rate);

            return response()->json([
                'tir' => $tasaTIR . "%",
                'approve_tir' => $tasaTIR >= $request->rate,
                'npv' => $npv,
                'approve_npv' => $npv > 0,
                'benefist_cost' => $costBene,
                'approve_benefist_cost' => $costBene > 1,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' => 'Datos no obtenidos',
                'detail' => $ex //->errorInfo[0]
            ], 400);
        }
    }
}
