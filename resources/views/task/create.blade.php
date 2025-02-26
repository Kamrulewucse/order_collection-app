@extends('layouts.app')
@section('title', 'Task')
@section('style')
    <style>
        .table td,
        .table th {
            padding: 6px;
            vertical-align: middle;
        }
    </style>
@endsection
@section('content')

    <!-- form start -->
    <form enctype="multipart/form-data" action="{{ route('task.store') }}"
        class="form-horizontal" method="post">
        @csrf
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Task Information </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('task_user_name') ? 'has-error' : '' }}">
                                    <label for="task_user_name">Task User <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="task_user_name"
                                        value="{{ auth()->user()->name ?? '' }}" readonly>
                                    <input type="hidden" value="{{ auth()->user()->id }}" name="task_user">
                                    @error('task_user')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' : '' }}">
                                    <label for="task_user">Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" name="date"
                                        value="{{ date('Y-m-d') }}" readonly>
                                    @error('date')
                                        <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="add_task_details">Task Details<span
                                            class="text-danger">*</span></label>
                                    <textarea type="text" class="form-control" id="add_task_details"
                                        placeholder="Task Details"></textarea>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_task_cost">Task Cost <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="add_task_cost" placeholder="Enter cost">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_task_priority">Task Priority <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="add_task_priority">
                                        <option value="">Select Priority</option>
                                        <option value="High">High</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_task_date">Task Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('add_task_date',date('d-m-Y')) }}" id="add_task_date" class="form-control date-picker" placeholder="Enter date">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn"
                                        class="btn btn-primary bg-gradient-primary btn-sm btn-block"><i
                                            class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="5%">S/L</th>
                                            <th class="text-center">Task Details</th>
                                            <th class="text-center">Task Cost</th>
                                            <th class="text-center">Task Priority</th>
                                            <th class="text-center">Task Date</th>
                                            <th class="text-center" width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="sr-doctor-name-container">
                                        @if (old('sr_doctor_id') != null && sizeof(old('sr_doctor_id')) > 0)
                                            @foreach (old('sr_doctor_id') as $key => $item)
                                                <tr class="sr-doctor-name-item">
                                                    <td class="text-center">
                                                        <span class="sr-doctor-sl">{{ ++$key }}</span>
                                                    </td>
                                                    <td class="text-left">
                                                        <div class="form-group mb-0  {{ $errors->has('task_details.' . $loop->index) ? 'has-error' : '' }}">
                                                            <textarea type="texty" value="{{ old('task_details.' . $loop->index) }}" class="form-control text-left task_details" name="task_details[]"></textarea>
                                                        </div>
                                                    </td>
                                                    <td class="text-left">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="0.01" class="form-control task_cost" name="task_cost[]" placeholder="Enter cost">
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('task_priority.' . $loop->index) ? 'has-error' : '' }}">
                                                            <select class="form-control select2 task_priority" name="task_priority[]">
                                                                <option value="High" {{ old('task_priority.' . $loop->index) == 'High' ? 'selected' : '' }}>High</option>
                                                                <option value="Medium" {{ old('task_priority.' . $loop->index) == 'Medium' ? 'selected' : '' }}>Medium</option>
                                                                <option value="Low" {{ old('task_priority.' . $loop->index) == 'Low' ? 'selected' : '' }}>Low</option>
                                                            </select>
                                                        </div>
                                                    </td>
                                                    <td class="text-left">
                                                        <div class="form-group mb-0">
                                                            <input type="text" autocomplete="off" value="{{ old('add_task_date',date('d-m-Y')) }}" name="task_date[]" class="form-control date-picker task_date" placeholder="Enter date">
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total</th>
                                            <th id="total_cost"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                                {{-- <div class="row">
                                    <div class="col-md-12">
                                        <div class="card-footer">
                                            <strong>Total Cost: </strong><span id="total_cost">0.00</span>
                                        </div>
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary bg-gradient-primary btn-sm">Save</button>
                        <a href="{{ route('sales-order.index') }}"
                            class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.card -->
            </div>
            <!--/.col (left) -->
        </div>
    </form>
    <template id="sr_doctor-template">
        <tr class="sr-doctor-name-item">
            <td class="text-center">
                <span class="sr-doctor-sl"></span>
            </td>
            <td class="text-left">
                <div class="form-group mb-0">
                    <textarea type="text" class="form-control text-left task_details" name="task_details[]"> </textarea>
                </div>
            </td>
            <td class="text-left">
                <div class="form-group mb-0">
                    <input type="number" step="0.01" class="form-control task_cost" name="task_cost[]" placeholder="Enter cost">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <select class="form-control select2 task_priority" name="task_priority[]">
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
            </td>
            <td class="text-left">
                <div class="form-group mb-0">
                    <input type="text" autocomplete="off" value="{{ old('add_task_date',date('d-m-Y')) }}" name="task_date[]" class="form-control date-picker task_date" placeholder="Enter date">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

@endsection
@section('script')
    <script>
        $(function() {
            function initializeDatePicker() {
                $('.date-picker').datepicker({
                    dateFormat: 'dd-mm-yy',
                    changeMonth: true,
                    changeYear: true
                });
            }
            $(document).ready(function() {
                initializeDatePicker();
            });
            var srDoctorIds = [];

            $(".sr_doctor_id").each(function(index) {
                if ($(this).val() != '') {
                    srDoctorIds.push($(this).val());
                }
            });

            $('body').on('keypress','#add_task_priority', function(e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });


            $('body').on('click', '#add_new_btn', function(e) {
                let addTaskDetails = $('#add_task_details').val();
                let addTaskCost = $('#add_task_cost').val();
                let addTaskPriority = $('#add_task_priority').val();
                let addTaskDate = $('#add_task_date').val();


                if (addTaskDetails == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, type task details!' });
                    return false;
                }
                if (addTaskCost == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, enter Task Cost!' });
                    return false;
                }
                if (addTaskPriority == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, select Task Priority!' });
                    return false;
                }
                if (addTaskDate == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, select Task Date!' });
                    return false;
                }

                if (addTaskPriority && addTaskDetails && addTaskDate && addTaskCost) {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#sr_doctor-template').html();
                    var itemHtml = $(html);
                    $('#sr-doctor-name-container').prepend(itemHtml);
                    var item = $('.sr-doctor-name-item').first();
                    item.hide();
                    item.find('.task_details').val(addTaskDetails);
                    item.find('.task_cost').val(addTaskCost);
                    item.find('.task_priority').val(addTaskPriority);
                    item.find('.task_date').val(addTaskDate);
                    item.show();

                    initializeDatePicker();
                    calculate();
                    $('#add_task_details').val('');
                    $('#add_task_priority').val(null).trigger('change');
                    $('#add_task_cost').val('');

                    let today = new Date();
                    let day = String(today.getDate()).padStart(2, '0');
                    let month = String(today.getMonth() + 1).padStart(2, '0');
                    let year = today.getFullYear();
                    let formattedDate = day + '-' + month + '-' + year;

                    $('#add_task_date').val(formattedDate);
                }

                return false;
            });


            $('body').on('click', '.btn-remove', function() {
                var sr_doctor_id = $(this).closest('tr').find('.sr_doctor_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.sr-doctor-name-item').remove();
                calculate();
            });
            $('body').on('keyup', 'input[type="number"]', function() {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function() {
                calculate();
            });
        })

        function calculate() {
            var totalCost = 0;
            var srDoctorSl = 1;

            var rows = $("#sr-doctor-name-container .sr-doctor-name-item");
            rows.each(function() {
                var srDoctorSlElement = $(this).find('.sr-doctor-sl');
                srDoctorSlElement.text(srDoctorSl);
                srDoctorSl++;

                var taskCost = parseFloat($(this).find('.task_cost').val()) || 0;
                totalCost += taskCost;
            });

            $('#total_cost').text(totalCost.toFixed(2));

            if (rows.length > 0) {
                $("#footer_area").show();
            } else {
                $("#footer_area").hide();
            }
        }

$('body').on('keyup', '.task_cost', function() {
    calculate();
});

$('body').on('change', '.task_cost', function() {
    calculate();
});
    </script>
@endsection
