@extends('layouts.user', ['header' => true, 'nav' => true, 'demo' => true])

@section('content')
<div class="page-wrapper">
    <div class="container-xl">
        <!-- Page title -->
        <div class="page-header d-print-none">
            <div class="row align-items-center">
                <div class="col">
                    <div class="page-pretitle">
                        {{ __('Overview') }}
                    </div>
                    <h2 class="page-title">
                        {{ __('Checkout') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    @if ($selected_plan == null)
    <div class="container-xl mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">{{ __('No Plan Found') }}</h3>
                    <a href="{{ route('user.checkout', Request::segment(3)) }}"
                        class="btn btn-primary">{{ __('Back') }}</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="container-xl mt-3">
        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">{{ __('Upgrade/Renewal Plan') }}</h3>

                        <div class="card-table table-responsive">
                            <table class="table table-vcenter">
                                <thead>
                                    <tr>
                                        <th class="w-1">Description</th>
                                        <th class="w-1">Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div>
                                                {{ $selected_plan->plan_name }} - {{ $selected_plan->validity }}
                                                {{ __('Days') }}
                                            </div>
                                        </td>
                                        <td class="text-bold">
                                            {{ $currency->symbol }}
                                            {{ $selected_plan->plan_price == '0' ? 0 : number_format($selected_plan->plan_price,2) }}
                                        </td>
                                    </tr>

                                    @if ($config[25]->config_value > 0)

                                    <tr>
                                        <td>
                                            <div>
                                                {{ $config[24]->config_value }}
                                            </div>
                                        </td>
                                        <td class="text-bold"> {{ $currency->symbol }}
                                            {{ number_format($selected_plan->plan_price * $config[25]->config_value / 100, 2) }}
                                        </td>
                                    </tr>

                                    @endif


                                    <tr>
                                        <td class="h3 text-bold"> {{ __('Total Payable') }} </td>
                                        <td class="w-1 text-bold h3"> {{ $currency->symbol }}
                                            {{ number_format($total, 2) }}
                                        </td>
                                    </tr>


                                </tbody>
                            </table>
                        </div>



                    </div>
                </div>


            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">{{ __('Payment method') }}</h3>

                        <form action="{{ route('prepare.payment.gateway', $selected_plan->plan_id) }}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-4">
                                    <div class="mb-3">
                                        <div class="form-selectgroup form-selectgroup-boxes d-flex flex-column">
                                            @foreach ($gateways as $gateway)
                                            <label class="form-selectgroup-item flex-fill">
                                                <input type="radio" name="payment_gateway_id"
                                                    value="{{ $gateway->payment_gateway_id }}"
                                                    class="form-selectgroup-input">
                                                <div class="form-selectgroup-label d-flex align-items-center p-3">
                                                    <div class="me-3">
                                                        <span class="form-selectgroup-check"></span>
                                                    </div>
                                                    <div>
                                                        <span
                                                            class="payment payment-provider-{{ $gateway->payment_gateway_name == 'Paypal' ? 'paypal' : 'visa' }} payment-xs me-2">
                                                            <img src="{{ url('/') }}{{ $gateway->payment_gateway_logo }}"
                                                                alt="">
                                                        </span>
                                                        {{ $gateway->display_name }} <strong></strong>
                                                    </div>
                                                </div>
                                            </label>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <input type="submit" value="{{ __('Continue for payment') }}"
                                            class="btn btn-primary">
                                    </div>

                                </div>

                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>


@endif
@include('user.includes.footer')
</div>

@endsection