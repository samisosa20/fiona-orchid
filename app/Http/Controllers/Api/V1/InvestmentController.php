<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Models\Investment;
use App\Models\Movement;
use App\Models\InvestmentAppreciation;

class InvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        $investments = Investment::where([
            ['user_id', $user->id]
        ])
            ->with('currency')
            ->get();

        $balances = Investment::where([
            ['user_id', $user->id]
        ])
            ->selectRaw('currencies.code as currency, badge_id, sum(end_amount - init_amount) as valuation, sum(end_amount) as amount')
            ->join('currencies', 'currencies.id', 'investments.badge_id')
            ->groupByRaw('currencies.code, badge_id')
            ->get();

        foreach ($balances as &$value) {
            $value->profit = Movement::where([
                ['movements.user_id', $user->id],
                ['currencies.id', $value->badge_id],
            ])
                ->whereNotNull('investment_id')
                ->join('accounts', 'accounts.id', 'movements.account_id')
                ->join('currencies', 'currencies.id', 'accounts.badge_id')
                ->sum('amount') * 1;
        }

        foreach ($investments as &$investment) {
            $investment->returns = Movement::where([
                ['movements.investment_id', $investment->id],
                ['add_withdrawal', false]
            ])
                ->sum('amount') * 1;

            $investment->add_withdrawal = Movement::where([
                ['movements.investment_id', $investment->id],
                ['add_withdrawal', true]
            ])
                ->selectRaw('sum(amount * -1) as amount')
                ->first()
                ->amount * 1;

            $appretiation = InvestmentAppreciation::where([
                ['investment_id', $investment->id]
            ])
                ->orderBy('date_appreciation', 'desc')
                ->first();

            $end_amount = $appretiation->amount ?? $investment->init_amount;
            $investment->end_amount = $end_amount;

            $total_add_withdrawal = $investment->init_amount + $investment->add_withdrawal;

            $investment->valorization = $total_add_withdrawal == 0 ?  "0%" : round(($end_amount - $total_add_withdrawal) / ($total_add_withdrawal) * 100, 2) . "%";
            $investment->total_rate = $total_add_withdrawal == 0 ?  "0%" : round(($end_amount + $investment->returns - $total_add_withdrawal) / ($total_add_withdrawal) * 100, 2) . "%";
        }

        return response()->json([
            'investments' => $investments,
            'balances' => $balances
        ]);
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
                'init_amount' => [
                    'required',
                    'numeric',
                ],
                'badge_id' => [
                    'required',
                ],
                'date_investment' => [
                    'required',
                    'date_format:Y-m-d'
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $user = auth()->user();

            $payment = Investment::create(array_merge($request->input(), ['user_id' => $user->id]));

            return response()->json([
                'message' => 'Inversion creada exitosamente',
                'data' => $payment,
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
    public function show($id)
    {
        $user = auth()->user();
        $data = Investment::where([
            ['user_id', $user->id],
            ['id', $id]
        ])
            ->with(['currency', 'movements'])
            ->first();

        $data->returns = Movement::where([
            ['movements.investment_id', $data->id],
            ['add_withdrawal', false]
        ])
            ->sum('amount') * 1;

        $data->add_withdrawal = Movement::where([
            ['movements.investment_id', $data->id],
            ['add_withdrawal', true]
        ])
            ->selectRaw('sum(amount * -1) as amount')
            ->first()
            ->amount * 1;

        $data->appreciations = InvestmentAppreciation::where([
            ['investment_id', $data->id]
        ])
            ->addSelect([
                'investment' => Movement::selectRaw('SUM(amount * -1)')
                    ->where('movements.investment_id', $data->id)
                    ->where('movements.add_withdrawal', 1)
                    ->whereColumn('movements.date_purchase', '<=', 'investment_appreciations.date_appreciation')
            ])
            ->get();

        $appretiation = InvestmentAppreciation::where([
            ['investment_id', $data->id]
        ])
            ->orderBy('date_appreciation', 'desc')
            ->first();

        $end_amount = $appretiation->amount ?? $data->init_amount;
        $data->end_amount = $end_amount;

        $total_add_withdrawal = $data->init_amount + $data->add_withdrawal;

        $data->valorization = $total_add_withdrawal == 0 ?  "0%" : round(($end_amount - $total_add_withdrawal) / ($total_add_withdrawal) * 100, 2) . "%";
        $data->total_rate = $total_add_withdrawal == 0 ?  "0%" : round(($end_amount + $data->returns - $total_add_withdrawal) / ($total_add_withdrawal) * 100, 2) . "%";

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
     * @param  \App\Models\Investment  $payment
     * @return \Illuminate\Http\Response
     */
    public function edit(Investment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Investment  $investment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Investment $investment)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                ],
                'init_amount' => [
                    'required',
                ],
                'end_amount' => [
                    'required',
                ],
                'badge_id' => [
                    'required',
                ],
                'date_investment' => [
                    'required',
                    'date_format:Y-m-d'
                ],
            ]);

            if ($validator->fails()) {
                return response([
                    'message' => 'data missing',
                    'detail' => $validator->errors()
                ], 400)->header('Content-Type', 'json');
            }

            $investment->fill($request->input())->save();

            return response()->json([
                'message' => 'Inversion editada exitosamente',
                'data' => $investment,
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
     * @param  \App\Models\Investment  $investment
     * @return \Illuminate\Http\Response
     */
    public function destroy(Investment $investment)
    {
        try {
            $investment->delete();
            return response()->json([
                'message' => 'Inversion eliminada exitosamente',
                'data' => $investment,
            ]);
        } catch (\Illuminate\Database\QueryException $ex) {
            return response([
                'message' =>  'Datos no guardados',
                'detail' => $ex->errorInfo[0]
            ], 400);
        }
    }
}
