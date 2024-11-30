<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{

    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $rutaImagen = null;
        if ($request->hasFile('img_src')) {
            // Validar que el archivo sea una imagen
            if ($request->file('img_src')->isValid()) {
                // Guardar la imagen en el almacenamiento de Laravel (por ejemplo, en la carpeta storage/app/public)
                $rutaImagen = $request->file('img_src')->store('images/productImages', 'public');
            }
        }

        // Crear el producto
        $product = Product::create([
            'name' => $request->name,
            'price' => $request->price,
            'description' => $request->description,
            'img_src' => $rutaImagen,
            'supplier_id' => $request->supplier_id
        ]);

        // Verificar si se creó correctamente el producto
        if ($product) {
            return response()->json(['message' => 'Producto creado correctamente.'], 201);
        } else {
            return response()->json(['error' => 'Hubo un problema al crear el producto.'], 500);
        }
    }

    public function show(string $id)
    {
        $product = Product::find($id);
        return response()->json($product);
    }


    public function update(Request $request, string $id)
    {
        $product = Product::findOrFail($id);

        // Manejo del archivo de imagen
        if ($request->hasFile('img_src')) {
            // Elimina la imagen anterior si existe
            if ($product->img_src) {
                Storage::disk('public')->delete($product->img_src);
            }
            // Almacena la nueva imagen
            $rutaImagen = $request->file('img_src')->store('images/productImages', 'public');
            $product->img_src = $rutaImagen;
        }

        // Actualiza los otros atributos del producto
        $product->name = $request->name;
        $product->price = $request->price;
        $product->description = $request->description;

        // Guarda el producto actualizado en la base de datos
        $product->save();

        // Retorna una respuesta indicando éxito
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);
    }

    public function destroy(string $id)
    {
        Product::destroy($id);
    }
}
