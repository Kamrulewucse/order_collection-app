@extends('layouts.app')
@section('title', 'Customer Payment')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Customer Payment Lists</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="client" class="col-form-label">Client <span
                                        class="text-danger">*</span></label>
                                <select name="client" id="client" class="form-control select2">
                                    <option value="">All Client</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}">{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="sr" class="col-form-label">SR <span class="text-danger">*</span></label>
                                <select name="sr" id="sr" class="form-control select2">
                                    <option value="">All SR</option>
                                    @foreach ($srs as $sr)
                                        <option value="{{ $sr->id }}">{{ $sr->name }}</option>
                                    @endforeach
                                </select>
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
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Customer Name</th>
                                    <th class="text-center">SR Name</th>
                                    <th class="text-center">Address</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Paid</th>
                                    <th class="text-center">Due</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-center">Total</th>
                                    <th class="text-right"></th>
                                    <th class="text-right"></th>
                                    <th class="text-right"></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    <div class="modal fade" id="modal-pay" data-backdrop="static" data-keyboard="false" tabindex="-1"
        aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customer Payment Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-sr-payment-form" method="POST" action="{{ route('saleOrder.sr_payment') }}">
                        @csrf
                        <input type="hidden" id="client_id" name="client_id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group form-group-has-error">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="date" name="date" value="{{ date('d-m-Y') }}"
                                        class="form-control date-picker">
                                    <span id="date-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group form-group-has-error">
                                    <label for="sales_order">Dsr Sales Order <span class="text-danger">*</span></label>
                                    <select name="sales_order" id="sales_order" class="form-control select2">
                                        <option value="">Select Dsr Sales Order</option>
                                    </select>
                                    <span id="sales_order-error" class="help-block error-message"></span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">

                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="payment">Receive Amount <span class="text-danger">*</span></label>
                                    <input type="number" id="payment" class="form-control" name="payment">
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
                    <button type="submit" id="sr-payment-btn" class="btn btn-primary">Save</button>
                </div>
            </div>

        </div>

    </div>
@endsection
@section('script')
    <script>
        $(function() {
            calculate();

            $('body').on('click', '.customer-pay', function() {
                let clientId = $(this).data('id');
                // alert(clientId);
                $("#client_id").val(clientId);
                $('#sales_order').html('<option value="">Select Sale Order</option>');
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_sales_orders_client') }}",
                    data: {
                        clientId: clientId
                    }
                }).done(function(data) {
                    $.each(data, function(index, item) {
                        let srName = item.sr ? item.sr.name : 'N/A';
                        $('#sales_order').append('<option value="' + item.id + '">' + item
                            .order_no + '( SR: ' + srName + ')' + '</option>');
                    });
                    $('#modal-pay').modal('show');
                });
            })
            $('body').on('change', '#sales_order', function() {
                let orderId = $(this).val();
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_sales_order_details') }}",
                    data: {
                        orderId: orderId
                    }
                }).done(function(data) {
                    $("#total").val(data.total);
                    $("#due").val(data.due);
                    $("#due_hidden").val(data.due);
                });
            })
            $('#payment_mode').change(function() {
                let paymentMode = $(this).val();
                var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                var id = valuesArray[0];
                var mode = valuesArray[1];
                if (paymentMode != '' && mode == 1) {
                    $("#if_payment_bank_mode").show();
                } else {
                    $("#if_payment_bank_mode").hide();
                }
            })
            $('#sr-payment-btn').click(function() {
                preloaderToggle(true);
                // Create a FormData object
                var formData = new FormData(document.getElementById('modal-sr-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-sr-payment-form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        preloaderToggle(false);
                        if (response.status) {
                            ajaxSuccessMessage(response.message)
                            window.location.href = response.redirect_url;
                        } else {
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: response.message
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById(
                                'notification-error-audio');
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
                            let notificationSound = document.getElementById(
                                'notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text('');
                            $('.form-group-has-error').removeClass('has-error');

                            // Update error messages for each field
                            $.each(errors, function(field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                                $('#' + field + '-error').closest(
                                    '.form-group-has-error').addClass('has-error')
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
                    url: "{{ route('client-payments.datatable') }}",
                    data: function(d) {
                        d.client = $("#client").val()
                        d.sr = $("#sr").val()
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [
                    [10, 25, 50, 100, -1],
                    [10, 25, 100, 50, "All"]
                ],
                columns: [
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'sr_name',
                        name: 'sr.name'
                    },
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false
                    },
                    {
                        data: 'total',
                        name: 'total',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'paid',
                        name: 'paid',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'due',
                        name: 'due',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },
                        className: 'text-right'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        className: 'text-center'
                    },
                ],
                order: [
                    [0, 'desc']
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true,
                "autoWidth": false,
                "colReorder": true,
                footerCallback: function(row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to sum the column values
                    var intVal = function(i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                            i : 0;
                    };

                    // Total over this page for each column
                    var pageTotalTotal = api.column(3, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    var pageTotalPaid = api.column(4, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    var pageTotalDue = api.column(5, {
                        page: 'current'
                    }).data().reduce(function(a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footer
                    $(api.column(3).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                    $(api.column(4).footer()).html(jsNumberFormat(pageTotalPaid.toFixed(2)));
                    $(api.column(5).footer()).html(jsNumberFormat(pageTotalDue.toFixed(2)));
                }
            });

            $('#start_date,#end_date,#search_btn,#client,#sr').change(function() {
                table.ajax.reload();
            });

        });

        function calculate() {

            let total = parseFloat($('#total').val());
            total = (isNaN(total) || total < 0) ? 0 : total;

            let due_hidden = parseFloat($('#due_hidden').val());
            due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

            let payment = parseFloat($('#payment').val());
            payment = (isNaN(payment) || payment < 0) ? 0 : payment;

            $("#due").val(Math.ceil(due_hidden - payment).toFixed(2));
        }
    </script>
@endsection
