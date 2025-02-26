<?php

namespace App\Http\Controllers;

use App\Imports\CommonExcelImport;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\Network;
use App\Models\Product;
use App\Models\Client;
use App\Models\SubCategory;
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
        $products = Product::get();
        $categories = Category::get();
        $sub_categories = SubCategory::get();
        return view('settings.product.index',compact('products','categories','sub_categories'));
    }
    public function dataTable()
    {
        $query = Product::with('unit','category','subCategory');

       // product filtering
        if (request()->has('product') && request('product') != '') {
            $query->where('id', request('product'));
        }

        if (request()->has('category') && request('category') != '') {
            $query->where('category_id', request('category'));
        }

        if (request()->has('sub_category') && request('sub_category') != '') {
            $query->where('sub_category_id', request('sub_category'));
        }
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Product $product) {
                $btn = '';
                $btn .= '<a href="' . route('product.edit', ['product' => $product->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                $btn .= ' <a role="button" data-id="' . $product->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                return $btn;

            })
            ->editColumn('type', function(Product $product) {
               if($product->type == 1){
                  return '<span class="badge badge-success">Raw Item</span>';
               }else{
                  return '<span class="badge badge-info">Finished Goods</span>';
               }
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
            ->addColumn('sub_category_name', function(Product $product) {
               return $product->subCategory->name ?? '';
            })

            ->rawColumns(['action','type'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $units = Unit::where('status',1)->get();
        $categories = Category::where('status',1)->get();
        return view('settings.product.create',compact('units',
            'categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')
            ],
            'type' => 'required',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required',
            'category' => 'required',
            'sub_category_id' =>'required',
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
        try {
            $units = Unit::where('status',1)->get();
            $categories = Category::where('status',1)->get();
            // If the Product exists, display the edit view
            return view('settings.product.edit', compact('product',
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
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('products')
                    ->ignore($product)
            ],
            'type' => 'required',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'unit' => 'required',
            'category' => 'required',
            'sub_category_id' =>'required',
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
