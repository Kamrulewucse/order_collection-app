@extends('layouts.app')
@section('title','Purchase Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('purchase_create'))
                    <a href="{{ route('purchase.create') }}" class="btn btn-primary bg-gradient-primary btn-sm">Create Purchase <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="company" class="col-form-label">Company <span
                                        class="text-danger">*</span></label>
                                <select name="company" id="company" class="form-control select2">
                                    <option value="">All Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="Enter End Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" name="search"
                                       class="btn btn-primary bg-gradient-primary form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Company</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="3" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th colspan="2"></th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-pay" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Supplier Payment</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-supplier-payment-form" method="POST" action="{{ route('purchase.supplier_payment') }}">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="order_no">Order No. <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="order_no" name="order_no" class="form-control">
                                    <span id="order_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="date" name="date" value="{{ date('d-m-Y') }}" class="form-control date-picker">
                                    <span id="date-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="payment_mode">Payment Mode <span class="text-danger">*</span></label>
                                    {{-- <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select Payment Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select> --}}
                                   <input type="text" readonly value="{{ $paymentMode->name }}" class="form-control">
                                    <input type="hidden" readonly name="payment_mode" value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}" class="form-control">
                                    <span id="payment_mode-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group form-group-has-error">
                                    <label for="cheque_no">Cheque No. <span class="text-danger">*</span></label>
                                    <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Enter Cheque No.">
                                    <span id="cheque_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="payment">Payment <span class="text-danger">*</span></label>
                                    <input type="number"  id="payment" class="form-control" name="payment">
                                    <span id="payment-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <input type="hidden" id="due_hidden" name="due_hidden">
                                    <label for="due">Due</label>
                                    <input type="text" readonly id="due" class="form-control" name="due">
                                    <span id="due-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="notes">Notes</label>
                                    <input type="text" id="notes" class="form-control" name="notes"
                                           placeholder="Enter Notes">
                                    <span id="notes-error" class="help-block error-message"></span>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="supplier-payment-btn" class="btn btn-primary">Save</button>
                </div>
            </div>

        </div>

    </div>

@endsection
@section('script')
    <script>
        $(function () {
            calculate();
            $('body').on('click', '.supplier-pay', function () {
                let orderId = $(this).data('id');
                $("#order_no").val($(this).data('order_no'));
                $("#total").val($(this).data('total'));
                $("#due").val($(this).data('due'));
                $("#due_hidden").val($(this).data('due'));
                $("#order_id").val(orderId);
                $('#modal-pay').modal('show');
            })
            $('#payment_mode').change(function (){
                let paymentMode = $(this).val();
                var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                var id = valuesArray[0];
                var mode = valuesArray[1];
                if (paymentMode != '' && mode == 1){
                    $("#if_payment_bank_mode").show();
                }else{
                    $("#if_payment_bank_mode").hide();
                }
            })
            $('#supplier-payment-btn').click(function() {
                preloaderToggle(true);
                // Create a FormData object
                var formData = new FormData(document.getElementById('modal-supplier-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-supplier-payment-form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        preloaderToggle(false);
                        if (response.status){
                            ajaxSuccessMessage(response.message)
                            window.location.href = response.redirect_url;
                        }else{
                            console.log(response.message);
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: response.message
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                        }
                    },
                    error: function(xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: 'Please fill up validate required fields.'
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text('');
                            $('.form-group-has-error').removeClass('has-error');

                            // Update error messages for each field
                            $.each(errors, function(field, errorMessage) {
                                $('#'+field+'-error').text(errorMessage[0]);
                                $('#'+field+'-error').closest('.form-group-has-error').addClass('has-error')
                            });
                        }
                    }
                });
            });
            $('body').on('keyup', '#payment', function() {
                calculate();
            });

            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('purchase.datatable') }}",
                    data: function (d) {
                        d.company = $("#company").val()
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {data: 'supplier_name', name: 'supplier.name'},
                    {
                        data: 'total',
                        name: 'total',
                        render: function (data, type, row) {
                            if (row.total > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getJvVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.total).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.total).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {
                        data: 'paid',
                        name: 'paid',
                        render: function (data, type, row) {
                            if (row.paid > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.paid).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.paid).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {data: 'due', name: 'due',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[1, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to sum the column values
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over this page for each column
                    var pageTotalTotal = api.column(3, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    var pageTotalPaid = api.column(4, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    var pageTotalDue = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                    $(api.column(4).footer()).html(jsNumberFormat(pageTotalPaid.toFixed(2)));
                    $(api.column(5).footer()).html(jsNumberFormat(pageTotalDue.toFixed(2)));
                }
            });
            $('#start_date,#end_date,#search_btn,#company').change(function () {
                table.ajax.reload();
                let start_date = $("#start_date").val();
                let end_date = $("#end_date").val();
            });
        });
        function calculate() {

            let total = parseFloat($('#total').val());
            total = (isNaN(total) || total < 0) ? 0 : total;

            let due_hidden = parseFloat($('#due_hidden').val());
            due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

            let payment = parseFloat($('#payment').val());
            payment = (isNaN(payment) || payment < 0) ? 0 : payment;

            $("#due").val((due_hidden - payment).toFixed(2));
        }
        function getVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$PAYMENT_VOUCHER,'purchase_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
        function getJvVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'purchase_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
    </script>
@endsection
