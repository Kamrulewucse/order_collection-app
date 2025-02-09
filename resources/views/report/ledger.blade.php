@extends('layouts.app')
@section('title','Ledger Report')
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
        @media print{
            @page {
                size: auto;
                margin: 20px 20px .5in 80px !important;
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
                <form action="{{ route('report.ledger') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request()->get('start_date') ?? $currentDate  }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request()->get('end_date') ?? date('d-m-Y')  }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_head">Account Head <span
                                            class="text-danger">*</span></label>
                                    <select name="account_head" id="account_head" class="form-control select2">
                                        <option value="">All Account Head</option>
                                        @foreach($accountHeadsList as $accountHeadList)
                                        <option {{ request('account_head') == $accountHeadList->id ? 'selected' : '' }} value="{{ $accountHeadList->id }}">{{ $accountHeadList->name }} - {{ $accountHeadList->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search" class="btn btn-primary bg-gradient-primary form-control" value="Search">
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
                        <a href="#" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm "><i class="fa fa-print"></i></a>
                        <a role="button" id="btn-export" class="btn btn-primary bg-gradient-indigo btn-sm"><i class="fas fa-file-excel"></i></a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive" id="printArea">
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
                            <div class="row">
                                <div class="col-12">
                                    <h3 class="text-center m-0 mb-2 mt-2" style="font-size: 19px !important;"><b>Ledger for : {{ request('start_date') }} to {{ request('end_date') }}</b></h3>

                                </div>
                            </div>

                            @foreach($accountHeads as $accountHead)
                                <?php

                                $firstDebitTotal = 0;
                                $firstCreditTotal = 0;
                                //3=Asset,5=Liability,4=Equity/Capital

                                $parentType = App\Models\AccountGroup::find($accountHead->account_group_id);
                                if ($parentType->top_parent_id) {
                                    if ($parentType->top_parent_id == 3) {
                                        $firstDebitTotal = $accountHead->opening_balance;
                                    } elseif ($parentType->top_parent_id == 4 || $parentType->top_parent_id == 5) {
                                        $firstCreditTotal = $accountHead->opening_balance;
                                    }
                                }

                                $debitOpening = $firstDebitTotal + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_debit : 0);
                                $creditOpening = $firstCreditTotal + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_credit : 0);

                                $rangeDebitOpening = 0;
                                $rangeCreditOpening = 0;

                                ?>

                                <table class="table table-bordered" style="">
                                        <tr>
                                            <th colspan="5" style="font-size: 20px">
                                                A/C Name :{{ $accountHead->name ?? '' }}-({{ $accountHead->code ?? '' }})
                                            </th>
                                        </tr>
                                        <tr>
                                            <th width="10%" class="text-center">Date</th>
                                            <th width="10%" class="text-center">Voucher#</th>
                                            <th width="50%" class="text-left">Particular</th>
                                            <th width="15%" class="text-center">Debit</th>
                                            <th width="15%" class="text-center">Credit</th>
                                        </tr>
                                        @if(($debitOpening - $creditOpening) > 0)
                                            @php
                                                $rangeDebitOpening = $debitOpening - $creditOpening;
                                            @endphp
                                        @else
                                            @php
                                                $rangeCreditOpening = $debitOpening - $creditOpening;
                                            @endphp
                                        @endif
                                        @if(abs(($debitOpening - $creditOpening))  > 0)
                                            <tr>
                                                <td>{{ request('start_date') }}</td>
                                                <td></td>
                                                <td class="text-left">Opening Balance</td>
                                                @if(($debitOpening - $creditOpening) > 0)
                                                    <td class="text-right">{{ number_format($debitOpening - $creditOpening,2) }}</td>
                                                    <td class="text-right"></td>
                                                @else
                                                    <td class="text-right"></td>
                                                    <td class="text-right">{{ number_format($debitOpening - $creditOpening,2) }}</td>
                                                @endif

                                            </tr>
                                        @endif
                                        <?php
                                            $accountHeadDebitTotal = 0;
                                            $accountHeadCreditTotal = 0;
                                            $transactions = $accountHead->transactions;
                                            $monthlyDebitTotal = 0;
                                            $monthlyCreditTotal = 0;
                                            $previousMonthDate = null;
                                        ?>
                                        @if(count($transactions) > 0)

                                            @foreach($transactions as $transaction)
                                                @php
                                                    // Check if the date changed
                                                    $dateChanged = $previousMonthDate !== \Carbon\Carbon::parse($transaction->date)->format('m-Y');
                                                    if ($dateChanged && $previousMonthDate !== null) {
                                                        // Display the subtotal for the previous date
                                                        echo '<tr><td colspan="3" class="text-right">Monthly Total</td><td class="text-right">' . number_format($monthlyDebitTotal, 2) . '</td><td class="text-right">' . number_format($monthlyCreditTotal, 2) . '</td></tr>';
                                                        echo '<tr><td style="padding-bottom: 30px!important;" colspan="4" class="text-right"><b>Monthly Balance:</b></td><td style="padding-bottom: 30px!important;" class="text-right"><b>'.number_format(($monthlyDebitTotal - $monthlyCreditTotal),2).'</b></td></tr>';

                                                        // Reset the total amount for the new date
                                                        $monthlyDebitTotal = 0;
                                                        $monthlyCreditTotal = 0;
                                                    }

                                                    // Accumulate the total amount for the current date
                                                    $previousMonthDate = \Carbon\Carbon::parse($transaction->date)->format('m-Y');
                                                @endphp
                                                <tr>
                                                    <td> {{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                                    <td>{{ $transaction->voucher_no }}</td>
                                                    <td class="text-left">
                                                        @if($transaction->payeeDepositorHead)
                                                            {{ $transaction->payeeDepositorHead->name ?? '' }}
                                                        @endif
                                                       {{ $transaction->notes }}
                                                    </td>
                                                    <td class="text-right">
                                                        @if(in_array($transaction->transaction_type,[1]))
                                                            <?php
                                                                $monthlyDebitTotal += $transaction->amount;
                                                                $accountHeadDebitTotal += $transaction->amount;
                                                            ?>
                                                            {{ number_format($transaction->amount,2) }}
                                                        @else

                                                        @endif

                                                    </td>
                                                    <td class="text-right">
                                                        @if(in_array($transaction->transaction_type,[2]))
                                                            <?php
                                                                $monthlyCreditTotal += $transaction->amount;
                                                                $accountHeadCreditTotal += $transaction->amount;
                                                            ?>
                                                            {{ number_format($transaction->amount,2) }}
                                                        @else

                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach


                                        @if ($previousMonthDate !== null)
                                            <tr>
                                                <td colspan="3" class="text-right">Monthly Total:</td>
                                                <td class="text-right">{{ number_format($monthlyDebitTotal, 2) }}</td>
                                                <td class="text-right">{{ number_format($monthlyCreditTotal, 2) }}</td>
                                            </tr>
                                            <tr>
                                                 <td style="padding-bottom: 30px!important;" colspan="4" class="text-right"><b>Monthly Balance:</b></td>
                                                  <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(($monthlyDebitTotal - $monthlyCreditTotal),2) }}</b></td>
                                            </tr>
                                        @endif
                                        @endif
                                        <tr>
                                            <td colspan="3" class="text-right">Account Total:</td>
                                            <td class="text-right">{{ number_format($accountHeadDebitTotal + $rangeDebitOpening,2) }}</td>
                                            <td class="text-right">{{ number_format($accountHeadCreditTotal + $rangeCreditOpening,2) }}</td>
                                        </tr>
                                        <tr>
                                            <td style="padding-bottom: 30px!important;" colspan="4" class="text-right"><b>Account Balance:</b></td>
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(($accountHeadDebitTotal + $debitOpening) - ($accountHeadCreditTotal + $creditOpening),2) }}</b></td>
                                        </tr>
                                    </table>

                            @endforeach

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
                        title: "Ledger Report",
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
                        a.download = 'ledger_report.xlsx';
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

