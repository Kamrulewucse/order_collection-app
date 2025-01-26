@extends('layouts.app')
@section('title','Trial Balance Report')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        @media print {
            @page {
                size: auto;
                margin: 20px 20px .6in 20px !important;
            }
            .table-bordered td, .table-bordered th {
                font-size: 20px !important;
            }
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
                <form action="{{ route('report.trial_balance') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date') ?? $currentDate }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y')  }}">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_head">Account Head <span
                                            class="text-danger">*</span></label>
                                    <select name="account_head" id="account_head" class="form-control select2">
                                        <option value="">All Account Head</option>
                                        @foreach($accountHeadsList as $accountHeadList)
                                            <option
                                                {{ request('account_head') == $accountHeadList->id ? 'selected' : '' }} value="{{ $accountHeadList->id }}">{{ $accountHeadList->name }}
                                                - {{ $accountHeadList->code }}</option>
                                        @endforeach
                                    </select>
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
    @if(count($accountHeads) > 0)
        <div class="row">
            <div class="col-12">
                <div class="card card-default">
                    <div class="card-header">
                        <a href="#" onclick="getprint('printArea')"class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i></a>
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
                                    <h3 class="text-center m-0 mt-2" style="font-size: 25px !important;">Trial
                                        Balance</h3>
                                    <h3 class="text-center m-0 mb-2" style="font-size: 19px !important;">
                                        Date: {{ request('start_date') }} to {{ request('end_date') }}</h3>
                                </div>
                            </div>
                            <table id="table" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>S/L</th>
                                    <th>Code</th>
                                    <th>Account Head</th>
                                    <th>Debit(Taka)</th>
                                    <th>Credit(Taka)</th>
                                    <th>Balance(Taka)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php

                                    $totalBalance = 0;
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                @foreach($accountHeads as $accountHead)

                                    @php


                                        $openingDebit = 0;
                                        $openingCredit = 0;
                                        //3=Asset,5=Liability,4=Equity/Capital

                                     $parentType = App\Models\AccountGroup::find($accountHead->account_group_id);
                                     if ($parentType->top_parent_id) {
                                            if ($parentType->top_parent_id == 3) {
                                                $openingDebit = $accountHead->opening_balance;
                                            } elseif ($parentType->top_parent_id == 4 || $parentType->top_parent_id == 5) {
                                            $openingCredit = $accountHead->opening_balance;
                                            }
                                        }


                                    $debitOpeningTotal = $openingDebit + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_debit : 0);
                                    $creditOpeningTotal = $openingCredit + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_credit : 0);

                                    $currentDebitTotal = (count($accountHead->transactions) > 0 ? $accountHead->transactions[0]->debit : 0);
                                    $currentCreditTotal = (count($accountHead->transactions) > 0 ? $accountHead->transactions[0]->credit : 0);


                                    $debit = $currentDebitTotal + $debitOpeningTotal;
                                    $credit = $currentCreditTotal + $creditOpeningTotal;
                                    $balance = $debit - $credit;
                                    $totalDebit += $debit;
                                    $totalCredit += $credit;
                                    $totalBalance += $balance;

                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $accountHead->code }}</td>
                                        <td class="text-left">{{ $accountHead->name }}</td>
                                        <td class="text-right">{{ number_format($debit,2) }}</td>
                                        <td class="text-right">{{ number_format($credit,2) }}</td>
                                        <td class="text-right">{{ number_format($balance,2)  }}</td>
                                    </tr>

                                @endforeach
                                <tr>
                                    <th colspan="3" class="text-right">Total Balance</th>
                                    <th colspan="" class="text-right">{{ number_format($totalDebit,2) }}</th>
                                    <th colspan="" class="text-right">{{ number_format($totalCredit,2) }}</th>
                                    <th colspan="" class="text-right">{{ number_format($totalBalance,2) }}</th>
                                </tr>
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
                        title: "Trial Balance",
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
                        a.download = 'trial_balance.xlsx';
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
