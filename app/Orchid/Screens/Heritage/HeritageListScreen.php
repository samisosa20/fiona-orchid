<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Heritage;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Orchid\Layouts\Heritage\HeritageListLayout;

use App\Models\Heritage;
use App\Models\Movement;
use App\Models\Account;

class HeritageListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $heritages = Heritage::where([
            ['user_id', $request->user()->id]
        ])
        ->distinct('year')
        ->select('year')
        ->orderBy('year')
        ->get();

        foreach ($heritages as &$value) {
            $value->balance = Movement::where([
                ['movements.user_id', $request->user()->id],
            ])
            ->whereYear('date_purchase', '=', $value->year)
            ->selectRaw('year(date_purchase) as year, currencies.code as currency, badge_id, cast(ifnull(sum(amount), 0) as float) as movements')
            ->join('accounts', 'accounts.id', 'movements.account_id')
            ->join('currencies', 'currencies.id', 'accounts.badge_id')
            ->groupByRaw('year(date_purchase), currencies.code, badge_id')
            ->get();

            foreach ($value->balance  as &$balance) {
                $init_amout = Account::where([
                    ['user_id', $request->user()->id],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('sum(init_amount) as amount')
                ->whereNull('deleted_at')
                ->first();
                
                $comercial_amount = Heritage::where([
                    ['user_id', $request->user()->id],
                    ['year', $value->year],
                    ['badge_id', $balance->badge_id],
                ])
                ->selectRaw('cast(ifnull(sum(comercial_amount), 0) as float) as comercial_amount')
                ->first();

                $balance->amount = $comercial_amount->comercial_amount + $balance->movements + $init_amout->amount;
            }
        }

        return [
            'heritages' => $heritages,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Heritages';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Register all your heritages';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Link::make(__('Add'))
                ->icon('plus')
                ->route('platform.heritages.create'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return string[]|\Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            HeritageListLayout::class,
        ];
    }



    /**
     * @param Request $request
     */
    public function activate(Request $request): void
    {
        Heritage::onlyTrashed()->find($request->get('id'))->restore();

        Toast::success(__('The heritage was activated.'));
    }
}
