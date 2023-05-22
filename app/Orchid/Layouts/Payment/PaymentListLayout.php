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
            TD::make(__('Actions'))
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(fn (PlannedPayment $payment) => DropDown::make()
                    ->icon('options-vertical')
                    ->list([
                        Link::make(__('Edit'))
                            ->route('platform.payments.edit', $payment->id)
                            ->icon('pencil'),
                    ])),

            TD::make('account_id', __('Account'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => $payment->account->name),
            
            TD::make('category_id', __('Category'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => $payment->category->name),
            
            TD::make('amount', __('Amount'))
                ->sort()
                ->cantHide()
                ->render(fn (PlannedPayment $payment) => number_format($payment->amount)),      

        ];
    }
}
