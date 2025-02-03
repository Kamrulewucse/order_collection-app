@extends('layouts.app')
@section('title', 'Dashboard')
@section('style')
    <link rel="stylesheet" type="text/css" media="all" href="{{ asset('themes/backend/date-range/daterangepicker.css') }}" />
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

        #sales-chart,
        #purchases-chart {
            height: 430px;
            /* Set a fixed height for the sales chart */
            width: 100%;
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

        .daterangepicker .ranges li.active {
            background-color: #6610f2;
            color: #fff;
        }

        div#report-range {
            color: #6610f2;
            text-align: center;
            width: max-content !important;
            float: right;
            font-size: 17px;
        }
    </style>
@endsection

@section('content')
    @php
        $startDate = \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') ?? date('d-m-Y');
        $endDate = \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') ?? date('d-m-Y');
    @endphp
    <div class="row justify-content-md-center">
        <div class="col-12 col-md-3">
            {{-- <a href="{{ route('sales.index',['start_date'=>$startDate,'end_date'=>$endDate,'type'=>'regular']) }}" class="info-box"> --}}
            <a href="" class="info-box">
                <span class="info-box-icon bg-gradient-success elevation-1"><i
                        class="text-white fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today Sales</span>
                    <span class="info-box-number" id="total_sales">{{ number_format($totalSales, 2) }}</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-3">
            <a href="" class="info-box">
                <span class="info-box-icon bg-gradient-info elevation-1"><i
                        class="text-white fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today Paid</span>
                    <span class="info-box-number" id="total_sales">{{ number_format($totalPaids, 2) }}</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-3">
            <a href="" class="info-box">
                <span class="info-box-icon bg-gradient-info elevation-1"><i
                        class="text-white fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today Due</span>
                    <span class="info-box-number" id="total_sales">{{ number_format($totalDues, 2) }}</span>
                </div>
            </a>
        </div>
        <div class="col-12 col-md-3">
            <a href="" class="info-box">
                <span class="info-box-icon bg-gradient-danger elevation-1"><i
                        class="text-white fa fa-shopping-cart"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Today Collection</span>
                    <span class="info-box-number" id="total_collection">{{ number_format($totalDuePayment, 2) }}</span>
                </div>
            </a>
        </div>
    </div>
    <div class="row">
        {{-- <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div id="purchases-chart"></div>
                    </div>
                </div>
            </div> --}}
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div id="sales-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>Sale Information</h5>
                    <span class="text-muted">Weekly sale information</span>
                    <div class="card-header-right">
                        <ul class="list-unstyled card-option">
                            <li><i class="feather icon-maximize full-card"></i></li>
                            <li><i class="feather icon-minus minimize-card"></i>
                            </li>
                            <li><i class="feather icon-trash-2 close-card"></i></li>
                        </ul>
                    </div>
                </div>
                <div class="card-block">
                    <div id="visitor" style="height:300px"></div>
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
    <script type="text/javascript" src="{{ asset('themes/backend/date-range/daterangepicker.js') }}"></script>
    <script type="text/javascript" src="{{ asset('themes/backend/chart.js/dist/Chart.js"') }}"></script>
    <script src="{{ asset('themes/backend/amchart/amcharts.js') }}"></script>
    <script src="{{ asset('themes/backend/amchart/serial.js') }}"></script>
    <script src="{{ asset('themes/backend/amchart/light.js') }}"></script>
    <script>
        // Helper function to get query parameters from the URL
        function getQueryParam(param) {
            var urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        // Check if start_date and end_date are in the URL
        var urlStart = getQueryParam('start_date');
        var urlEnd = getQueryParam('end_date');

        // If dates exist in the URL, use them; otherwise, default to today's date
        var start = urlStart ? moment(urlStart, 'YYYY-MM-DD') : moment(); // Default start date is today
        var end = urlEnd ? moment(urlEnd, 'YYYY-MM-DD') : moment(); // Default end date is today

        // Function to update the date picker display
        function cb(start, end) {
            const startDate = moment(start); // Replace with your start date
            const endDate = moment(end); // Replace with your end date

            const spanLabel = getDateRangeLabel(startDate, endDate);
            $('#report-range span').html(spanLabel);
            //$('#report-range span').html(start.format('D MMM, YYYY') + ' - ' + end.format('D MMM, YYYY'));

            // Update hidden inputs with the selected dates
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        }

        // Initialize the date range picker with either URL dates or default today
        $('#report-range').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
            }
        }, cb);

        // Update the date picker display on page load with correct range
        cb(start, end);

        // Submit the form when the user clicks "Apply"
        $('#report-range').on('apply.daterangepicker', function(ev, picker) {
            // Submit the form after selecting a date range
            $('#dashboard_year_form').submit();
        });

        function getDateRangeLabel(start, end) {
            const today = moment().startOf('day');
            const yesterday = moment().subtract(1, 'day').startOf('day');
            const last7Days = moment().subtract(6, 'days').startOf('day');
            const last30Days = moment().subtract(29, 'days').startOf('day'); // Last 30 days calculation
            const startOfMonth = moment().startOf('month');
            const startOfLastMonth = moment().subtract(1, 'month').startOf('month');
            const endOfLastMonth = moment().subtract(1, 'month').endOf('month');

            // Today
            if (start.isSame(today, 'day') && end.isSame(today, 'day')) {
                return 'Today';
            }

            // Yesterday
            if (start.isSame(yesterday, 'day') && end.isSame(yesterday, 'day')) {
                return 'Yesterday';
            }

            // Last 7 Days
            if (start.isSame(last7Days, 'day') && end.isSame(today, 'day')) {
                return 'Last 7 Days';
            }

            // Last 30 Days
            if (start.isSame(last30Days, 'day') && end.isSame(today, 'day')) {
                return 'Last 30 Days';
            }

            // This Month
            if (start.isSameOrAfter(startOfMonth, 'day') && end.isSameOrBefore(moment().endOf('month'), 'day')) {
                return 'This Month';
            }

            // Last Month
            if (start.isSame(startOfLastMonth, 'day') && end.isSame(endOfLastMonth, 'day')) {
                return 'Last Month';
            }

            // Custom Range
            return start.format('D MMM, YY') + ' - ' + end.format('D MMM, YY');
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var salesDataChat1 = {!! json_encode($salesDataChat1) !!}; // Laravel sales data

            var dates = salesDataChat1.map(item => item.date); // Extract dates
            var totals = salesDataChat1.map(item => item.total_sales); // Extract total sales


            Highcharts.chart('sales-chart', {
                chart: {
                    type: 'line',
                    height: 400 // Ensure height matches the container
                },
                title: {
                    text: 'Sales Orders'
                },
                xAxis: {
                    categories: dates,
                    title: {
                        text: 'Date'
                    }
                },
                yAxis: {
                    title: {
                        text: 'Total Sales'
                    }
                },
                series: [{
                    name: 'Sales',
                    data: totals
                }]
            });
        });
    </script>
    <script>
        "use strict";
        $(document).ready(function() {
            var dataValues = {!! json_encode($salesDataChart2) !!};
            function e(e, t, a) {
                return null == a && (a = "rgba(0,0,0,0)"), {
                    labels: ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15"],
                    datasets: [{
                        label: "",
                        borderColor: e,
                        borderWidth: 2,
                        hitRadius: 30,
                        pointRadius: 3,
                        pointHoverRadius: 4,
                        pointBorderWidth: 5,
                        pointHoverBorderWidth: 12,
                        pointBackgroundColor: Chart.helpers.color("#000000").alpha(0).rgbString(),
                        pointBorderColor: e,
                        pointHoverBackgroundColor: e,
                        pointHoverBorderColor: Chart.helpers.color("#000000").alpha(.1).rgbString(),
                        fill: !0,
                        lineTension: 0,
                        backgroundColor: a,
                        data: t
                    }]
                }
            }

            function t() {
                return {
                    title: {
                        display: !1
                    },
                    tooltips: {
                        position: "nearest",
                        mode: "index",
                        intersect: !1,
                        yPadding: 10,
                        xPadding: 10
                    },
                    legend: {
                        display: !1,
                        labels: {
                            usePointStyle: !1
                        }
                    },
                    responsive: !0,
                    maintainAspectRatio: !0,
                    hover: {
                        mode: "index"
                    },
                    scales: {
                        xAxes: [{
                            display: !1,
                            gridLines: !1,
                            scaleLabel: {
                                display: !0,
                                labelString: "Month"
                            }
                        }],
                        yAxes: [{
                            display: !1,
                            gridLines: !1,
                            scaleLabel: {
                                display: !0,
                                labelString: "Value"
                            },
                            ticks: {
                                beginAtZero: !0
                            }
                        }]
                    },
                    elements: {
                        point: {
                            radius: 4,
                            borderWidth: 12
                        }
                    },
                    layout: {
                        padding: {
                            left: 10,
                            right: 10,
                            top: 25,
                            bottom: 25
                        }
                    }
                }
            }
            var a = (AmCharts.makeChart("visitor", {
                type: "serial",
                hideCredits: !0,
                theme: "light",
                dataDateFormat: "YYYY-MM-DD",
                precision: 2,
                valueAxes: [{
                    id: "v1",
                    title: "Weekly Sales",
                    position: "left",
                    autoGridCount: !1,
                    labelFunction: function(e) {
                        return "৳" + Math.round(e)
                    }
                }, {
                    id: "v2",
                    title: "New Visitors",
                    gridAlpha: 0,
                    position: "right",
                    autoGridCount: !1
                }],
                graphs: [{
                    id: "g3",
                    valueAxis: "v1",
                    lineColor: "#feb798",
                    fillColors: "#feb798",
                    fillAlphas: 1,
                    type: "column",
                    title: "Total Sale",
                    valueField: "t_amount",
                    clustered: !1,
                    columnWidth: .5,
                    legendValueText: "৳[[value]]",
                    balloonText: "[[title]]<br /><b style='font-size: 130%'>৳[[value]]</b>"
                }, {
                    id: "g4",
                    valueAxis: "v1",
                    lineColor: "#fe9365",
                    fillColors: "#fe9365",
                    fillAlphas: 1,
                    type: "column",
                    title: "Total Due",
                    valueField: "p_amount",
                    clustered: !1,
                    columnWidth: .3,
                    legendValueText: "৳[[value]]",
                    balloonText: "[[title]]<br /><b style='font-size: 130%'>৳[[value]]</b>"
                }],
                chartCursor: {
                    pan: !0,
                    valueLineEnabled: !0,
                    valueLineBalloonEnabled: !0,
                    cursorAlpha: 0,
                    valueLineAlpha: .2
                },
                categoryField: "date",
                categoryAxis: {
                    parseDates: !0,
                    dashLength: 1,
                    minorGridEnabled: !0
                },
                legend: {
                    useGraphSettings: !0,
                    position: "top"
                },
                balloon: {
                    borderThickness: 1,
                    cornerRadius: 5,
                    shadowAlpha: 0
                },
                dataProvider: dataValues
            }), AmCharts.makeChart("proj-earning", {
                type: "serial",
                hideCredits: !0,
                theme: "light",
                dataProvider: [{
                    type: "UI",
                    visits: 10
                }, {
                    type: "UX",
                    visits: 15
                }, {
                    type: "Web",
                    visits: 12
                }, {
                    type: "App",
                    visits: 16
                }, {
                    type: "SEO",
                    visits: 8
                }],
                valueAxes: [{
                    gridAlpha: .3,
                    gridColor: "#fff",
                    axisColor: "transparent",
                    color: "#fff",
                    dashLength: 0
                }],
                gridAboveGraphs: !0,
                startDuration: 1,
                graphs: [{
                    balloonText: "Active User: <b>[[value]]</b>",
                    fillAlphas: 1,
                    lineAlpha: 1,
                    lineColor: "#fff",
                    type: "column",
                    valueField: "visits",
                    columnWidth: .5
                }],
                chartCursor: {
                    categoryBalloonEnabled: !1,
                    cursorAlpha: 0,
                    zoomable: !1
                },
                categoryField: "type",
                categoryAxis: {
                    gridPosition: "start",
                    gridAlpha: 0,
                    axesAlpha: 0,
                    lineAlpha: 0,
                    fontSize: 12,
                    color: "#fff",
                    tickLength: 0
                },
                export: {
                    enabled: !1
                }
            }), document.getElementById("newuserchart").getContext("2d"));
            window.myDoughnut = new Chart(a, {
                type: "doughnut",
                data: {
                    datasets: [{
                        data: [{{ isset($daily_sale_info_query[0]) ? $daily_sale_info_query[0]->t_amount : 0 }},
                            {{ isset($daily_sale_info_query[0]) ? $daily_sale_info_query[0]->p_amount : 0 }},
                            {{ isset($daily_sale_info_query[0]) ? $daily_sale_info_query[0]->d_amount : 0 }}
                        ],
                        backgroundColor: ["#fe9365", "#01a9ac", "#fe5d70"],
                        label: "Dataset 1"
                    }],
                    labels: ["Total Sale", "Paid", "Due"]
                },
                options: {
                    maintainAspectRatio: !1,
                    responsive: !0,
                    legend: {
                        position: "bottom"
                    },
                    title: {
                        display: !0,
                        text: ""
                    },
                    animation: {
                        animateScale: !0,
                        animateRotate: !0
                    }
                }
            });
            var a = document.getElementById("sale-chart1").getContext("2d"),
                a = (new Chart(a, {
                    type: "line",
                    data: e("#b71c1c", [25, 30, 15, 20, 25, 30, 15, 25, 35, 30, 20, 10, 12, 1],
                        "transparent"),
                    options: t()
                }), document.getElementById("sale-chart2").getContext("2d")),
                a = (new Chart(a, {
                    type: "line",
                    data: e("#00692c", [30, 15, 25, 35, 30, 20, 25, 30, 15, 20, 25, 10, 12, 1],
                        "transparent"),
                    options: t()
                }), document.getElementById("sale-chart3").getContext("2d"));
            new Chart(a, {
                type: "line",
                data: e("#096567", [15, 20, 25, 10, 30, 15, 25, 35, 30, 20, 25, 30, 12, 1], "transparent"),
                options: t()
            })
        });
    </script>

@endsection
