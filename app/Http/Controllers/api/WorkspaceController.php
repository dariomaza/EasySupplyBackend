<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WorkspaceController extends Controller
{
    public function index()
    {
        $workspaces = Workspace::all();
        return $workspaces;
    }

    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workspace = Workspace::find($id);
        return $workspace;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $workspace = Workspace::findOrFail($id);

        if ($request->hasFile('imgSrc')) {
            if ($workspace->imgSrc) {
                // Eliminar la imagen anterior si existe
                Storage::disk('s3')->delete($workspace->imgSrc);
            }
            // Almacenar la nueva imagen en S3
            $rutaImagen = $request->file('imgSrc')->storePublicly('images/workspaceImages', 's3');

            $workspace->imgSrc = $rutaImagen;
        }

        $workspace->name = $request->name;
        $workspace->street = $request->street;
        $workspace->city = $request->city;
        $workspace->zipCode = $request->zipCode;
        $workspace->country = $request->country;

        $workspace->save();

        return response()->json(["Workspace updated successfully", $workspace]);
    }






    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        Workspace::destroy($id);
    }

    public function getProductsByWorkspace(Request $request, string $id)
    {
        $limit = $request->query('limit');

        $query = DB::table('products')
            ->join('suppliers', 'products.supplier_id', '=', 'suppliers.id')
            ->join('workspaces', 'workspaces.id', '=', 'suppliers.workspace_id')
            ->where('workspaces.id', '=', $id)
            ->select('products.*');

        if ($limit) {
            $query->limit($limit);
        }

        $products = $query->get();

        return response()->json($products);
    }


    public function getCartByWorkspaceId(String $id)
    {
        $workspace = Workspace::findOrFail($id);

        $cart = $workspace->cart;

        return Response()->json($cart);
    }

    public function getWorkspaceInvoices(String $workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);

        return response()->json([
            "invoices" => $workspace->invoices
        ]);
    }

    public function getWorkspaceUsers(String $workspaceId)
    {
        $workspace = Workspace::findOrFail($workspaceId);
        $users = $workspace->users;
        return response()->json($users);
    }
}
