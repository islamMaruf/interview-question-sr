<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductVariantPrice;
use App\Models\Variant;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {

        $products = Product::query();

        $title = request('title');
        $variant = request('variant');
        $price_from = request('price_from');
        $price_to = request('price_to');
        $date = request('date');

        if ($title) {
            $products = $products->where('title', 'like', request()->title . "%");
        }
        if ($date) {
            $products = $products->where('created_at', $date);
        }
        $products = $products->with(['variantPrices' => function ($query) use ($variant, $price_from, $price_to) {
            if ($variant) {
                $query = $query->where(function ($search) use ($variant) {
                    $search->where('product_variant_one', $variant)
                        ->orWhere('product_variant_two', $variant)
                        ->orWhere('product_variant_three', $variant);
                });
            }
            if ($price_from && $price_to) {
                $query->whereBetween('price', [$price_from, $price_to]);
            } elseif ($price_from) {
                $query->where('price', '>=', $price_from);
            } else {
                $query->where('price', '<>', $price_to);
            }
        }]);
        $products = $products->paginate(3);

        $variants = ProductVariant::get()->unique('variant')->groupBy(function ($productVariant) {
            return Variant::find($productVariant->variant_id)->title;
        });

        return view('products.index', compact('products', 'variants'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function create()
    {
        $variants = Variant::all();
        return view('products.create', compact('variants'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreProductRequest $request)
    {
        try {
            DB::beginTransaction();
            $product = Product::create([
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description
            ]);
            foreach ($request->product_variant as $variant) {
                $variant_id = $variant['option'];
                foreach ($variant['tags'] as $tag) {
                    ProductVariant::create([
                        'variant_id' => $variant_id,
                        'product_id' => $product->id,
                        'variant' => $tag
                    ]);
                }
            }
            foreach ($request->product_variant_prices as $product_variant_price) {
                $title = explode("/", $product_variant_price['combination_variant']);
                $product_variant_one = isset($title[0]) && $title[0] ? ProductVariant::where('variant', $title[0])->first()->id : null;
                $product_variant_two = isset($title[1]) && $title[1] ? ProductVariant::where('variant', $title[1])->first()->id : null;
                $product_variant_three = isset($title[2]) && $title[2] ? ProductVariant::where('variant', $title[2])->first()->id : null;
                ProductVariantPrice::create([
                    'product_variant_one' => $product_variant_one,
                    'product_variant_two' => $product_variant_two,
                    'product_variant_three' => $product_variant_three,
                    'price' => $product_variant_price['price'],
                    'stock' => $product_variant_price['stock'],
                    'product_id' => $product->id
                ]);
            }
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
        }
        return redirect()->route('product.index');
    }


    /**
     * Display the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function show($product)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        $variants = Variant::whereHas('productVariant')->with(['productVariant' => function ($query) use ($product) {
            $query->where('product_id', $product->id);
        }])->get();

        $product = $product->load(['productVariant', 'variantPrices']);
        $productVariant = $this->getProductVariant($product);
        $variantPrice = $product->variantPrices;
        return view('products.edit', compact('variants', 'product', 'variantPrice', 'productVariant'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        try {
            // DB::beginTransaction()
            $product->update([
                'title' => $request->title,
                'sku' => $request->sku,
                'description' => $request->description
            ]);
            foreach ($request->product_variant as $variant) {
                $variant_id = $variant['option'];
                foreach ($variant['tags'] as $tag) {
                    // where('variant_id', $variant_id)->where('product_id', $product->id)->
                    ProductVariant::updateOrCreate(
                        [
                            'variant_id' => $variant_id,
                            'product_id' => $product->id,

                        ],
                        [
                            'variant_id' => $variant_id,
                            'product_id' => $product->id,
                            'variant' => $tag
                        ]
                    );
                }
            }
            foreach ($request->product_variant_prices as $product_variant_price) {
                $title = explode("/", $product_variant_price['combination_variant']);
                $product_variant_one = isset($title[0]) && $title[0] ? ProductVariant::where('variant', $title[0])->first()->id ?? null : null;
                $product_variant_two = isset($title[1]) && $title[1] ? ProductVariant::where('variant', $title[1])->first()->id ?? null : null;
                $product_variant_three = isset($title[2]) && $title[2] ? ProductVariant::where('variant', $title[2])->first()->id ?? null : null;
                ProductVariantPrice::updateOrCreate(
                    [

                        'product_id' => $product->id
                    ],
                    [
                        'product_variant_one' => $product_variant_one,
                        'product_variant_two' => $product_variant_two,
                        'product_variant_three' => $product_variant_three,
                        'product_id' => $product->id,
                        'price' => $product_variant_price['price'],
                        'stock' => $product_variant_price['stock'],

                    ]
                );
            }
            // DB::commit();
        } catch (Exception $exception) {
            dd($exception);
            // DB::rollBack();
        }
        return redirect()->route('product.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }

    private function getProductVariant($product)
    {
        $product_variant = collect($product->productVariant)->groupBy('variant_id');
        $store_array = [];
        foreach ($product_variant as $key => $variant) {
            $store[$key] = [];
            foreach ($variant as $v) {
                array_push($store[$key], $v->variant);
            }
            array_push($store_array, $store);
        }
        $product_variant = collect($store_array)->last();
        $temp = [];
        foreach ($product_variant as  $key => $value) {
            $temp[$key]['option'] = $key;
            $temp[$key]['tags'] = $value;
        }
        return collect($temp)->values();
    }
}
