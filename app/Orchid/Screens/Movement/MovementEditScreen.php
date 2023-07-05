<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Movement;

use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Facades\Validator;

use App\Models\Movement;
use App\Models\Category;
use App\Models\Account;

use App\Orchid\Layouts\Movement\MovementTypeLayout;

class MovementEditScreen extends Screen
{
    /**
     * @var Movement
     */
    public $movement;

    public $route;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param Movement $movement
     *
     * @return array
     */
    public function query(Movement $movement, Request $request): iterable
    {
        $url = url()->previous();
        
        $request->session()->put('route', app('router')->getRoutes($url)->match(app('request')->create($url))->getName());
        $request->session()->put('account_id', app('router')->getRoutes($url)->match(app('request')->create($url))->parameters()['account'] ?? null);

        $accounts = Account::where([
            ['user_id', $request->user()->id],
        ])
        ->select('id', 'badge_id')
        ->get();

        //dd($movement->transferOut, $movement->transferIn);

        $accountIn = null;
        $accountOut = null;
        $amountEnd = null;

        if($movement){
            $movement->type = $movement->transferOut || $movement->transferIn ? 'transfer' : ($movement->amount > 0 ? 'income' : 'expensive');
            if($movement->transferOut || $movement->transferIn){
                $accountIn = $movement->transferIn ? $movement->transferIn->account_id : $movement->account_id;
                $accountOut = $movement->transferOut ? $movement->transferOut->account_id : $movement->account_id;
                $amountEnd = $movement->transferIn ? abs($movement->transferIn->amount) : abs($movement->amount);
                $movement->amount = $movement->transferOut ? abs($movement->transferOut->amount) : abs($movement->amount);
            }
            $movement->amount = $movement->amount ? abs($movement->amount) : null;
        }

        return [
            'movement' => $movement,
            'defaultAccount' => app('router')->getRoutes($url)->match(app('request')->create($url))->parameters()['account'] ?? null,
            'user' => $request->user(),
            'accounts' => $accounts,
            'accountIn' => $accountIn,
            'accountOut' => $accountOut,
            'amountEnd' => $amountEnd,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->movement->exists ? 'Edit Movement' : 'Create Movement';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Details such as name, email and password';
    }

    /**
     * The screen's action buttons.
     *
     * @return Action[]
     */
    public function commandBar(): iterable
    {
        return [

            Button::make(__('Remove'))
                ->icon('trash')
                ->confirm(__('Once the Movement is deleted, all of its resources and data will be permanently deleted. Before deleting your Movement, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee($this->movement->exists),

            Button::make(__('Save'))
                ->icon('check')
                ->method('save'),
        ];
    }

    /**
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(MovementTypeLayout::class)
            ->title(__('Select Type')),
            Layout::block(Layout::view('layouts.movement.form'))

        ];
    }

    /**
     * @param Movement    $movement
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(Movement $movement, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'movement.amount' => [
                'required',
                'not_in:0'
            ]
        ]);

        if($validator->fails()){
            Toast::error(__('Amount different 0.'));
            return;
        }

        if($request->input('movement')['type'] !== 'transfer') {
            $movement->fill($request->collect('movement')->toArray())
                ->fill(['user_id' => $request->user()->id])
                ->fill(['amount' => $request->input('movement')['type'] === 'income' ? abs((float)$request->input('movement')['amount'] ) : abs((float)$request->input('movement')['amount']) * -1])
                ->save();
        } else {
            $validator = Validator::make($request->all(), [
                'movement.account_end_id' => [
                    'required',
                ],
            ]);
    
            if($validator->fails()){
                Toast::error(__('Amount in is required.'));
                return;
            }
            if($request->input('movement')['account_id'] === $request->input('movement')['account_end_id']) {
                Toast::error(__('Amount in cant be iqual to Account out.'));
                return;
            }

            if($movement->id){
                // if out move
                if(!$movement->transfer_id) {
                    $accountIn = Account::find($request->input('movement.account_end_id'));
                    $accountOut = Account::find($request->input('movement.account_id'));

                    $outData = [
                        'account_id' => $request->input('movement.account_id'),
                        'description' => $request->input('movement.description'),
                        'amount' => abs((float)$request->input('movement.amount')) * -1,
                        'trm' => $accountIn->badge_id !== $accountOut->badge_id ? $request->input('movement.amount') / ($request->input('movement.amount_end') ?? $request->input('movement.amount')) : 1,
                        'date_purchase' => $request->input('movement.date_purchase'),
                    ];
                    $movement->fill($outData)->save();

                    $inData = [
                        'account_id' => $request->input('movement.account_end_id'),
                        'description' => $request->input('movement.description'),
                        'amount' => $accountIn->badge_id !== $accountOut->badge_id ? abs((float)$request->input('movement.amount_end')) : abs((float)$request->input('movement.amount')),
                        'trm' => $accountIn->badge_id !== $accountOut->badge_id ? ($request->input('movement.amount_end') ?? $request->input('movement.amount')) / $request->input('movement.amount') : 1,
                        'date_purchase' => $request->input('movement.date_purchase'),
                    ];
                    $outMovement = Movement::where([
                        ['transfer_id', $movement->id]
                    ])->first();

                    $outMovement ->fill($inData)->save();
                } else {
                    $accountIn = Account::find($request->input('movement.account_id'));
                    $accountOut = Account::find($request->input('movement.account_end_id'));

                    $outData = [
                        'account_id' => $request->input('movement.account_end_id'),
                        'description' => $request->input('movement.description'),
                        'amount' => $accountIn->badge_id !== $accountOut->badge_id ? abs((float)$request->input('movement.amount_end')) : abs((float)$request->input('movement.amount')),
                        'trm' => $accountIn->badge_id !== $accountOut->badge_id ? ($request->input('movement.amount_end') ?? $request->input('movement.amount')) / $request->input('movement.amount') : 1,
                        'date_purchase' => $request->input('movement.date_purchase'),
                    ];
                    $movement->fill($outData)->save();
                    
                    $inData = [
                        'account_id' => $request->input('movement.account_id'),
                        'description' => $request->input('movement.description'),
                        'amount' => abs((float)$request->input('movement.amount')) * -1,
                        'trm' => $accountIn->badge_id !== $accountOut->badge_id ? $request->input('movement.amount') / ($request->input('movement.amount_end') ?? $request->input('movement.amount')) : 1,
                        'date_purchase' => $request->input('movement.date_purchase'),
                    ];
                    $inMovement = Movement::where([
                        ['id', $movement->transfer_id]
                    ])->first();

                    $inMovement ->fill($inData)->save();
                }
            } else {

                $transfer_id = Category::where([
                    ['user_id', $request->user()->id],
                    ['group_id', env('GROUP_TRANSFER_ID')]
                ])
                ->first();
    
                 // Create out move
                 $movement = Movement::create([
                    'account_id' => $request->input('movement.account_id'),
                    'category_id' => $transfer_id->id,
                    'description' => $request->input('movement.description'),
                    'amount' => abs((float)$request->input('movement.amount')) * -1,
                    'trm' => $request->input('movement.amount') / ($request->input('movement.amount_end') ?? $request->input('movement.amount')),
                    'date_purchase' => $request->input('movement.date_purchase'),
                    'user_id' => $request->user()->id,
                ]);
    
                // Create in move
                $movement = Movement::create([
                    'account_id' => $request->input('movement.account_end_id'),
                    'category_id' => $transfer_id->id,
                    'description' => $request->input('movement.description'),
                    'amount' => abs((float)$request->input('movement.amount_end')) > 0.0 ? abs((float)$request->input('movement.amount_end')) : abs((float)$request->input('movement.amount')),
                    'trm' => ($request->input('movement.amount_end') ?? $request->input('movement.amount')) / $request->input('movement.amount'),
                    'date_purchase' => $request->input('movement.date_purchase'),
                    'user_id' => $request->user()->id,
                    'transfer_id' => $movement->id
                ]);
            }

        }
        

        Toast::info(__('Movement was saved.'));

        return redirect()->route(session('route'), session('account_id'));
    }

    /**
     * @param Movement $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(Movement $movement)
    {
        $transfer_id = $movement->transfer_id;
        $movement->delete();
        
        if($transfer_id) {
            Movement::find($transfer_id)->delete();
        }

        Toast::info(__('Movement was removed'));

        return redirect()->route('platform.index');
    }

}
