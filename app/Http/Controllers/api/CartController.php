<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Workspace;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allCarts = [];
        $carts = Cart::pluck('id');
        foreach ($carts as $cartId) {
            $cart = DB::table('products')
                ->join('products_carts', 'products.id', '=', 'products_carts.product_id')
                ->join('carts', 'products_carts.cart_id', '=', 'carts.id')
                ->where('carts.id', '=', $cartId)
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.description',
                    'products.price',
                    'products.img_src',
                    'products.supplier_id',
                    'carts.total_price',
                    'products_carts.product_cuantity'
                )
                ->get();

            array_push($allCarts, $cart);
        }

        return Response()->json($allCarts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $products = DB::table('products')
            ->join('products_carts', 'products.id', '=', 'products_carts.product_id')
            ->join('carts', 'products_carts.cart_id', '=', 'carts.id')
            ->where('carts.id', '=', $id)
            ->select(
                'products.id as product_id',
                'products.name',
                'products.description',
                'products.price',
                'products.img_src',
                'products.supplier_id',
                'carts.total_price',
                'products_carts.product_cuantity'
            )
            ->get();

        return Response()->json($products);
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $cart = Cart::findOrFail($id);
        $cart->total_price = $request->total_price;
        $cart->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Response()->json(Cart::destroy($id));
    }

    public function addProductToCart(Request $request, string $id)
    {

        $cart = Cart::findOrFail($id);

        // Verificar si el producto ya existe en el carrito
        $existingProduct = DB::table('products_carts')
            ->where('product_id', $request->product_id)
            ->where('cart_id', $cart->id)
            ->first();

        if ($existingProduct) {
            // Si el producto ya existe, actualizamos la cantidad sumando el valor de $request->product_cuantity
            DB::table('products_carts')
                ->where('product_id', $request->product_id)
                ->where('cart_id', $cart->id)
                ->increment('product_cuantity', $request->product_cuantity);
        } else {
            // Si el producto no existe, lo insertamos
            DB::table('products_carts')->insert([
                'product_id' => $request->product_id,
                'cart_id' => $cart->id,
                'product_cuantity' => $request->product_cuantity
            ]);
        }
    }

    public function removeProductFromCart(Request $request, string $id)
    {
        $cart = Cart::findOrFail($id);

        // Eliminar el registro que coincida con el product_id y el cart_id
        DB::table('products_carts')
            ->where('product_id', $request->product_id)
            ->where('cart_id', $cart->id)
            ->delete();
    }

    public function updateProductQuantity(Request $request, string $cartId)
    {
        $affected = DB::table('products_carts')
            ->where('cart_id', $cartId)
            ->where('product_id', $request->product_id)
            ->update(['product_cuantity' => $request->product_cuantity, 'updated_at' => now()]);

        if ($affected) {
            return response()->json(['message' => 'Product quantity updated successfully.']);
        } else {
            return response()->json(['message' => 'Cart item not found.'], 404);
        }
    }
}
