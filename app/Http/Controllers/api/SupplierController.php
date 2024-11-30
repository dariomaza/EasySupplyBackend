<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::all();
        return response()->json($suppliers);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $rutaImagen = null;
        if ($request->hasFile('img_src')) {
            // Validar que el archivo sea una imagen
            if ($request->file('img_src')->isValid()) {
                // Guardar la imagen en el almacenamiento de Laravel (por ejemplo, en la carpeta storage/app/public)
                $rutaImagen = $request->file('img_src')->store('images/suppliersImages', 'public');
            }
        }

        $supplier = Supplier::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'order_method' => $request->order_method,
            'img_src' => $rutaImagen,
            'workspace_id' => $request->workspace_id
        ]);

        if ($supplier) {
            return response()->json(['message' => 'Supplier created successfuly.'], 201);
        } else {
            return response()->json(['error' => 'There was a problem creating the product.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $supplier = Supplier::find($id);
        return response()->json($supplier);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $supplier = Supplier::findOrFail($id);
    
        if ($request->hasFile('img_src')) {
            // Elimina la imagen anterior si existe
            if ($supplier->img_src) {
                Storage::disk('public')->delete($supplier->img_src);
            }
            // Almacena la nueva imagen
            $rutaImagen = $request->file('img_src')->store('images/suppliersImages', 'public');
            $supplier->img_src = $rutaImagen;
        }

        $supplier->name = $request->name;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->order_method = $request->orderMethod;
        $supplier->save();

        return response()->json($supplier);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Supplier::destroy($id);
    }

    public function getByWorkspaceId($workspaceId)
    {
        $suppliers = Workspace::findOrFail($workspaceId)->suppliers;
        return response()->json($suppliers);
    }

    public function getLimitedSuppliers($workspaceId, $limit = 4)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $suppliers = isset($limit) ? $workspace->suppliers()->take($limit)->get() : $workspace->suppliers;
        return response()->json($suppliers);
    }


    public function getProductsBySupplierID(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return response()->json($supplier->products);
    }

    public function getSupplierOrderMethod(string $id)
    {
        $supplier = Supplier::findOrFail($id);
        return $supplier->order_method;
    }
}
