<?php

namespace App\Http\Controllers;

use App\Imports\CommonExcelImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Network;
use App\Models\Product;
use App\Models\Client;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('inventory_system.product.index');
    }
    public function dataTable()
    {
        $query = Product::with('unit','category');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Product $product) {
                $btn = '';
                if (auth()->user()->hasPermissionTo('product_edit')) {
                    $btn .= '<a href="' . route('product.edit', ['product' => $product->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->hasPermissionTo('product_delete')) {
                    $btn .= ' <a role="button" data-id="' . $product->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;

            })
            ->editColumn('purchase_price', function(Product $product) {
               return number_format($product->purchase_price,2);
            })
            ->editColumn('selling_price', function(Product $product) {
               return number_format($product->selling_price,2);
            })
            ->addColumn('unit_name', function(Product $product) {
               return $product->unit->name ?? '';
            })
            ->addColumn('category_name', function(Product $product) {
               return $product->category->name ?? '';
            })

            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('product_create')) {
            abort(403, 'Unauthorized');
        }
        $units = Unit::where('status',1)->get();
        $categories = Category::where('status',1)->get();
        return view('inventory_system.product.create',compact('units',
            'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('product_create')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')
            ],
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required',
            'category' => 'required',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $validatedData['code'] = Product::max('code') ? Product::max('code') + 1 : 1001;
            $validatedData['unit_id'] = $request->unit;
            $validatedData['category_id'] = $request->category;
            unset($validatedData['unit']); // Remove the 'channel' key
            unset($validatedData['category']); // Remove the 'channel' key

            // Create a new Product record in the database
            $product = Product::create($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product.index')->with('success', 'Product created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('product.create')->with('error', 'An error occurred while creating the Product: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        if (!auth()->user()->hasPermissionTo('product_edit')) {
            abort(403, 'Unauthorized');
        }
        try {
            $units = Unit::where('status',1)->get();
            $categories = Category::where('status',1)->get();
            // If the Product exists, display the edit view
            return view('inventory_system.product.edit', compact('product',
                'units','categories'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Product is not found
            return redirect()->route('product.index')->with('error', 'Product not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        if (!auth()->user()->hasPermissionTo('product_edit')) {
            abort(403, 'Unauthorized');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')
                    ->ignore($product)
            ],
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required',
            'category' => 'required',
            'status' => 'required|boolean', // Ensure 'status' is boolean
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $validatedData['unit_id'] = $validatedData['unit'];
            $validatedData['category_id'] = $validatedData['category'];
            unset($validatedData['unit']);
            unset($validatedData['category']);
            // Update the Product record in the database
            $product->update($validatedData);

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('product.index')->with('success', 'Product updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('product.edit',['product'=>$product->id])->withInput()
                ->with('error', 'An error occurred while updating the Product: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            if (!auth()->user()->hasPermissionTo('product_delete')) {
                abort(403, 'Unauthorized');
            }
            $inventory = Inventory::where('product_id',$product->id)->first();
            if ($inventory) {
                // If a related Supplier exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has stock logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Product record
            $product->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Product deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Product not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
