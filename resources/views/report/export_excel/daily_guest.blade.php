<div class="row">
    <div class="col-12">
        <p class="custom-p-tag">To</p>
        <p class="custom-p-tag">Rajshahi Metropolitan Police (RMP)</p>
        <p class="custom-p-tag">Rajshahi</p>
        <p class="custom-p-tag pb-4">Sub: Reporting of Daily Guests</p>
        <p class="custom-p-tag">Sir,</p>
        <p class="custom-p-tag pb-4">With due regards, I would like to notify that, the particulars of boarders at Hotel Mukta International on the date of {{ date('d-m-Y') }} are listed hereunder for your kind attention.</p>
    </div>
</div>

@if($guestType == 1)
    <table class="table table-bordered">
        <thead>
        <tr>
            <th  class="text-center">S/L</th>
            <th  class="text-center">Name</th>
            <th  class="text-center">Age</th>
            <th  class="text-center">Occupation</th>
            <th  class="text-center">Address</th>
            <th  class="text-center">Room</th>
            <th  class="text-center">Mobile</th>


        </tr>
        </thead>
        <tbody>
        @foreach($checkedIns as $checkedIn)
            @php
                $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                            ->where('id','!=',$checkedIn->guest_id)
                            ->get();
            @endphp
            <tr>
                <td  class="text-center">{{ $loop->iteration }}</td>
                <td  class="text-left">
                    {{ $checkedIn->guest_name }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->name }} <br>
                    @endforeach
                </td>
                <td  class="text-center">
                    {{ $checkedIn->guest->age ?? '' }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->age }} <br>
                    @endforeach

                </td>
                <td ></td>
                <td  class="text-center">
                    {{ $checkedIn->address }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->current_address }} <br>
                    @endforeach
                </td>
                <td  class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                <td  class="text-center">
                    {{ $checkedIn->mobile_no }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->mobile_no }} <br>
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@elseif($guestType == 2)
    <table class="table table-bordered">
        <thead>
        <tr>
            <th  class="text-center">S/L</th>
            <th  class="text-center">Guest Name</th>
            <th  class="text-center">Check-In Date</th>
            <th  class="text-center">Check-In Time</th>
            <th  class="text-center">Passport No.</th>
            <th  class="text-center">Nationality</th>
            <th  class="text-center">Room No.</th>
            <th  class="text-center">Purpose of Visit</th>
            <th  class="text-center">Visa Ex. Date</th>
        </tr>
        </thead>
        <tbody>
        @foreach($checkedIns as $checkedIn)
            @php
                $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                ->where('id','!=',$checkedIn->guest_id)
                ->get();
            @endphp
            <tr>
                <td  class="text-center">{{ $loop->iteration }}</td>
                <td  class="text-left">
                    {{ $checkedIn->guest_name }}{!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->name }} <br>
                    @endforeach
                </td>
                <td  class="text-center">{{ $checkedIn->created_at->format('d-m-Y') }}</td>
                <td  class="text-center">{{ $checkedIn->created_at->format('h:i a') }}</td>
                <td  class="text-center">
                    {{ $checkedIn->guest_passport_no }}{!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->passport_no }} <br>
                    @endforeach
                </td>
                <td  class="text-center"></td>
                <td  class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                <td  class="text-center"></td>
                <td  class="text-center"></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@elseif($guestType == 3)
    <table class="table table-bordered">
        <thead>
        <tr>
            <th  class="text-center">S/L</th>
            <th  class="text-center">Name</th>
            <th  class="text-center">Age</th>
            <th  class="text-center">Occupation</th>
            <th  class="text-center">Address</th>
            <th  class="text-center">Room</th>
            <th  class="text-center">Mobile</th>


        </tr>
        </thead>
        <tbody>
        @foreach($checkedIns as $checkedIn)
            @php
                $otherGuests = \App\Models\Guest::whereIn('id',json_decode($checkedIn->guests_id))
                            ->where('id','!=',$checkedIn->guest_id)
                            ->get();
            @endphp
            <tr>
                <td  class="text-center">{{ $loop->iteration }}</td>
                <td  class="text-left">
                    {{ $checkedIn->guest_name }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->name }} <br>
                    @endforeach
                </td>
                <td  class="text-center">
                    {{ $checkedIn->guest->age ?? '' }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->age }} <br>
                    @endforeach

                </td>
                <td ></td>
                <td  class="text-center">
                    {{ $checkedIn->address }} {!! count($otherGuests->whereNull('guest_passport_no')) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests->whereNull('guest_passport_no') as $otherGuest)
                        {{ $otherGuest->current_address }} <br>
                    @endforeach
                    @if($checkedIn->guest_passport_no != '')
                        Passport No. {{ $checkedIn->guest_passport_no }}
                    @endif
                    {!! count($otherGuests->whereNotNull('guest_passport_no')) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests->whereNotNull('guest_passport_no') as $otherGuest)
                        Passport No. {{ $otherGuest->passport_no }} <br>
                    @endforeach
                </td>
                <td  class="text-center">{{ $checkedIn->room->room_no ?? '' }}</td>
                <td  class="text-center">
                    {{ $checkedIn->mobile_no }} {!! count($otherGuests) > 0 ? '<br>' : ''  !!}
                    @foreach($otherGuests as $otherGuest)
                        {{ $otherGuest->mobile_no }} <br>
                    @endforeach
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
