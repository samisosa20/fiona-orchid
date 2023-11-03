<?php

namespace App\Controllers\Reports;

use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use DB;
use Carbon\Carbon;


use App\Models\Movement;
use App\Models\Account;

class HelpersController extends Controller
{
    static function calcNpv($initInvestment, $appretiation, $periods, $rate, $endInvestment = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $cashflow = [-1 * (float)$initInvestment];
        $cashflow = array_merge($cashflow, array_fill(0, (int)$periods - 1, (float)$appretiation));
        array_push($cashflow, $endInvestment ? (float)$endInvestment + $appretiation : (float)$initInvestment + $appretiation);
        $columnArray = array_chunk($cashflow, 1);

        $sheet->fromArray($columnArray, null, 'A1');

        // Calculate NPV
        $sheet->setCellValue('C1', '=NPV(' . $rate / 100 . ',A1:A' . count($cashflow) . ')');
        $npv = $sheet->getCell('C1')->getCalculatedValue();
        $npv = round((float)$npv, 2);

        return $npv;
    }

    static function calcTir($initInvestment, $incomes, $expensive, $periods, $endInvestment = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $expensiveFlow = [(float)$initInvestment];
        $expensiveFlow = array_merge($expensiveFlow, array_fill(0, (int)$periods, (float)$expensive));

        $incomeFlow = [0];
        $incomeFlow = array_merge($incomeFlow, array_fill(0, (int)$periods - 1, (float)$incomes));
        array_push($incomeFlow, $endInvestment ? (float)$endInvestment + $incomes : (float)$initInvestment + $incomes);

        $cashflow = array();

        foreach ($incomeFlow as $key => $income) {
            $cashflow[] = $income - $expensiveFlow[$key];
        }

        $columnArray = array_chunk($cashflow, 1);

        $sheet->fromArray($columnArray, null, 'A1');

        // Calculate IRR
        $sheet->setCellValue('C1', '=IRR(A1:A' . count($cashflow) . ')');
        $tir = $sheet->getCell('C1')->getCalculatedValue();
        $tir = round((float)$tir * 100, 2);

        return $tir;
    }

    static function calcCostBene($initInvestment, $incomes, $expensive,  $periods, $rate, $endInvestment = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $expensiveFlow = [-1 * (float)$initInvestment];
        $expensiveFlow = array_merge($expensiveFlow, array_fill(0, (int)$periods, -1 * (float)$expensive));
        $columnArray = array_chunk($expensiveFlow, 1);

        $sheet->fromArray($columnArray, null, 'B1');

        $incomeFlow = [0];
        $incomeFlow = array_merge($incomeFlow, array_fill(0, (int)$periods - 1, (float)$incomes));
        array_push($incomeFlow, $endInvestment ? (float)$endInvestment + $incomes : (float)$initInvestment + $incomes);
        $columnArray = array_chunk($incomeFlow, 1);


        $sheet->fromArray($columnArray, null, 'A1');

        // Calculate NPV
        $sheet->setCellValue('C1', '=NPV(' . $rate / 100 . ',A1:A' . count($incomeFlow) . ')');
        $sheet->setCellValue('C2', '=NPV(' . $rate / 100 . ',B1:B' . count($expensiveFlow) . ')');

        $npi = $sheet->getCell('C1')->getCalculatedValue();
        $npe = $sheet->getCell('C2')->getCalculatedValue();

        $npv = round($npi / abs($npe), 2);

        return $npv;
    }

    static function calcROI($initInvestment, $incomes, $expensive,  $periods, $endInvestment = null)
    {
        $expensiveFlow = [(float)$initInvestment];
        $expensiveFlow = array_merge($expensiveFlow, array_fill(0, (int)$periods, (float)$expensive));

        $incomeFlow = [0];
        $incomeFlow = array_merge($incomeFlow, array_fill(0, (int)$periods - 1, (float)$incomes));
        array_push($incomeFlow, $endInvestment ? (float)$endInvestment + $incomes : (float)$initInvestment + $incomes);

        $cashflow = array();

        foreach ($incomeFlow as $key => $income) {
            $cashflow[] = $income - $expensiveFlow[$key];
        }

        $total_expensives = array_reduce($expensiveFlow, function ($carry, $item) {
            return $carry + $item;
        }, 0);

        $total_utility = array_reduce($cashflow, function ($carry, $item) {
            return $carry + $item;
        }, 0);

        $roi = round($total_utility / $total_expensives * 100, 2);

        return $roi;
    }

    static function translateResult($result)
    {
        $falseApproveCount = 0;
        $optionsFails = [
            "Invertir en esto sería como intentar nadar en un lago de chocolate caliente: suena dulce, pero terminarías pegajoso y sin ganancias. 🍫🏊‍♂️💦",
            "Esta inversión es como buscar un unicornio en el jardín trasero: mágico en teoría, pero poco probable de encontrar. 🦄🌳✨",
            "Invertir aquí es como intentar enseñar a tu abuela a enviar un GIF animado por correo electrónico: puede que sea divertido, pero no llevará a ninguna parte. 👵📧🤷‍♂️",
            "Esta inversión es como tratar de hacer una carrera de caracoles: seguro que es lento y puede que nunca llegues a la meta. 🐌🏁🐢",
            "Invertir en esto sería como intentar construir un castillo de arena en medio de un huracán: puede que sea emocionante, pero no durará mucho tiempo. 🏖️🌀🏰",
            "Esta inversión es como pretender ser un malabarista con sandías en una bicicleta unipersonal: entretenido, pero probablemente no termine bien. 🍉🚴‍♂️💥"
        ];
        $optionsIdontKnow = [
            "Esta inversión es como mirar a través del agujero de la cerradura de una puerta desconocida: tienes curiosidad, pero no estás seguro de lo que encontrarás al otro lado. 🔍🚪🤔",
            "Invertir aquí es como pescar en un lago sin saber si hay peces: puede que tengas suerte y atrapes algo grande, o puede que solo tengas historias para contar. 🎣🐟🤷‍♂️",
            "Esta inversión es como comprar un boleto de lotería: emocionante, pero con probabilidades inciertas. Puede que seas un ganador, o puede que necesites un plan de respaldo. 🎫🤞💼",
            "Invertir en esto es como entrar en una selva sin mapa: aventurero pero lleno de desafíos desconocidos. 🌴🗺️🌿",
            "Esta inversión es como hacer malabares con espadas de fuego: impresionante si funciona, pero puede haber riesgos en juego. 🤹‍♂️🔥🤯",
            "Esta inversión es como navegar en aguas desconocidas: emocionante, pero no siempre sabes si encontrarás tierras inexploradas o un naufragio. ⛵🌊🌴",
        ];
        $optionsApprove = [
            "Invertir aquí es como encontrar un tesoro en tu propio jardín: una oportunidad que no puedes dejar pasar. 💰🏡🌟",
            "Esta inversión tiene el potencial de ser una mina de oro: las señales son prometedoras y los riesgos son bajos. ⛏️💰🤩",
            "Invertir en esto es como plantar semillas en primavera: con el tiempo, verás crecer tus ganancias. 🌱🌷💸",
            "Esta es una oportunidad que parece tener todas las luces verdes: el camino hacia el éxito está despejado. 🚦💰😄",
            "Invertir aquí es como subirse a un tren en plena marcha: rápido, emocionante y con un destino lucrativo. 🚄💰🎉",
            "Esta inversión es como jugar al ajedrez: estratégica, con movimientos bien pensados que pueden llevarte a la victoria. ♟️💰👑",
        ];


        foreach ($result as $key => $value) {
            if (strpos($key, 'approve_') === 0 && $value === false) {
                $falseApproveCount++;
            }
        }

        if ($falseApproveCount > 2) {
            return [
                'fun' => $optionsFails[rand(0, count($optionsFails) - 1)],
                'real' => 'No te recomendaría que hagas esta inversión.'
            ];
        } else if ($falseApproveCount == 2) {
            return [
                'fun' => $optionsIdontKnow[rand(0, count($optionsIdontKnow) - 1)],
                'real' => 'No parece haber una seguridad sólida en esta inversión. Te sugiero que examines cuidadosamente los indicadores y detalles de la inversión antes de tomar una decisión.'
            ];
        }

        return [
            'fun' => $optionsApprove[rand(0, count($optionsApprove) - 1)],
            'real' => 'Puedes considerar esta inversión, pero te recomendaría que evalúes detenidamente los riesgos, la fiabilidad y otros aspectos relacionados con ella'
        ];
    }

    static function canExpensive($amount, $currency)
    {
        $init_amout = (float)Account::withTrashed()
            ->where([
                ['user_id', auth()->user()->id],
                ['badge_id', $currency],
            ])
            ->sum('init_amount');

        if ($init_amout) {
            $saldoActual = $init_amout;
        } else {
            $saldoActual = 0;
        }

        // Calcular el saldo actual
        $balances = (float)Movement::where([
            ['user_id', auth()->user()->id],
        ])
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->sum('amount');

        if ($balances) {
            $saldoActual += $balances;
        }

        $year = Carbon::now()->year;
        $month = Carbon::now()->month;

        // get avg expensives and incomes
        $avgExpensiveMonthly = Movement::selectRaw('
            YEAR(date_purchase) as year,
            MONTH(date_purchase) as month,
            sum(amount) as promedio_mensual
        ')
            ->where([
                ['movements.user_id', auth()->user()->id],
                ['amount', '<', 0],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
            ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->groupBy('year', 'month')
            ->pluck('promedio_mensual', 'month');

        $avgExpensiveTransMonthly = Movement::selectRaw('
            YEAR(date_purchase) as year,
            MONTH(date_purchase) as month,
            sum(amount) as promedio_mensual
        ')
            ->where([
                ['movements.user_id', auth()->user()->id],
                ['amount', '<', 0],
                ['group_id', '=', env('GROUP_TRANSFER_ID')],
                ['trm', '<>', 1],
            ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->groupBy('year', 'month')
            ->pluck('promedio_mensual', 'month');

        $avgIncomeMonthly = Movement::selectRaw('
            YEAR(date_purchase) as year,
            MONTH(date_purchase) as month,
            sum(amount) as promedio_mensual
        ')
            ->where([
                ['movements.user_id', auth()->user()->id],
                ['amount', '>', 0],
                ['group_id', '<>', env('GROUP_TRANSFER_ID')],
            ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->groupBy('year', 'month')
            ->pluck('promedio_mensual', 'month');

        $avgIncomeTransMonthly = Movement::selectRaw('
            YEAR(date_purchase) as year,
            MONTH(date_purchase) as month,
            sum(amount) as promedio_mensual
        ')
            ->where([
                ['movements.user_id', auth()->user()->id],
                ['amount', '>', 0],
                ['group_id', '=', env('GROUP_TRANSFER_ID')],
                ['trm', '<>', 1],
            ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->groupBy('year', 'month')
            ->pluck('promedio_mensual', 'month');

        $actualExpensive = (float)Movement::where([
            ['movements.user_id', auth()->user()->id],
            ['amount', '<', 0],
            ['group_id', '<>', env('GROUP_TRANSFER_ID')],
        ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->whereMonth('date_purchase', $month)
            ->sum('amount');

        $actualExpensive += (float)Movement::where([
            ['movements.user_id', auth()->user()->id],
            ['amount', '<', 0],
            ['group_id', '=', env('GROUP_TRANSFER_ID')],
            ['trm', '<>', 1],
        ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->whereMonth('date_purchase', $month)
            ->sum('amount');

        $actualIncome = (float)Movement::where([
            ['movements.user_id', auth()->user()->id],
            ['amount', '>', 0],
            ['group_id', '<>', env('GROUP_TRANSFER_ID')],
        ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->whereMonth('date_purchase', $month)
            ->sum('amount');

        $actualIncome += (float)Movement::where([
            ['movements.user_id', auth()->user()->id],
            ['amount', '>', 0],
            ['group_id', '=', env('GROUP_TRANSFER_ID')],
            ['trm', '<>', 1],
        ])
            ->join('categories', 'categories.id', 'movements.category_id')
            ->whereHas('account', function ($query) use ($currency) {
                $query->where('badge_id', '=', $currency);
            })
            ->whereYear('date_purchase', $year)
            ->whereMonth('date_purchase', $month)
            ->sum('amount');


        $avgExpensiveMonthly = array_reduce($avgExpensiveMonthly->toArray(), function ($carry, $item) {
            return $carry + $item;
        }, 0) / count($avgExpensiveMonthly);
        $avgExpensiveMonthly += array_reduce($avgExpensiveTransMonthly->toArray(), function ($carry, $item) {
            return $carry + $item;
        }, 0) / count($avgExpensiveTransMonthly);

        $avgIncomeMonthly = array_reduce($avgIncomeMonthly->toArray(), function ($carry, $item) {
            return $carry + $item;
        }, 0) / count($avgIncomeMonthly);
        $avgIncomeMonthly += array_reduce($avgIncomeTransMonthly->toArray(), function ($carry, $item) {
            return $carry + $item;
        }, 0) / count($avgIncomeTransMonthly);

        $addIncomes = $actualIncome >= $avgIncomeMonthly ? 0 : $avgIncomeMonthly - $actualIncome;
        $addExpensives = $avgExpensiveMonthly >= $actualExpensive ? 0 : $avgExpensiveMonthly - $actualExpensive;

        $futureBalance = $saldoActual + $addIncomes + $addExpensives;

        $message = "Tu saldo actual es de: " . number_format($saldoActual, 2, '.', ',') . " y tus ingresos promedios son de: " .
            number_format($avgIncomeMonthly, 2, '.', ',') . ", ademas tienes unos gastos promedios de: " . number_format($avgExpensiveMonthly, 2, '.', ',') .
            " en lo que va del mes te ha ingresado: " . number_format($actualIncome, 2, '.', ',') . " y haz gastado: " . number_format($actualExpensive, 2, '.', ',') .
            ", Lo que quiere decir que al finalizar el mes si todo se comporta normal quedarias con un saldo de: " . number_format($futureBalance, 2, '.', ',');


        if ($futureBalance >= $amount) {
            $message = $message . ", y podrias gastar " . number_format($amount, 2, '.', ',') . ", dejandote con un saldo de: " . number_format($futureBalance - $amount, 2, '.', ',');
        } else {
            $message = $message . ", y No puedes gastar " . number_format($amount, 2, '.', ',') . " ya que quedarias en saldo negativo y tocaria pedir un prestamo.";
        }

        return $message;
    }
}
