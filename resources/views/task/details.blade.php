@extends('layouts.app')
@section('title', 'Task Details')
@section('style')
    <style>
        .table-bordered td,
        .table-bordered th {
            padding: 2px;
        }

        .customer-area-border {
            border: 1.5px solid #000 !important;
            ;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }

        .customer-area-border p {
            font-size: 20px !important;
        }

        @media print {

            .table-bordered td,
            .table-bordered th {
                font-size: 18px !important;
            }

            @page {
                size: auto;
                margin: .5in !important;
            }

            .signature-area {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')"
                        class="btn btn-primary bg-gradient-primary btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        <div class="row" style="border-bottom: 1.5px solid #000;">
                            <div class="col-12">
                                <h5 class="text-center">{{ config('app.religious_title') }}</h5>
                            </div>
                            <div class="col-4">
                                <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                    <img height="80px" width="150" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>

                            </div>
                        </div>
                        <div class="row pt-3 pb-3">
                            <div class="col-4">
                                <h5 class="text-center m-0">User: {{ $task->user->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">TASK NO. {{ $task->task_no }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">DATE:
                                    {{ \Carbon\Carbon::parse($task->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">Task Details</th>
                                    <th class="text-center">Task Priority</th>
                                    <th class="text-center">Task Date</th>
                                    <th class="text-center">Task Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($task->details as $item)
                                    <tr class="product-item">
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->task_details }}</td>
                                        <td class="text-center">{{ $item->task_priority }}</td>
                                        <td class="text-center">{{ $item->date }}</td>
                                        <td class="text-center">{{ number_format($item->task_cost,2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th class="text-center">{{ number_format($task->task_total_cost,2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var APP_URL = '{!! url()->full() !!}';

        function getprintChallan(print) {
            document.title = "{{ $task->order_no }}_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
