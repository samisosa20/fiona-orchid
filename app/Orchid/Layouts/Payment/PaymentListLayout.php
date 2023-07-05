<?php

declare(strict_types=1);

namespace App\Orchid\Layouts\Payment;

use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

use App\Models\PlannedPayment;

class PaymentListLayout extends Table
{
    /**
     * @var string
     */
    public $target = 'payments';

    /**
     * @return string
     */
    protected function textNotFound(): string
    {
        return __('You currently have no Payments created');
    }

    /**
     * @return TD[]
     */
    public function columns(): array
    {
        return [
            TD::make('account_id', __('Account'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) =>  Link::make($payment->account->name)
                ->route('platform.payments.edit', $payment->id)),
            
            TD::make('category_id', __('Category'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => $payment->category->name),
            
            TD::make('amount', __('Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => number_format($payment->amount)),      
            
            TD::make('specific_day', __('Specific Day'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => $payment->specific_day),      

        ];
    }
}
