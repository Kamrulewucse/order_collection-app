@extends('layouts.app')
@section('title','Balance Sheet')
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
                <form action="{{ route('report.balance_sheet') }}">
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
                                    <h3 class="text-center m-0" style="font-size: 20px !important;">
                                        STATEMENT OF FINANCIAL POSITION </h3>
                                    <h3 class="text-center" style="font-size: 20px !important;">FOR THE
                                        AS AT {{ date('d',strtotime(request('end_date'))) }}th {{ date('F',strtotime(request('end_date'))) }},  {{ date('Y',strtotime(request('end_date'))) }} (Provisional)</h3>
                                </div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th  width="50%" class="text-center" rowspan="2">Particulars</th>
                                    <th width="4%" class="text-center" rowspan="2">Notes</th>
                                    <th colspan="4" class="text-center">Amount in Taka</th>
                                </tr>
                                <tr>
                                    <th class="text-center">{{ date('d-m-Y',strtotime(request('end_date'))) }}</th>
                                    <th width="2%"></th>
                                    <th class="text-center">{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date'))))}}</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td colspan="4">&nbsp;</td></tr>
                                @php
                                    $assetSlArray = [];
                                    $assetSl = 1;
                                    $totalAssetBalance = 0;
                                    $totalPreviousAssetBalance = 0;
                                @endphp

                                @foreach($assets as $asset)
                                    @php
                                        $rowData = [];
                                        foreach($asset->accountGroups as $accountHeadType) {
                                            $assetBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 1);
                                            $previousAssetBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 2);
                                            $rowData[] = [
                                                'id' => $accountHeadType->id,
                                                'name' => $accountHeadType->name,
                                                'note_no' => $accountHeadType->note_no,
                                                'assetBalance' => $assetBalance,
                                                'previousAssetBalance' => $previousAssetBalance,
                                            ];
                                        }
                                   $totalSubAssetBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['assetBalance'];
                                    }, 0);

                                    $totalPreviousSubAssetBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['previousAssetBalance'];
                                    }, 0);
                                    $totalAssetBalance += $totalSubAssetBalance;
                                    $totalPreviousAssetBalance += $totalPreviousSubAssetBalance;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $assetSl }}.<u>{{ $asset->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalSubAssetBalance), 2) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalPreviousSubAssetBalance), 2) }}</b></td>
                                    </tr>

                                    @foreach($rowData as $row)
                                        <tr>
                                            <td class="text-left"><span class="custom-ml-18">{{ $row['name'] }}</span></td>
                                            <td><b>{{ $row['note_no'] }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['assetBalance']), 2) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['previousAssetBalance']), 2) }}</td>
                                        </tr>

                                    @endforeach
                                    @php
                                        $assetSlArray[] = $assetSl;
                                        $assetSl++;
                                    @endphp
                                @endforeach

                                <tr><td colspan="4">&nbsp;</td></tr>
                                <tr>
                                    <td class="text-left" colspan="2"><b>Total Assets {{ '(' . implode('+', $assetSlArray) . ')' }}</b></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($totalAssetBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($totalPreviousAssetBalance),2) }}</b></td>
                                </tr>
                                <tr><td colspan="4">&nbsp;</td></tr>

                                @php
                                    $equityLiabilityArray = [];
                                    $equityLiabilitySl = $assetSl;
                                    $totalEquityBalance = 0;
                                    $totalPreviousEquityBalance = 0;
                                @endphp

                                @foreach($equities as $equity)
                                    @php
                                        $rowData = [];
                                        foreach($equity->accountGroups as $accountHeadType) {
                                            $equityBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 1);
                                            $previousEquityBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 2);
                                            $rowData[] = [
                                                'id' => $accountHeadType->id,
                                                'name' => $accountHeadType->name,
                                                'note_no' => $accountHeadType->note_no,
                                                'equityBalance' => $equityBalance,
                                                'previousEquityBalance' => $previousEquityBalance,
                                            ];
                                        }
                                   $totalSubEquityBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['equityBalance'];
                                    }, 0);

                                    $totalPreviousSubEquityBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['previousEquityBalance'];
                                    }, 0);
                                    $totalEquityBalance += $totalSubEquityBalance;
                                    $totalPreviousEquityBalance += $totalPreviousSubEquityBalance;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $equityLiabilitySl }}.<u>{{ $equity->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalSubEquityBalance), 2) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalPreviousSubEquityBalance), 2) }}</b></td>
                                    </tr>

                                    @foreach($rowData as $row)
                                        <tr>
                                            <td class="text-left"><span class="custom-ml-18">{{ $row['name'] }}</span></td>
                                            <td><b>{{ $row['note_no'] }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['equityBalance']), 2) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['previousEquityBalance']), 2) }}</td>
                                        </tr>

                                    @endforeach
                                    @php
                                        $equityLiabilityArray[] = $equityLiabilitySl;
                                        $equityLiabilitySl++;
                                    @endphp
                                @endforeach

                                <tr><td colspan="4">&nbsp;</td></tr>

                                @php
                                    $totalLiabilityBalance = 0;
                                    $totalPreviousLiabilityBalance = 0;
                                @endphp

                                @foreach($liabilities as $liability)
                                    @php
                                        $rowData = [];
                                        foreach($liability->accountGroups as $accountHeadType) {
                                            $liabilityBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 1);
                                            $previousLiabilityBalance = accountGroupBalance($accountHeadType->id, request('start_date'), request('end_date'), 2);
                                            $rowData[] = [
                                                'id' => $accountHeadType->id,
                                                'name' => $accountHeadType->name,
                                                'note_no' => $accountHeadType->note_no,
                                                'liabilityBalance' => $liabilityBalance,
                                                'previousLiabilityBalance' => $previousLiabilityBalance,
                                            ];
                                        }
                                   $totalSubLiabilityBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['liabilityBalance'];
                                    }, 0);

                                    $totalPreviousSubLiabilityBalance = array_reduce($rowData, function($carry, $row) {
                                        return $carry + $row['previousLiabilityBalance'];
                                    }, 0);
                                    $totalLiabilityBalance += $totalSubLiabilityBalance;
                                    $totalPreviousLiabilityBalance += $totalPreviousSubLiabilityBalance;
                                    @endphp
                                    @if(!$loop->first)
                                        <tr><td colspan="4">&nbsp;</td></tr>
                                    @endif
                                    <tr>
                                        <td class="text-left" colspan="2"><b>{{ $equityLiabilitySl }}.<u>{{ $liability->name }}:</u></b></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalSubLiabilityBalance), 2) }}</b></td>
                                        <td></td>
                                        <td class="text-right"><b>{{ number_format(abs($totalPreviousSubLiabilityBalance), 2) }}</b></td>
                                    </tr>

                                    @foreach($rowData as $row)
                                        <tr>

                                            <td class="text-left"><span class="custom-ml-18">{{ $row['name'] }}</span></td>
                                            <td><b>{{ $row['note_no'] }}</b></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['liabilityBalance']), 2) }}</td>
                                            <td></td>
                                            <td class="text-right border-left-right {{ $loop->first ? 'border-top' : ''}} {{ $loop->last ? 'border-bottom' : ''}}">{{ number_format(abs($row['previousLiabilityBalance']), 2) }}</td>
                                        </tr>

                                    @endforeach
                                    @php
                                        $equityLiabilityArray[] = $equityLiabilitySl;
                                        $equityLiabilitySl++;
                                    @endphp
                                @endforeach

                                <tr><td colspan="4">&nbsp;</td></tr>


                                <tr>
                                    <td class="text-left" colspan="2"><b>Total Equity and Liabilities {{ '(' . implode('+', $equityLiabilityArray) . ')' }}</b></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($totalEquityBalance + $totalLiabilityBalance),2) }}</b></td>
                                    <td></td>
                                    <td class="text-right double-single-border"><b>{{ number_format(abs($totalPreviousEquityBalance + $totalPreviousLiabilityBalance),2) }}</b></td>
                                </tr>
                                <tr><td colspan="4">&nbsp;</td></tr>
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
                        title: "Balance Sheet",
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
                        a.download = 'balance_sheet.xlsx';
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
