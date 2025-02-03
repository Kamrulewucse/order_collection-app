<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignImage;
use App\Models\Client;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class CampaignController extends Controller
{
    public function index()
    {
        try {
            return view('campaign.index');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }
    public function dataTable()
    {

        $query = Campaign::query();


        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Campaign $campaign) {

                $btn = '';
                $btn .= '<a href="' . route('campaign.show', ['campaign' => $campaign->id]) . '" class="btn btn-success bg-gradient-success btn-sm btn-edit">Images</a> ';
                $btn .= '<a href="' . route('campaign.edit', ['campaign' => $campaign->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                if(in_array(auth()->user()->role, ['Admin', 'SuperAdmin'])){
                    $btn .= ' <a role="button" data-id="' . $campaign->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }

                return $btn;
            })
            ->editColumn('start_date', function (Campaign $campaign) {
                return Carbon::parse($campaign->start_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function (Campaign $campaign) {
                return Carbon::parse($campaign->end_date)->format('d-m-Y');
            })
            ->editColumn('end_date', function (Campaign $campaign) {
                return number_format($campaign->campaign_cost,2);
            })
            ->editColumn('campaign_type', function (Campaign $campaign) {
                if ($campaign->campaign_type == 1) {
                    return '<span class="badge badge-success">Online</span>';
                } else{
                    return '<span class="badge badge-warning">Offline</span>';
                }
            })
            ->rawColumns(['action', 'campaign_type'])
            ->toJson();
    }
    public function create()
    {
        $srs = Client::where('type', 2)->get();
        return view('campaign.create', compact('srs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:1,2',
            'location' => 'nullable|string|max:255',
            'campaign_cost' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'file_names.*' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Store the campaign data
            $campaign = new Campaign();
            $campaign->name = $request->name;
            $campaign->campaign_type = $request->campaign_type;
            $campaign->location = $request->location;
            $campaign->campaign_cost = $request->campaign_cost;
            $campaign->start_date = Carbon::parse($request->start_date);
            $campaign->end_date = Carbon::parse($request->end_date);
            $campaign->created_by = auth()->user()->id;
            $campaign->details = $request->details;
            $campaign->save();

            // Handle file uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $key => $image) {
                    $filename = Uuid::uuid1()->toString() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = 'uploads/campaign_images';
                    $image->move($destinationPath, $filename);
                    $campaignImage = new CampaignImage();
                    $campaignImage->campaign_id = $campaign->id;
                    $campaignImage->file_name = $request->file_names[$key] ?? 'image_' . ($key + 1);
                    $campaignImage->file_path = $destinationPath . '/' . $filename;
                    $campaignImage->save();
                }
            }
            return redirect()->route('campaign.index')->with('success', 'Campaign added successfully');
        } catch (\Exception $e) {
            dd($e);
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to submit leave application: ' . $e->getMessage()]);
        }
    }

    public function edit(Campaign $campaign)
    {
        $campaign->load('images');
        return view('campaign.edit', compact('campaign'));
    }

    public function update(Request $request, Campaign $campaign)
    {
        // return($request->all());
        $request->validate([
            'name' => 'required|string|max:255',
            'campaign_type' => 'required|in:1,2',
            'location' => 'nullable|string|max:255',
            'campaign_cost' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'details' => 'nullable|string',
            'file_names.*' => 'nullable|string|max:255',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        try {
            // Store the campaign data
            $campaign->name = $request->name;
            $campaign->campaign_type = $request->campaign_type;
            $campaign->location = $request->location;
            $campaign->campaign_cost = $request->campaign_cost;
            $campaign->start_date = Carbon::parse($request->start_date);
            $campaign->end_date = Carbon::parse($request->end_date);
            $campaign->created_by = auth()->user()->id;
            $campaign->details = $request->details;
            $campaign->save();

            // Handle deleted images
            if ($request->has('deleted_images')) {
                foreach ($request->deleted_images as $imageId) {
                    $image = CampaignImage::find($imageId);
                    if ($image) {
                        if (file_exists(public_path($image->file_path))) {
                            unlink(public_path($image->file_path)); // Delete the image file
                        }
                        $image->delete();
                    }
                }
            }

            // Update existing image names
            if ($request->has('existing_file_names')) {
                foreach ($request->existing_file_names as $id => $fileName) {
                    $image = CampaignImage::find($id);
                    if ($image) {
                        $image->file_name = $fileName;
                        $image->save();
                    }
                }
            }

            // Handle file uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $key => $image) {
                    $filename = Uuid::uuid1()->toString() . '.' . $image->getClientOriginalExtension();
                    $destinationPath = 'uploads/campaign_images';
                    $image->move($destinationPath, $filename);
                    $campaignImage = new CampaignImage();
                    $campaignImage->campaign_id = $campaign->id;
                    $campaignImage->file_name = $request->file_names[$key] ?? 'image_' . ($key + 1);
                    $campaignImage->file_path = $destinationPath . '/' . $filename;
                    $campaignImage->save();
                }
            }

            return redirect()->route('campaign.index')->with('success', 'Campaign updated successfully');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors(['error' => 'Failed to update leave application: ' . $e->getMessage()]);
        }
    }

    public function show(Campaign $campaign)
    {
        $campaign->load('images');
        return view('campaign.images', compact('campaign'));
    }
    public function destroy(Campaign $campaign)
    {
        try {
            $campaignImages = CampaignImage::where('campaign_id',$campaign->id)->get();
            foreach ($campaignImages as $image) {
                if (file_exists(public_path($image->file_path))) {
                    unlink(public_path($image->file_path)); // Delete the image file
                }
                $image->delete();
            }
            $campaign->delete();

            return response()->json(['success' => true, 'message' => 'Campaign deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete leave: ' . $e->getMessage()]);
        }
    }
}
