<?php

declare(strict_types=1);

namespace App\Orchid\Screens\Payment;

use Illuminate\Http\Request;
use Orchid\Screen\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use App\Models\PlannedPayment;

use App\Orchid\Layouts\Payment\PaymentEditLayout;

class PaymentEditScreen extends Screen
{
    /**
     * @var PlannedPayment
     */
    public $payment;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @param PlannedPayment $payment
     *
     * @return array
     */
    public function query(PlannedPayment $payment, Request $request): iterable
    {
        return [
            'payment' => $payment,
            'user' => $request->user(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->payment->exist ? 'Edit Payment' : 'Create Payment';
    }

    /**
     * Display header description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Details of your payment planned';
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
                ->confirm(__('Once the Payment is deleted, all of its resources and data will be permanently deleted. Before deleting your Event, please download any data or information that you wish to retain.'))
                ->method('remove')
                ->canSee(!!$this->payment->id),

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

            Layout::block(PaymentEditLayout::class)

        ];
    }

    /**
     * @param PlannedPayment    $payment
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function save(PlannedPayment $payment, Request $request)
    {
        $payment->fill($request->collect('payment')->toArray())
            ->fill(['user_id' => $request->user()->id])
            ->save();

        Toast::info(__('Payment was saved.'));

        return redirect()->route('platform.payments');
    }

    /**
     * @param PlannedPayment $user
     *
     * @throws \Exception
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function remove(PlannedPayment $payment)
    {
        $payment->delete();

        Toast::info(__('Payment was removed'));

        return redirect()->route('platform.payments');
    }

}
