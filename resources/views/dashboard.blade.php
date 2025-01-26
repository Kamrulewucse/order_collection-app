@extends('layouts.app')
@section('title','Dashboard')
@section('style')
    <style>
        a.canvasjs-chart-credit {
            display: none;
        }
        text.highcharts-credits {
            display: none;
        }
        .highcharts-figure,
        .highcharts-data-table table {
            min-width: 310px;
            max-width: 100% !important;
            margin: 1em auto;
        }

        #datatable {
            font-family: Verdana, sans-serif;
            border-collapse: collapse;
            border: 1px solid #ebebeb;
            margin: 10px auto;
            text-align: center;
            width: 100%;
            max-width: 500px;
        }

        #datatable caption {
            padding: 1em 0;
            font-size: 1.2em;
            color: #555;
        }

        #datatable th {
            font-weight: 600;
            padding: 0.5em;
        }

        #datatable td,
        #datatable th,
        #datatable caption {
            padding: 0.5em;
        }

        #datatable thead tr,
        #datatable tr:nth-child(even) {
            background: #f8f8f8;
        }

        #datatable tr:hover {
            background: #f1f7ff;
        }
    </style>
@endsection
@section('content')
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-gradient-yellow elevation-1"><i class="text-white fa fa-bangladeshi-taka-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL COLLECTION</span>
                    <span class="info-box-number" id="total_collection">{{ number_format($totalCollection,2) }}</span>
                </div>
            </div>
        </div>
        @foreach($paymentModes as $paymentMode)
            @php
                $collectionAmount = \App\Models\Voucher::
                    where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                    //->whereNotNull('booking_id')
                    ->where('date',date('Y-m-d'))
                    ->where('payment_account_head_id',$paymentMode->id)
                    ->sum('amount');
                    $paymentModeLogoPath = null;
                    $imagePath = public_path('img/payment_mode_logo/'.$paymentMode->name.'.png');
                    if (file_exists($imagePath)) {
                        $paymentModeLogoPath = asset('img/payment_mode_logo/'.$paymentMode->name.'.png');
                    }

            @endphp
            <div class="col-12 col-md-3">
                <div class="info-box">

                    @if($paymentModeLogoPath)
                        <div class="card  m-0 p-0">
                            <div class="card-body m-0 p-0">
                                <img height="60px" width="70px" src="{{ asset($paymentModeLogoPath) }}" alt="">
                            </div>
                        </div>
                    @else
                        <span class="info-box-icon bg-gradient-primary elevation-1">
                        <i class="text-white fa fa-bangladeshi-taka-sign"></i>
                    </span>
                    @endif

                    <div class="info-box-content">
                        <span class="info-box-text">{{ $paymentMode->name }}</span>
                        <span class="info-box-number" id="payment_mode_collection_{{ $paymentMode->id }}">{{ number_format($collectionAmount,2) }}</span>
                    </div>
                </div>
            </div>
        @endforeach

        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon  bg-gradient-success elevation-1"><i class="fa fa-handshake"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SETTLED</span>
                    <span class="info-box-number" id="total_settled">{{ number_format($totalSettled,2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-gradient-indigo elevation-1"><i class="fa fa-bank"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">BANK DEPOSIT</span>
                    <span class="info-box-number" id="total_bank_deposit">{{ number_format($totalBankDeposit,2) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="month_wise_revenue_expense"></div>
                        <table id="month_wise_revenue_expense_datatable" style="display: none">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Revenue</th>
                                <th>Expense</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($revenueExpenses as $revenueExpense)
                                <tr>
                                    <th>{{ $revenueExpense['month'] }}</th>
                                    <th>{{ $revenueExpense['revenue'] }}</th>
                                    <th>{{ $revenueExpense['expenses'] }}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </figure>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <figure class="highcharts-figure">
                        <div id="month_wise_profit"></div>
                        <table id="month_wise_profit_datatable" style="display: none">
                            <thead>
                            <tr>
                                <th></th>
                                <th>Profit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($profits as $profit)
                                <tr>
                                    <th>{{ $profit['month'] }}</th>
                                    <th>{{ $profit['profit'] }}</th>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </figure>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="https://code.highcharts.com/highcharts.js"></script>
    <script src="https://code.highcharts.com/modules/data.js"></script>
    <script src="https://code.highcharts.com/modules/exporting.js"></script>
    <script src="https://code.highcharts.com/modules/accessibility.js"></script>
    <script>

        Highcharts.chart('month_wise_revenue_expense', {
            data: {
                table: 'month_wise_revenue_expense_datatable'
            },

            chart: {
                type: 'column',
                options3d: {
                    enabled: true,
                    alpha: 15,
                    beta: 15,
                    depth: 50,
                    viewDistance: 25
                }
            },
            title: {
                text: 'Revenue & Expense'
            },

            xAxis: {
                type: 'category',
            },
            yAxis: {
                allowDecimals: false,
                title: {
                    text: 'Amount'
                },
                labels: {
                    formatter: function () {
                        return this.value.toLocaleString(); // Formats the labels using locale-specific separators
                    }
                }
            },
            series: [
                {
                    color: "#4170c8",
                    dataLabels: {
                        enabled: true,
                        format: '{point.y:,.0f}', // Format for data labels on top of columns
                        inside: false // Position data labels inside or outside the column
                    }
                },
                {
                    color: '#ffc107',
                    dataLabels: {
                        enabled: true,
                        format: '{point.y:,.0f}', // Format for data labels on top of columns
                        inside: false // Position data labels inside or outside the column
                    }
                }
            ]

        });
        Highcharts.chart('month_wise_profit', {
            data: {
                table: 'month_wise_profit_datatable'
            },

            chart: {
                type: 'column',
                options3d: {
                    enabled: true,
                    alpha: 15,
                    beta: 15,
                    depth: 50,
                    viewDistance: 25
                }
            },
            title: {
                text: 'Profit'
            },

            xAxis: {
                type: 'category',
            },
            yAxis: {
                allowDecimals: false,
                title: {
                    text: 'Amount'
                },
                labels: {
                    formatter: function () {
                        return this.value.toLocaleString(); // Formats the labels using locale-specific separators
                    }
                }
            },
            series: [
                {
                    color: "#28a745",
                    dataLabels: {
                        enabled: true,
                        format: '{point.y:,.0f}', // Format for data labels on top of columns
                        inside: false // Position data labels inside or outside the column
                    }
                }
            ]

        });
    </script>
@endsection
