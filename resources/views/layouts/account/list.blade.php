<div class="row">
    @isset($accounts)
    @foreach ($accounts as $account)
    @php
    $color = $account->balance + $account->init_amount > 0 ? 'success' : 'danger';
    @endphp
    <div class="col mb-4">
        <a href="/accounts/{{$account->id}}/movements" data-turbo="true">
            <div class="rounded shadow bg-white p-3 mx-auto cursor" style="width: 315px; height: 160px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold lh-md" style="font-size: 24px;">
                        {{$account->name}}
                    </h2>
                    <p class="m-0">{{$account->currency->code}}</p>
                </div>
                <div style="height: 56px; width: 185px;">
                    <p>{{$account->description}}</p>
                </div>
                <p class="m-0 float-end text-{{$color}}">$ {{number_format($account->balance + $account->init_amount, 2, ',', '.')}}</p>
            </div>
        </a>
    </div>
    @endforeach
    @endisset
    @isset($events)
    @foreach ($events as $event)
        @php
            $balance = implode("", array_map(function ($v) {
            $color = $v["movements"] > 0 ? 'success' : 'danger';
            return "<p class='text-$color m-0'>".number_format($v["movements"], 2, ',', '.'). " " . $v["currency"]."</p>";
            }, $event->balance->toArray()));
        @endphp
        <div class="col mb-4">
            <div class="rounded shadow bg-white p-3 mx-auto cursor" style="width: 315px; height: 135px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);">
                <div class="d-flex justify-content-between align-items-center"  formaction="{{request()->server('HTTP_HOST').'/'.request()->path()}}/?event_id={{$event->id}}" data-controller="modal-toggle" data-action="click->modal-toggle#targetModal" data-modal-toggle-title="" data-modal-toggle-key="movementsModal" data-modal-toggle-async="true" data-modal-toggle-params="{&quot;event_id&quot;:{{$event->id}}}" data-modal-toggle-action="{{request()->server('HTTP_HOST').'/'.request()->path()}}/?event_id={{$event->id}}" data-modal-toggle-open="">
                    <h2 class="fw-bold lh-md" style="font-size: 24px;">
                        {{$event->name}}
                    </h2>
                </div>
                <div>{!!$balance!!}</div>
                <div class="float-end">
                    {!!
                        Orchid\Screen\Actions\DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Orchid\Screen\Actions\Link::make(__('Edit'))
                                ->route('platform.events.edit', $event->id)
                                ->icon('pencil'),
                        ])
                    !!}
                </div>
            </div>
        </div>
    @endforeach
    @endisset
    @isset($investments)
    @foreach ($investments as $investment)
        <div class="col mb-4">
            <div class="rounded shadow bg-white p-3 mx-auto cursor" style="width: 315px; height: 135px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.5);">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="fw-bold lh-md" formaction="{{request()->server('HTTP_HOST').'/'.request()->path()}}/?investment_id={{$investment->id}}" data-controller="modal-toggle" data-action="click->modal-toggle#targetModal" data-modal-toggle-title="" data-modal-toggle-key="movementsModal" data-modal-toggle-async="true" data-modal-toggle-params="{&quot;investment_id&quot;:{{$investment->id}}}" data-modal-toggle-action="{{request()->server('HTTP_HOST').'/'.request()->path()}}/?investment_id={{$investment->id}}" data-modal-toggle-open="" style="font-size: 24px;">
                        {{$investment->name}}
                    </h2>
                    <div>
                    {!!
                        Orchid\Screen\Actions\DropDown::make()
                        ->icon('options-vertical')
                        ->list([
                            Orchid\Screen\Actions\Link::make(__('Edit'))
                                ->route('platform.investments.edit', $investment->id)
                                ->icon('pencil'),
                        ])
                    !!}
                </div>
                </div>
                <p class="m-0"><b>{{ __('Start Amount')}}:</b>$ {{number_format($investment->init_amount, 2, ',', '.')}}</p>
                <p class="m-0"><b>{{ __('Current Amount')}}:</b>$ {{number_format($investment->end_amount, 2, ',', '.')}}</p>
                <div class="d-flex justify-content-between align-items-center">
                    <p class="m-0">$ {{number_format($investment->balance, 2, ',', '.')}} {{$investment->currency->code}}</p>
                    <p class="m-0">{{round(($investment->end_amount - $investment->init_amount) / $investment->init_amount * 100, 2)}} %</p>
                </div>
            </div>
        </div>
    @endforeach
    @endisset
</div>