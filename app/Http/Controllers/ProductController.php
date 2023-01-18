<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Variant;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index(Request $request)
    {
        if($request){

            $title = $request->title;
            $variant = $request->variant;
            $price_from = $request->price_from;
            $price_to = $request->price_to;
            $date = $request->date;

            $products = Product::with('productVariantPrice')
                ->when($title, function ($query, $title) {
                    return $query->where('title', 'like', '%'.$title.'%');
                })
                ->when($date, function ($query, $date) {
                    return $query->whereDate('created_at', $date);
                })
                ->when($variant, function ($query, $variant) {
                    return $query->where('id', $variant);
                })
                ->when($price_from, function ($query, $price_from) {
                    return $query->where('price', $price_from);
                })
                ->when($price_to, function ($query, $price_to) {
                    return $query->where('price', $price_to);
                })
                ->paginate(5);

            $product_variants = ProductVariant::all();
            return view('products.index',compact('products','product_variants'));
        }

        $products = Product::latest()->paginate(5);
        $product_variants = ProductVariant::all();
        return view('products.index',compact('products','product_variants'));
    }


    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }


    public function store(Request $request)
    {
        if($request->isMethod('post')){


             $request->validate([
                'product_name' => 'required',
                'product_sku' => 'required',
                'product_description' => 'required',
            ]);


            $product = Product::create([
                'title' => $request->product_name,
                'sku' => $request->product_sku,
                'description' => $request->product_description,
            ]);


            // create variants
            $newVariant = new ProductVariant;

            if ($request->has('product_variant')) {
                foreach ($request->product_variant as $key => $variant) {

                    foreach ($variant['value'] as $value) {
                        $newVariant->create(
                            [
                                'variant' => $value,
                                'variant_id' => $variant['option'],
                                'product_id' => $product->id
                            ]
                        );

                    }
                }
            }




            //create previews
//            if ($request->has('product_preview')) {
//                foreach ($request->product_preview as $key => $variant) {
//                    $preview = new ProductVariantPrice;
//
//                    $preview->product_id = $product->id;
//
//                    $preview->price = $request->price[$key];
//                    $preview->stock = $request->stock[$key];
//                    $preview->save();
//                }
//            }

            return redirect()->back()->with('success', 'Product created successfully');

        }
    }



    public function show($product)
    {

    }


    public function edit(Product $product)
    {
        $prod = Product::with(['productVariantPrice','variant'])->findOrFail($product->id);
        $product = json_decode($prod);

        return view('products.edit', compact('product'));

    }


    public function update(Request $request, Product $product)
    {
        //
    }


    public function destroy(Product $product)
    {
        //
    }
}
