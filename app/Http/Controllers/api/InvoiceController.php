<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Supplier;
use App\Models\Workspace;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $orderId)
    {
        $products = DB::table('products')
            ->join('products_orders', 'products.id', '=', 'products_orders.product_id')
            ->join('orders', 'products_orders.order_id', '=', 'orders.id')
            ->where('orders.id', '=', $orderId)
            ->select(
                'products.id as product_id',
                'products.name',
                'products.description',
                'products.price',
                'products.img_src',
                'products.supplier_id',
                'orders.order_price',
                'products_orders.product_cuantity',
                'orders.id'
            )
            ->get();;

        $workspace = Order::findOrFail($orderId)->workspace;
        $user = $workspace->users->first();

        return Response()->json([
            'products' => $products,
            'workspace' => $workspace,
            'user' => $user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Buscar el registro de la factura por su ID
        $invoice = Invoice::findOrFail($id);

        // Verificar si el archivo correspondiente existe en el almacenamiento S3
        if (Storage::disk('s3')->exists($invoice->path)) {
            // Borrar el archivo del almacenamiento S3
            Storage::disk('s3')->delete($invoice->path);
        }

        // Borrar el registro de la factura de la base de datos
        $invoice->delete();

        // Devolver una respuesta exitosa
        return response()->json(['message' => 'Factura y archivo borrados exitosamente'], 200);
    }

    public function getOrderProducts(String $orderId, String $supplierId)
    {
        $order = DB::table('products')
            ->join('products_orders', 'products.id', '=', 'products_orders.product_id')
            ->join('orders', 'products_orders.order_id', '=', 'orders.id')
            ->where('orders.id', '=', $orderId)
            ->where('products.supplier_id', '=', $supplierId)
            ->select(
                'products.id as product_id',
                'products.name',
                'products.description',
                'products.price',
                'products.img_src',
                'products.supplier_id',
                'orders.order_price',
                'products_orders.product_cuantity',
                'orders.id'
            )
            ->get();

        $supplier = Supplier::findOrFail($supplierId);
        $workspace = Order::findOrFail($orderId)->workspace;
        $user = $workspace->users->first();

        return Response()->json([
            'order' => $order,
            'supplier' => $supplier,
            'workspace' => $workspace,
            'user' => $user
        ]);
    }

    public function uploadPDF(Request $request)
    {
        // Validar la solicitud
        $request->validate([
            'file' => 'required|file|mimes:pdf|max:2048',
        ]);

        // Almacenar el archivo en S3 en la carpeta 'pdfs'
        $file = $request->file('file');
        $path = $file->storePublicly('pdfs', 's3');

        // Obtener el tamaño del archivo en bytes y convertir a megabytes
        $sizeInBytes = $file->getSize();
        $sizeInMB = $sizeInBytes / (1024 * 1024); // Convertir a MB

        // Guardar la ruta y el tamaño en la base de datos
        $invoice = new Invoice();
        $invoice->path = $path;
        $invoice->workspace_id = $request->workspace_id;
        $invoice->size = $sizeInMB; // Almacenar el tamaño en MB
        $invoice->save();

        // Responder con éxito
        return response()->json(['path' => $path, 'size' => $sizeInMB], 200);
    }

    public function getTotalSizeByWorkspaceId($workspaceId)
    {
        // Sum the size of all invoices for the given workspace_id
        $totalSize = Invoice::where('workspace_id', $workspaceId)->sum('size');

        return response()->json([
            'totalSize' => $totalSize
        ]);
    }
}
