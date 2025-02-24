<?php

namespace App\Http\Controllers;

use App\Events\LocationUpdate;
use App\Models\Client;
use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function liveLocation(){
        $users = User::whereNotIn('role',['doctor'])->take(10)->get();
        return view('location.live_location',compact('users'));
    }
    public function locationHistory(){
        $users = User::whereNotIn('role',['doctor'])->take(10)->get();
        return view('location.location_history',compact('users'));
    }
    // public function updateLocationGet(){
    //     try {
    //         $payload = [
    //             'user_id' => 1,
    //             'lat' => 23.761900,
    //             'lng' => 90.433100,
    //             'timestamp' => now()->toDateTimeString()
    //         ];

    //         // $location = Location::create([
    //         //     'user_id' => auth()->user()->id,
    //         //     'latitude' => 23.761900,
    //         //     'longitude' => 90.433100,
    //         // ]);
    //         $location = [
    //             'user_id' => auth()->user()->id,
    //             'latitude' => 23.761900,
    //             'longitude' => 90.433100,
    //         ];
    //         event(new LocationUpdate($location));
    //         // dd(event(new LocationUpdate($location)));
        
    //         return response()->json(['status' => 'success']);

    //     } catch (\Exception $e) {
    //         // dd($e->getMessage());
    //         \Log::error('Publish failed', ['error' => $e->getMessage()]);
    //         return response()->json(['status' => 'error'], 500);
    //     }
    // }
    public function updateLocation(Request $request)
    {
        try {
            $user = auth()->user();

            if($user->role != 'Doctor'){
                $newLatitude = $request->latitude;
                $newLongitude = $request->longitude;
    
                // Only update database if location changes
                if ($user->latest_latitude != $newLatitude || $user->latest_longitude != $newLongitude) {
                    
                    // Bulk update user data in one query
                    $user->update([
                        'is_online' => ($user->is_online == 0) ? 1 : $user->is_online,
                        'latest_latitude' => $newLatitude,
                        'latest_longitude' => $newLongitude,
                    ]);
    
                    $currentDate = now()->format('Y-m-d');
                    // $newPoint = [$newLatitude, $newLongitude];
                    $newPoint = [
                        'latitude' => $newLatitude,      
                        'longitude' => $newLongitude,    
                        'order_status' => 0,      
                        'order_no' => ''  
                    ];
    
                    $location = Location::where('user_id', $user->id)
                    ->where('date', $currentDate)
                    ->first();
    
                    $existingHistory = $location ? json_decode($location->history, true) : [];
                    $existingHistory[] = $newPoint;
    
                    Location::updateOrCreate(
                        ['user_id' => $user->id, 'date' => $currentDate],
                        ['history' => json_encode($existingHistory)]
                    );
    
                    // Fire location update event
                    event(new LocationUpdate([
                        'user_id' => $user->id,
                        'latitude' => $newLatitude,
                        'longitude' => $newLongitude,
                    ]));
                }
    
                return response()->json(['status' => 'Location updated successfully']);
            }

        } catch (\Exception $e) {
            \Log::error('Publish failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => $e->getMessage()], 500);
        }
    }
    // public function updateLocation(Request $request)
    // {
    //     try {
    //         $user = auth()->user();
    //         if($user->is_online == 0){
    //             $user->is_online = 1;
    //             $user->save();
    //         }
            
    //         if($user->latest_latitude != $request->latitude || $user->latest_longitude != $request->longitude){
                
    //             $user->latest_latitude = $request->latitude;
    //             $user->latest_longitude = $request->longitude;
    //             $user->save();

    //             $userId = Auth::id();
    //             $currentDate = now()->format('Y-m-d');

    //             $newPoint = [$request->latitude, $request->longitude];

    //             $checkLocation = Location::where('user_id', $userId)
    //                                 ->where('date', $currentDate)
    //                                 ->first();
            
    //             if ($checkLocation) {
    //                 $history = json_decode($checkLocation->history, true);
    //                 $history[] = $newPoint;
    //                 $checkLocation->update(['history' => json_encode($history)]);
    //             } else {
    //                 Location::create([
    //                     'user_id' => $userId,
    //                     'date' => $currentDate,
    //                     'history' => json_encode([$newPoint]), // Store as JSON array
    //                 ]);
    //             }

    //             $location = [
    //                 'user_id' => $userId,
    //                 'latitude' => $request->latitude,
    //                 'longitude' => $request->longitude,
    //             ];
    //             event(new LocationUpdate($location));
    //         }
        
    //         return response()->json(['status' => 'Location updated successfully']);

    //     } catch (\Exception $e) {
    //         // dd($e->getMessage());
    //         \Log::error('Publish failed', ['error' => $e->getMessage()]);
    //         return response()->json(['status' => 'error'], 500);
    //     }
    // }
    public function initialLocations()
    {
        $users = User::get();
        
        return response()->json(
            $users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'position' => $user->latest_latitude != 0 
                    ? [$user->latest_latitude, $user->latest_longitude]
                    : null
            ])
        );
    }
    public function userLocationHistory($userId,$date)
    {
        $location = Location::where('user_id', $userId)
            ->where('date', date('Y-m-d',strtotime($date)))
            ->first();

        return response()->json($location ? json_decode($location->history) : []);
    }
    public function searchUser(Request $request){
        $query = $request->get('query');

        $users = User::where('name', 'like', "%$query%")
                        ->orWhere('email', 'like', "%$query%")
                        ->take(10)
                        ->get();

        return view('location.search_user_results', compact('users'));
    }
    public function setOffline(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $user->update(['is_online' => 0]); // Update the user as offline
        }

        return response()->json(['status' => 'offline updated']);
    }
}
