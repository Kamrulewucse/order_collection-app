<?php

namespace App\Http\Controllers;

use App\Enumeration\VoucherType;
use App\Models\AccountHead;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\DistributionOrderItem;
use App\Models\Floor;
use App\Models\Guest;
use App\Models\GuestExpenseLog;
use App\Models\Inventory;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\Room;
use App\Models\SaleOrder;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getGuestExpenseLog(Request $request)
    {
        $expenseLog = GuestExpenseLog::with('paymentAccountHead')->find($request->id);
        $expenseLog->date = Carbon::parse($expenseLog->date)->format('d-m-Y');
        return response()->json($expenseLog);

    }
    public function getCollectionAmount(Request $request)
    {
        if ($request->start_date != '' && $request->end_date != ''){
            $totalCollection = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->whereBetween('date',[
                    Carbon::parse($request->start_date)->format('Y-m-d'),
                    Carbon::parse($request->end_date)->format('Y-m-d')
                ])

                ->sum('amount');
            $totalSettled = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->whereBetween('date',[Carbon::parse($request->start_date)->format('Y-m-d'),Carbon::parse($request->end_date)->format('Y-m-d')])
                ->where('collection_receive_status',1)
                ->sum('amount');
            $totalBankDeposit = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->whereBetween('date',[Carbon::parse($request->start_date)->format('Y-m-d'),Carbon::parse($request->end_date)->format('Y-m-d')])
                ->where('collection_receive_status',2)
                ->sum('amount');

            $paymentModes = AccountHead::where('payment_mode','>',0)
                ->where('id','!=',101)
                ->orderByRaw("CASE WHEN id IN (1,5) THEN 0 ELSE 1 END, id ASC")
                ->get();
                $paymentModeCollections = [];
                foreach ($paymentModes as $paymentMode){
                    $paymentModeCollectionAmount = Voucher::where('voucher_type',VoucherType::$COLLECTION_VOUCHER)
                        //->whereNotNull('booking_id')
                        ->whereBetween('date',[
                            Carbon::parse($request->start_date)->format('Y-m-d'),
                            Carbon::parse($request->end_date)->format('Y-m-d')
                        ])
                        ->where('payment_account_head_id',$paymentMode->id)
                        ->sum('amount');
                    array_push($paymentModeCollections,[
                        'id'=>$paymentMode->id,
                        'amount'=>number_format($paymentModeCollectionAmount,2),
                    ]);
                }



            return response()->json([
                'status'=>true,
                'total_collection'=>number_format($totalCollection,2),
                'total_settled'=>number_format($totalSettled,2),
                'total_bank_deposit'=>number_format($totalBankDeposit,2),
                'payment_mode_collections'=>$paymentModeCollections,
            ]);
        }
        return response()->json([
            'status'=>false,
        ]);

    }
    public function getStockInfo(Request $request)
    {
        $inventory = Inventory::with('product')
                    ->where('product_id',$request->product_id)
                    ->first();
        $product = Product::with('unit')
                ->where('id',$request->product_id)
                ->first();

        return response()->json([
            'inventory'=>$inventory,
            'product'=>$product,
        ]);
    }
    public function getSalesOrders(Request $request)
    {
        $salesOrders = SaleOrder::with('customer')
                    ->where('due','>',0)
                    ->where('distribution_order_id',$request->orderId)
                    ->get();

        return response()->json($salesOrders);
    }
    public function getSalesOrdersCustomer(Request $request)
    {
        $salesOrders = SaleOrder::whereHas('distributionOrder', function ($query) {
                        $query->where('close_status', 1);
                    })->where('due','>',0)
                    ->where('customer_id',$request->customerId)
                    ->with('distributionOrder','customer')
                    ->get();

        return response()->json($salesOrders);
    }
    public function getSalesOrderDetails(Request $request)
    {
        $salesOrder = SaleOrder::where('id',$request->orderId)
                    ->first();

        return response()->json($salesOrder);
    }
    public function getDistributionProductInfo(Request $request)
    {
      $distributionOrderItem = DistributionOrderItem::where('product_id',$request->product_id)
          ->where('distribution_order_id',$request->distribution_order_id)
           ->first();
        $product = Product::with('unit')
                ->where('id',$request->product_id)
                ->first();

        return response()->json([
            'distribution_order_item'=>$distributionOrderItem,
            'product'=>$product,
        ]);
    }
    public function getCheckedInGroupInformation(Request $request)
    {

        $existingGroups = Booking::where('booking_type',2)//Check In
            ->where('status',0)//Checked In
            ->whereNotNull('group_name')
            ->orderBy('group_name')
            ->select('group_name')
            ->groupBy('group_name')
            ->get();

        $query  = Room::where('hotel_id',1)
            ->where('room_status',2);//Checked In
        if ($request->groupType == 1){
            $query->whereHas('blankGroupCheckedIn'); // Ensure the relationship exists
        }
        $checkedInRooms = $query->orderBy('sort')
                ->get();
       // dd($checkedInRooms);

        return response()->json([
            'groups'=>$existingGroups,
            'rooms'=>$checkedInRooms,
        ]);
    }
    public function getGuestBillOutstanding(Request $request)
    {
        $bookings = Booking::where('booking_type',2)->whereNull('checkout_at')->get();

        $html = view('check_in.partial.guest_bill_outstanding_results_ajax',
            compact('bookings'))->render();
        return response()->json($html);
    }
    public function getGuestCheckInRents(Request $request)
    {
        $bookings = Booking::where('booking_type',2)
            ->whereNull('checkout_at')
            ->get();

        $html = view('check_in.partial.guest_check_in_rents_results_ajax',
            compact('bookings'))->render();
        return response()->json($html);
    }
    public function getCheckedInGroupRoomsInformation(Request $request)
    {

        $existingRoomsId = Booking::where('booking_type',2)//Check In
            ->where('status',0)//Checked In
            ->where('group_name',$request->existing_group_name)
            ->pluck('room_id');


        return response()->json([
            'rooms_id'=>$existingRoomsId,
        ]);
    }
    public function getBookingCheckin(Request $request)
    {
        $booking = Booking::select('id','advance')->where('id',$request->id)->first();

        return response()->json($booking);
    }
    public function getBookingRooms(Request $request)
    {

        $booking = Booking::with(['guest', 'bookingDetails' => function ($query) {
            $query->select('room_id','booking_id') // Include other necessary columns
            ->groupBy('room_id','booking_id');
        }])->with('createdBy')
            ->where('id',$request->bookingId) // Ensure the correct variable or ID is used here
            ->first();

          $html = view('booking.partial.booking_details',compact('booking'))->render();
        return response()->json($html);
    }
    public function getAccountHead(Request $request)
    {
        $accountHead = AccountHead::where('id',$request->id)->first();
        return response()->json($accountHead);
    }
    public function getProductDetails(Request $request)
    {
        $product = Product::where('id',$request->id)->first();
        return response()->json($product);
    }

    public function getFloor(Request $request)
    {
        $floors = Floor::where('status',1)->where('hotel_id',$request->hotelId)->get();
        return response()->json($floors);
    }
    public function guestsSearch(Request $request)
    {
        $query = $request->input('query');

        $guests = Guest::where('name', 'LIKE', "%$query%")
            ->orWhere('mobile_no', 'LIKE', "%$query%")
            ->get();
        $guestListHtml = view('check_in.partial.guest_search_results_ajax',compact('guests'))->render();

        return response()->json([
            'guest_count'=>count($guests),
            'html'=>$guestListHtml,
        ]);
    }

    public function roomSearch(Request $request)
    {
        $keyword = $request->input('keyword');

        $query = Room::with('roomCategory');

        if ($keyword != '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('room_no', 'LIKE', "%$keyword%")
                    ->orWhere('person_no', 'LIKE', "%$keyword%")
                    ->orWhere('bed_no', 'LIKE', "%$keyword%")
                    ->orWhere('rent', 'LIKE', "%$keyword%");
            });

            $query->orWhereHas('roomCategory', function ($q) use ($keyword) {
                $q->where('name', 'LIKE', "%$keyword%");
            });
        }

        $rooms = $query->where('status', 1)
            ->orderBy('sort')
            ->get();



        $roomListHtml = view('check_in.partial.room_search_results_ajax',compact('rooms'))->render();

        return response()->json([
            'html'=>$roomListHtml,
           ]);

    }



    public function getAvailableRooms(Request $request)
    {

        $otherBookedRoomId = \App\Models\BookingDetail::
                 where('status',0)
                ->where('start_date',$request->bookingDate)
                ->first()->room_id ?? '';

        $bookingRooms = \App\Models\Room::orderBy('sort')
            ->where('id','!=',$otherBookedRoomId)
            ->get();

        return response()->json($bookingRooms);
    }
    public function getRoomRent(Request $request)
    {
        $room = Room::where('id',$request->roomId)->first();
        return response()->json($room);
    }
    public function getRoomsListHtml(Request $request)
    {
        $selectedRoomsId = $request->roomsId;
       if (!$selectedRoomsId){
           $selectedRoomsId = [];
       }
       $startDate = Carbon::parse($request->startDate)->format('Y-m-d');
       $endDate = Carbon::parse($request->endDate)->format('Y-m-d');

        $rooms = Room::where('status',1)->where('floor_id',$request->floorId)
            ->where('condition',$request->condition)
            ->with('floor')
            ->get();
        $html = view('booking.partial.__room_list_html',compact('rooms',
            'selectedRoomsId','startDate','endDate'))->render();
        return response()->json($html);
    }
}
