<?php

use App\Models\AccountGroup;
use App\Models\AccountHead;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

function shortName($name){

    $firstName = Str::of($name)->before(' ');
    $lastName = Str::of($name)->after(' ');

    if ($lastName) {
        $shortName = strtoupper($firstName[0] . $lastName[0]);
    } else {
        $shortName = strtoupper($firstName[0]);
    }

    return $shortName;
}
function numberToMonthName($monthNumber){
    if ($monthNumber != '')
        return Carbon::create(null, $monthNumber, 1)->format('F');
    else
        return '';
}
function ordinalSuffix($number) {
    if ($number % 10 == 1 && $number % 100 != 11) {
        return 'st';
    } elseif ($number % 10 == 2 && $number % 100 != 12) {
        return 'nd';
    } elseif ($number % 10 == 3 && $number % 100 != 13) {
        return 'rd';
    } else {
        return 'th';
    }
}

function getFiscalYear($date) {

    $currentMonth = Carbon::parse($date)->format('m');
    if ($currentMonth < 7) {
        $firstYear = Carbon::parse($date)->format('Y') - 1;
        $secondYear = Carbon::parse($date)->format('Y');
        $fiscalYear = $firstYear.'-'.$secondYear;
    } else {
        $firstYear = Carbon::parse($date)->format('Y');
        $secondYear = $firstYear + 1;
        $fiscalYear = $firstYear.'-'.$secondYear;
    }


    return $fiscalYear;
}
function friendlyFieldName($fieldName) {

    $fieldName = preg_replace('/\.\d+/', '', $fieldName); // Remove dot and number
    $fieldName = str_replace('_', ' ', $fieldName); // Replace underscores with spaces
    return ($fieldName);
}
function accountGroupBalance($parentId,$startDate, $endDate,$type)
{
    if ($type == 1) {
        //Current Fiscal year
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
    } else {
        //Previous Fiscal year
        $start = date('d-m-Y', strtotime('-1 year', strtotime($startDate)));
        $end = date('d-m-Y', strtotime('-1 day', strtotime($startDate)));
    }

    // Get the parent AccountHeadType by ID
    $parentType = AccountGroup::find($parentId);
    // Get all child IDs recursively
    $childIds = $parentType->getAllChildIds();
    $childIds[] = $parentId;
    // Retrieve AccountHead records for the child types
    $accountHeadsIdQuery = AccountHead::whereIn('account_group_id', $childIds);

    $accountHeadsId = $accountHeadsIdQuery->select('id')
        ->pluck('id')
        ->toArray();

    $transactionLogQuery = Transaction::select(
        DB::raw('SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) as debitTotal'),
        DB::raw('SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) as creditTotal')
    );

    $transactionLogs = $transactionLogQuery->whereIn('account_head_id', $accountHeadsId)
        ->where('date','<=',$end)
        ->first();

    $debitOpening = 0;
    $creditOpening = 0;
        $accountHeadOpening = AccountHead::whereIn('id', $accountHeadsId)
            ->sum('opening_balance');

        if ($parentType->top_parent_id) {
            if ($parentType->top_parent_id == 3) {
                $debitOpening = $accountHeadOpening;
            } elseif ($parentType->top_parent_id == 4 || $parentType->top_parent_id == 5) {
                $creditOpening = $accountHeadOpening;
            }
        }

    return ($transactionLogs->debitTotal + $debitOpening) - ($transactionLogs->creditTotal + $creditOpening);
}
function accountGroupBalanceIncomeStatement($parentId,$startDate, $endDate,$type)
{
    if ($type == 1) {
        //Current Fiscal year
        $start = Carbon::parse($startDate)->format('Y-m-d');
        $end = Carbon::parse($endDate)->format('Y-m-d');
    } else {
        //Previous Fiscal year
        $start = date('d-m-Y', strtotime('-1 year', strtotime($startDate)));
        $end = date('d-m-Y', strtotime('-1 day', strtotime($startDate)));
    }

    // Get the parent AccountHeadType by ID
    $parentType = AccountGroup::find($parentId);
    // Get all child IDs recursively
    $childIds = $parentType->getAllChildIds();
    $childIds[] = $parentId;
    // Retrieve AccountHead records for the child types
    $accountHeadsIdQuery = AccountHead::whereIn('account_group_id', $childIds);
    $accountHeadsId = $accountHeadsIdQuery->select('id')
        ->pluck('id')
        ->toArray();

    $transactionLogQuery = Transaction::select(
        DB::raw('SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) as debitTotal'),
        DB::raw('SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) as creditTotal')
    );

    $transactionLogs = $transactionLogQuery->whereIn('account_head_id', $accountHeadsId)
        ->whereBetween('date',[$start,$end])
        ->first();

    return $transactionLogs->debitTotal - $transactionLogs->creditTotal;
}
function dropdownMenuContainer($elements){
    $html ='<div class="btn-group">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-ellipsis-v"></i></button>
            <div class="dropdown-menu" role="menu" style="">';
    $html .= $elements;
    $html .='</div></div>';
    return $html;
}

function getLocationName($latitude, $longitude)
{
    $cacheKey = "location_{$latitude}_{$longitude}";

    // Check cache first
    return Cache::remember($cacheKey, 3600, function () use ($latitude, $longitude) {
        $url = "https://nominatim.openstreetmap.org/reverse";
        $queryParams = [
            'format' => 'json',
            'lat' => $latitude,
            'lon' => $longitude,
            'addressdetails' => 1,
        ];

        try {
            // Make the API request with rate limiting
            usleep(1000000); // 1-second delay for rate limiting

            $response = Http::withHeaders([
                'User-Agent' => 'Restaurant ERP/1.0 (restaurant@gmail.com)'
            ])->get($url, $queryParams);

            if ($response->successful()) {
                $data = $response->json();
                $locationName = $data['display_name'] ?? 'Location not found';

                if (!$locationName && isset($data['address'])) {
                    $address = $data['address'];
                    $locationName = $address['city'] ??
                                    $address['town'] ??
                                    $address['village'] ??
                                    $address['county'] ??
                                    $address['state'] ??
                                    $address['country'] ??
                                    'Location not found';
                }

                return $locationName;
            }

            return 'Failed to fetch location';
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    });
}
