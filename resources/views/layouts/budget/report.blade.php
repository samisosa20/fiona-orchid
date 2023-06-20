<div class="row">
    <div class="col-md">
        <div class="p-4 bg-white rounded shadow-sm mb-3 pt-3">
            <small class="text-muted d-block mb-1">{{ __('Incomes') }}</small>
            @foreach ($incomes as $key => $category)
                <div class="py-4 px-3 border-bottom cursor-pointer" data-bs-toggle="collapse"
                    data-bs-target="#collapseIncome{{ $key }}" aria-expanded="false"
                    aria-controls="collapseIncome{{ $key }}">
                    <p>{{ $category['name'] }}</p>
                    @php
                        $sumsMove = [];
                        $sumsBudget = [];
                        
                        foreach ($category['movements'] as $subArray) {
                            foreach ($subArray as $item) {
                                $code = $item['code'];
                                $value = $item['amount'];
                        
                                $sumsMove[$code] = isset($sumsMove[$code]) ? $sumsMove[$code] + $value : $value;
                            }
                        }
                        foreach ($category['budgets'] as $subArray) {
                            foreach ($subArray as $item) {
                                $code = $item->currency->code;
                                $value = $item->amount;
                        
                                $sumsBudget[$code] = isset($sumsBudget[$code]) ? $sumsBudget[$code] + $value : $value;
                            }
                        }
                    @endphp
                    @foreach ($sumsMove as $keyMove => $move)
                        @php
                            $porce = 0;
                            $budget = 0;
                            $color = 'danger';
                            if (isset($sumsBudget[$keyMove])) {
                                $porce = round((abs($move) / $sumsBudget[$keyMove]) * 100, 2);
                                $budget = $sumsBudget[$keyMove];
                                $color = $porce > 90 ? 'success' : ($porce > 65 ? 'warning' : 'danger');
                            }
                        @endphp
                        <div>
                            <small>{{ number_format(abs($move ?? 0)) }}</small>
                            <small class="float-end">{{ number_format($budget) }} {{ $keyMove }}</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-{{$color}} rounded fw-bold" role="progressbar"
                                aria-valuenow="{{ $porce }}" aria-valuemin="0" aria-valuemax="100"
                                style="width: {{ $porce }}%">{{ $porce }}%</div>
                        </div>
                    @endforeach
                    @foreach ($category['sub_categories'] as $income)
                        <div class="collapse" id="collapseIncome{{ $key }}">
                            <div class="py-4 px-3 border-bottom">
                                <p>{{ $income->name }}</p>
                                @if (count($income->budget) > 0)
                                    @foreach ($income->budget as $budget)
                                        @php
                                            $move = array_values(array_filter($income->movements->toArray(), fn($v) => $v['code'] === $budget->currency->code))[0] ?? [];
                                            $porce = 0;
                                            $color = 'danger';
                                            if (count($move) > 0) {
                                                $porce = round(($move['amount'] / $budget->amount) * 100, 2);
                                                $color = $porce > 90 ? 'success' : ($porce > 65 ? 'warning' : 'danger');
                                            }
                                        @endphp
                                        <div>
                                            <small>{{ number_format(abs($move['amount'] ?? 0)) }}</small>
                                            <small class="float-end">{{ number_format($budget->amount) }}
                                                {{ $budget->currency->code }}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{$color}} rounded fw-bold" role="progressbar"
                                                aria-valuenow="{{ $porce }}" aria-valuemin="0"
                                                aria-valuemax="100" style="width: {{ $porce }}%">
                                                {{ $porce }}%</div>
                                        </div>
                                    @endforeach
                                @else
                                    @if (count($income->movements) > 0)
                                        @foreach ($income->movements as $movements)
                                            <div>
                                                <small>{{ number_format(abs($movements['amount'] ?? 0)) }}</small>
                                                <small class="float-end">{{ number_format(0) }}
                                                    {{ $movements['code'] }}</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-success rounded fw-bold"" role="progressbar"
                                                    aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                    style="width: 100%">100%</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div>
                                            <small>{{ number_format(abs(0)) }}</small>
                                            <small class="float-end">{{ number_format(0) }}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger rounded fw-bold"" role="progressbar"
                                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                                style="width: 0%">0%</div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-md">
        <div class="p-4 bg-white rounded shadow-sm mb-3 pt-3">
            <small class="text-muted d-block mb-1">{{ __('Expensives') }}</small>
            @foreach ($expensives as $key => $category)
                <div class="py-4 px-3 border-bottom cursor-pointer" data-bs-toggle="collapse"
                    data-bs-target="#collapseExpensive{{ $key }}" aria-expanded="false"
                    aria-controls="collapseExpensive{{ $key }}">
                    <p>{{ $category['name'] }}</p>
                    @php
                        $sumsMove = [];
                        $sumsBudget = [];
                        
                        foreach ($category['movements'] as $subArray) {
                            foreach ($subArray as $item) {
                                $code = $item['code'];
                                $value = $item['amount'];
                        
                                $sumsMove[$code] = isset($sumsMove[$code]) ? $sumsMove[$code] + $value : $value;
                            }
                        }
                        foreach ($category['budgets'] as $subArray) {
                            foreach ($subArray as $item) {
                                $code = $item->currency->code;
                                $value = $item->amount;
                        
                                $sumsBudget[$code] = isset($sumsBudget[$code]) ? $sumsBudget[$code] + $value : $value;
                            }
                        }
                    @endphp
                    @if (count($sumsMove) > 0)
                        @foreach ($sumsMove as $keyMove => $move)
                            @php
                                $porce = 0;
                                $budget = 0;
                                $color = 'success';
                                if (isset($sumsBudget[$keyMove])) {
                                    $porce = round((abs($move) / $sumsBudget[$keyMove]) * 100, 2);
                                    $budget = $sumsBudget[$keyMove];
                                    $color = $porce > 90 ? 'danger' : ($porce > 65 ? 'warning' : 'success');
                                } elseif (abs($move) > 0) {
                                    $porce = 100;
                                    $color = 'danger';
                                }
                            @endphp
                            <div>
                                <small>{{ number_format(abs($move ?? 0)) }}</small>
                                <small class="float-end">{{ number_format($budget) }} {{ $keyMove }}</small>
                            </div>
                            <div class="progress">
                                <div class="progress-bar bg-{{$color}} rounded fw-bold" role="progressbar"
                                    aria-valuenow="{{ $porce }}" aria-valuemin="0" aria-valuemax="100"
                                    style="width: {{ $porce }}%">{{ $porce }}%</div>
                            </div>
                        @endforeach
                    @else
                        <div>
                            <small>{{ number_format(abs(0)) }}</small>
                            <small class="float-end">{{ number_format(0) }}</small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-danger rounded fw-bold" role="progressbar"
                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                style="width: 0%">0%</div>
                        </div>
                    @endif
                    @foreach ($category['sub_categories'] as $expensive)
                        <div class="collapse" id="collapseExpensive{{ $key }}">
                            <div class="py-4 px-3 border-bottom">
                                <p>{{ $expensive->name }}</p>
                                @if (count($expensive->budget) > 0)
                                    @foreach ($expensive->budget as $budget)
                                        @php
                                            $move = array_values(array_filter($expensive->movements->toArray(), fn($v) => $v['code'] === $budget->currency->code))[0] ?? [];
                                            $porce = 0;
                                            $color = 'success';
                                            if (count($move) > 0) {
                                                $porce = round((abs($move['amount']) / $budget->amount) * 100, 2);
                                                $color = $porce > 90 ? 'danger' : ($porce > 65 ? 'warning' : 'success');
                                            }
                                        @endphp
                                        <div>
                                            <small>{{ number_format(abs($move['amount'] ?? 0)) }}</small>
                                            <small class="float-end">{{ number_format($budget->amount) }}
                                                {{ $budget->currency->code }}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-{{$color}} rounded fw-bold" role="progressbar"
                                                aria-valuenow="{{ $porce }}" aria-valuemin="0"
                                                aria-valuemax="100" style="width: {{ $porce }}%">
                                                {{ $porce }}%</div>
                                        </div>
                                    @endforeach
                                @else
                                    @if (count($expensive->movements) > 0)
                                        @foreach ($expensive->movements as $movements)
                                            <div>
                                                <small>{{ number_format(abs($movements['amount'] ?? 0)) }}</small>
                                                <small class="float-end">{{ number_format(0) }}
                                                    {{ $movements['code'] }}</small>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-danger rounded fw-bold" role="progressbar"
                                                    aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"
                                                    style="width: 100%">100%</div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div>
                                            <small>{{ number_format(abs(0)) }}</small>
                                            <small class="float-end">{{ number_format(0) }}</small>
                                        </div>
                                        <div class="progress">
                                            <div class="progress-bar bg-danger rounded fw-bold" role="progressbar"
                                                aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                                style="width: 0%">0%</div>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>
    <div class="col-md">
        <div class="p-4 bg-white rounded shadow-sm mb-3 pt-3">
            <small class="text-muted d-block mb-1">{{ __('Utility') }}</small>
            <div class="py-4 px-3 border-bottom">
                <p>Utility</p>
                @foreach ($totalMovements as $key => $move)
                    @php
                        $porce = 0;
                        $budget = 0;
                        $color = 'danger';
                        if(isset($totalBudgets[$key])) {
                            $porce = round(($move / $totalBudgets[$key]) * 100, 2);
                            $color = $porce > 90 ? 'success' : ($porce > 65 ? 'warning' : 'danger');
                            $budget = $totalBudgets[$key];
                        }
                    @endphp
                    <div>
                        <small>{{ number_format($move) }}</small>
                        <small class="float-end">{{ number_format($budget) }} {{$key}}</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar bg-{{$color}} rounded" role="progressbar" aria-valuenow="{{$porce}}" aria-valuemin="0"
                            aria-valuemax="100" style="width: {{$porce}}%">{{$porce}}%</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
