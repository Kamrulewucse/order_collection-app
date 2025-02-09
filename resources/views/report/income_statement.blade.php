@extends('layouts.app')
@section('title','Income Statement')
@section('style')
    <style>

        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            text-align: center;
            padding: 0.2rem !important;
            font-size: 19px !important;
        }
        .table-bordered td{
            border: none !important;
        }
        .table-bordered {
            border: 1px solid #ffffff;
        }
        table thead{
            border-collapse: inherit !important;
        }
        @media print {
            @page {
                size: auto;
                margin: 20px !important;
            }

        }
        td.double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 4px double #000 !important;
        }
        td.border-left-right{
            border-left: 1px solid #000 !important;
            border-right: 1px solid #000 !important;
        }
        td.border-top{
            border-top: 1px solid #000 !important;
        }
        td.border-bottom{
            border-bottom: 1px solid #000 !important;
        }
        td b a ,td a{
            color: #000 !important;
            text-decoration: none !important;
        }
        .custom-ml-18{
            margin-left: 18px !important;
        }

    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('report.income_statement') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date') ?? $currentDate }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y')  }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>

    @if(request('start_date'))
    <div class="row">
            <div class="col-12">
                <div class="card card-default">
                    <div class="card-header">
                        <a href="#" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i></a>
                        <a role="button" id="btn-export" class="btn btn-primary bg-gradient-indigo btn-sm"><i class="fas fa-file-excel"></i></a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive-sm" id="printArea">
                            <div class="row" style="border-bottom: 1.5px solid #000;">
                                <div class="col-4">
                                    <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                        <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
                                    </h1>
                                </div>
                                <div class="col-8 text-right">
                                    <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                    <h5 class="m-0">{{ config('app.address') }}</h5>
                                    <h5 class="m-0">{{ config('app.contact') }}</h5>
                                    <h5>{{ config('app.email') }}</h5>
                                </div>
                            </div>
                            <div class="row print-heading">
                                <div class="col-12">
                                    <p class="text-center m-0" style="font-size: 20px !important;">
                                        STATEMENT OF PROFIT OR LOSS AND OTHER COMPREHENSIVE INCOME </p>
                                    <p class="text-center" style="font-size: 20px !important;">FOR THE
                                        PERIOD ENDED {{ date('d',strtotime(request('end_date'))) }}th {{ date('F',strtotime(request('end_date'))) }},
                                        {{ date('Y',strtotime(request('end_date'))) }} (Provisional)</p>
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th colspan="" width="50%" class="text-center" rowspan="2">Particulars</th>
                                    <th width="3%" class="text-center" rowspan="2">Note</th>
                                    <th colspan="4" class="text-center">Amount in Taka</th>
                                </tr>
                                <tr>
                                    <th class="text-center">{{ date('d-m-Y',strtotime(request('end_date'))) }}</th>
                                    <th width="2%"></th>
                                    <th class="text-center">{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date')))) }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="5">&nbsp;</td></tr>
                                <tr>

                                    <td colspan="5" class="text-left"><b><u>Revenue</u></b></td>
                                </tr>
                                @php

                                    $grossProfitBalance = 0;
                                    $previousGrossProfitBalance = 0;
                                @endphp
                                @if($revenueType)
                                    @php
                                        $revenueBalance = accountGroupBalanceIncomeStatement($revenueType->id,request('start_date'),request('end_date'),1);
                                        $previousRevenueBalance = accountGroupBalanceIncomeStatement($revenueType->id,request('start_date'),request('end_date'),2);
                                        $grossProfitBalance += $revenueBalance;
                                        $previousGrossProfitBalance += $previousRevenueBalance;
                                    @endphp

                                    <tr>

                                        <td class="text-left">{{ $revenueType->name}}</td>
                                        <td>{{ $revenueType->note_no }}</td>
                                        <td class="text-right border-left-right border-top">{{ number_format(abs($revenueBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-left-right border-top">{{ number_format(abs($previousRevenueBalance),2) }}</td>
                                    </tr>
                                @endif


                                @if($salesReturnsAndAllowances)
                                    @php
                                        $salesReturnsAndAllowancesBalance = accountGroupBalanceIncomeStatement($salesReturnsAndAllowances->id,request('start_date'),request('end_date'),1);
                                        $previousSalesReturnsAndAllowancesBalance  = accountGroupBalanceIncomeStatement($salesReturnsAndAllowances->id,request('start_date'),request('end_date'),2);
                                        $grossProfitBalance += $salesReturnsAndAllowancesBalance;
                                        $previousGrossProfitBalance += $previousSalesReturnsAndAllowancesBalance;
                                    @endphp

                                    <tr>

                                        <td class="text-left">Less: {{ $salesReturnsAndAllowances->name }}</td>
                                        <td>{{ $salesReturnsAndAllowances->note_no }}</td>
                                        <td class="text-right border-left-right">{{ number_format(abs($salesReturnsAndAllowancesBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-left-right">{{ number_format(abs($previousSalesReturnsAndAllowancesBalance),2) }}</td>
                                    </tr>
                                @endif

                                @if($vatExpense)
                                    @php
                                        $vatExpenseBalance = accountGroupBalanceIncomeStatement($vatExpense->id,request('start_date'),request('end_date'),1);
                                        $previousVatExpenseBalance = accountGroupBalanceIncomeStatement($vatExpense->id,request('start_date'),request('end_date'),2);

                                        $grossProfitBalance += $vatExpenseBalance;
                                        $previousGrossProfitBalance += $previousVatExpenseBalance;
                                    @endphp

                                    <tr>

                                        <td class="text-left">Less: {{ $vatExpense->name }}</td>
                                        <td>{{ $vatExpense->note_no }}</td>
                                        <td class="text-right border-left-right border-bottom">{{ number_format(abs($vatExpenseBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-left-right border-bottom">{{ number_format(abs($previousVatExpenseBalance),2) }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="2" class="text-left"><b>Net Sales</b></td>
                                    <td class="text-right"><b>{{ number_format(abs($grossProfitBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ number_format(abs($previousGrossProfitBalance),2) }}</b></td>
                                </tr>

                                @if($costType)
                                    @php
                                        $costOfGoodsBalance = accountGroupBalanceIncomeStatement($costType->id,request('start_date'),request('end_date'),1);
                                        $previousCostOfGoodsBalance = accountGroupBalanceIncomeStatement($costType->id,request('start_date'),request('end_date'),2);
                                        $grossProfitBalance += $costOfGoodsBalance;
                                        $previousGrossProfitBalance += $previousCostOfGoodsBalance;
                                    @endphp
                                    <tr>

                                        <td class="text-left">Less:{{ $costType->name }}</td>
                                        <td>{{ $costType->note_no }}</td>
                                        <td class="text-right border-bottom">{{ number_format(abs($costOfGoodsBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-bottom">{{ number_format(abs($previousCostOfGoodsBalance),2) }}</td>
                                    </tr>

                                @endif
                                <tr>
                                    <td colspan="2" class="text-left"><b>Gross Profit/(Loss)</b></td>
                                    <td class="text-right"><b>{{ number_format(abs($grossProfitBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ number_format(abs($previousGrossProfitBalance),2) }}</b></td>
                                </tr>
                                <tr><td colspan="5">&nbsp;</td></tr>
                                @php
                                    $operatingExpenseBalance = 0;
                                    $operatingExpenseBalance2 = 0;
                                    $previousOperatingExpenseBalance = 0;
                                    $previousOperatingExpenseBalance2 = 0;
                                @endphp
                                @if($administrativeExpense)
                                    @php
                                        $operatingExpenseBalance = accountGroupBalanceIncomeStatement($administrativeExpense->id,request('start_date'),request('end_date'),1);
                                        $previousOperatingExpenseBalance = accountGroupBalanceIncomeStatement($administrativeExpense->id,request('start_date'),request('end_date'),2);
                                    @endphp
                                    <tr>

                                        <td class="text-left">Less: {{ $administrativeExpense->name }}</td>
                                        <td>{{ $administrativeExpense->note_no }}</td>
                                        <td class="text-right text-right border-left-right border-top">{{ number_format(abs($operatingExpenseBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right text-right border-left-right border-top">{{ number_format(abs($previousOperatingExpenseBalance),2) }}</td>
                                    </tr>

                                @endif
                                @if($sellingDistributionExpenses)
                                    @php
                                        $operatingExpenseBalance2 = accountGroupBalanceIncomeStatement($sellingDistributionExpenses->id,request('start_date'),request('end_date'),1);
                                        $previousOperatingExpenseBalance2 = accountGroupBalanceIncomeStatement($sellingDistributionExpenses->id,request('start_date'),request('end_date'),2);
                                    @endphp
                                    <tr>

                                        <td class="text-left">Less: {{ $sellingDistributionExpenses->name }}</td>
                                        <td>{{ $sellingDistributionExpenses->note_no }}</td>
                                        <td class="text-right border-left-right border-bottom">{{ number_format(abs($operatingExpenseBalance2),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-left-right border-bottom">{{ number_format(abs($previousOperatingExpenseBalance2),2) }}</td>
                                    </tr>

                                @endif


                                @php
                                    $operatingProfitLossBalance = $grossProfitBalance - ($operatingExpenseBalance + $operatingExpenseBalance2);
                                    $previousOperatingProfitLossBalance = $previousGrossProfitBalance - ($previousOperatingExpenseBalance + $previousOperatingExpenseBalance2);
                                @endphp



                                <tr><td colspan="5">&nbsp;</td></tr>
                                <tr>
                                    <td colspan="2" class="text-left"><b>Operating Profit/(Loss)</b></td>
                                    <td class="text-right"><b>{{ number_format(abs($operatingProfitLossBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ number_format(abs($previousOperatingProfitLossBalance),2) }}</b></td>
                                </tr>
                                @php
                                    $financialExpensesBalance = 0;
                                    $previousFinancialExpensesBalance = 0;
                                @endphp
                                @if($financialExpenses)
                                    @php
                                        $financialExpensesBalance = accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),1);
                                        $previousFinancialExpensesBalance = accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),2);
                                    @endphp
                                    <tr>

                                        <td class="text-left">Less: {{ $financialExpenses->name }}</td>
                                        <td>{{ $financialExpenses->note_no }}</td>
                                        <td class="text-right border-bottom">{{ number_format(abs($financialExpensesBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-bottom">{{ number_format(abs($previousFinancialExpensesBalance),2) }}</td>
                                    </tr>
                                @endif
                                @php
                                    $totalFinancialExpensesBalance = $operatingProfitLossBalance - $financialExpensesBalance;
                                    $previousTotalFinancialExpensesBalance = $previousOperatingProfitLossBalance - $previousFinancialExpensesBalance;
                                @endphp
                                <tr>
                                    <td colspan="2"></td>
                                    <td class="text-right"><b>{{ number_format(abs($totalFinancialExpensesBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ number_format(abs($previousTotalFinancialExpensesBalance),2) }}</b></td>
                                </tr>
                                <tr><td colspan="5">&nbsp;</td></tr>
                                @php
                                    $otherIncomeBalance = 0;
                                    $previousOtherIncomeBalance = 0;
                                @endphp
                                @if($otherIncome)
                                    @php
                                        $otherIncomeBalance = accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),1);
                                        $previousOtherIncomeBalance = accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),2);
                                    @endphp
                                    <tr>

                                        <td class="text-left">{{ $otherIncome->name }}</td>
                                        <td>{{ $otherIncome->note_no }}</td>
                                        <td class="text-right border-bottom">{{ number_format(abs($otherIncomeBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-bottom">{{ number_format(abs($previousOtherIncomeBalance),2) }}</td>
                                    </tr>
                                @endif
                                @php
                                    $netProfitLossBeforeTaxBalance = $totalFinancialExpensesBalance + $otherIncomeBalance;
                                    $previousNetProfitLossBeforeTaxBalance = $previousTotalFinancialExpensesBalance + $previousOtherIncomeBalance;
                                @endphp
                                <tr>
                                    <td colspan="2" class="text-left"><b>Net Profit /(Loss) before Tax</b></td>
                                    <td class="text-right"><b>{{ number_format(abs($netProfitLossBeforeTaxBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right"><b>{{ number_format(abs($previousNetProfitLossBeforeTaxBalance),2) }}</b></td>
                                </tr>


                                @php
                                    $incomeTaxExpenseBalance = 0;
                                    $previousIncomeTaxExpenseBalance = 0;
                                @endphp
                                @if($incomeTaxExpense)
                                    @php
                                        $incomeTaxExpenseBalance = accountGroupBalanceIncomeStatement($incomeTaxExpense->id,request('start_date'),request('end_date'),1);
                                        $previousIncomeTaxExpenseBalance = accountGroupBalanceIncomeStatement($incomeTaxExpense->id,request('start_date'),request('end_date'),2);
                                    @endphp
                                    <tr>

                                        <td class="text-left">{{ $incomeTaxExpense->name }}</td>
                                        <td>{{ $incomeTaxExpense->note_no }}</td>
                                        <td class="text-right border-bottom">{{ number_format(abs($incomeTaxExpenseBalance),2) }}</td>
                                        <td></td>
                                        <td class="text-right border-bottom">{{ number_format(abs($previousIncomeTaxExpenseBalance),2) }}</td>
                                    </tr>
                                @endif

                                @php
                                    $netProfitLossAfterTaxBalance = $netProfitLossBeforeTaxBalance - $incomeTaxExpenseBalance;
                                    $previousNetProfitLossAfterTaxBalance = $previousNetProfitLossBeforeTaxBalance - $previousIncomeTaxExpenseBalance;
                                @endphp
                                <tr><td colspan="5">&nbsp;</td></tr>
                                <tr>
                                    <td colspan="2" class="text-left"><b>Net Profit/(Loss) After Tax Transferred to BS</b></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($netProfitLossAfterTaxBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($previousNetProfitLossAfterTaxBalance),2) }}</b></td>
                                </tr>
                                <tr><td colspan="5">&nbsp;</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('script')
    <script>
        $(function(){
            $('#btn-export').click(function(){
                //alert('Hasan');
                var htmlContent = $('#printArea').html();

                $.ajax({
                    url: "{{ route('export.html_content') }}",
                    type: "POST",
                    data: {
                        title: "Income Statement",
                        start_date: "{{ request('start_date') }}",
                        end_date: "{{ request('end_date') }}",
                        htmlContent: htmlContent,
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (response) {
                        var url = window.URL.createObjectURL(response);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = 'income_statement.xlsx';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    },
                    error: function (xhr) {
                        console.error("An error occurred: " + xhr.responseText);
                    }
                });
            });
        });

        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
