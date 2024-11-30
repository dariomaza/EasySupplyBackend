<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Mail\OrderMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Workspace;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $allorders = [];
        $orders = Order::pluck('id');
        foreach ($orders as $orderId) {
            $order = DB::table('products')
                ->join('products_orders', 'products.id', '=', 'products_orders.product_id')
                ->join('orders', 'products_orders.order_id', '=', 'orders.id')
                ->where('orders.id', '=', $orderId)
                ->orderBy('products.supplier_id')
                ->select(
                    'products.id as product_id',
                    'products.name',
                    'products.description',
                    'products.price',
                    'products.img_src',
                    'products.supplier_id',
                    'orders.order_price',
                    'products_orders.product_cuantity'
                )
                ->get();

            array_push($allorders, $order);
        }

        return Response()->json($allorders);
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
    public function show(string $id)
    {
        $order = DB::table('products')
            ->join('products_orders', 'products.id', '=', 'products_orders.product_id')
            ->join('orders', 'products_orders.order_id', '=', 'orders.id')
            ->where('orders.id', '=', $id)
            ->orderBy('products.supplier_id')
            ->select(
                'products.id as product_id',
                'products.name',
                'products.description',
                'products.price',
                'products.img_src',
                'products.supplier_id',
                'orders.order_price',
                'products_orders.product_cuantity'
            )
            ->get();

        return Response()->json($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $order = Order::findOrFail($id);
        $order->order_price = $request->order_price;
        $order->save();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return Response()->json(Order::destroy($id));
    }

    public function makeOrder(Request $request, string $cartId)
    {
        $cart = Cart::findOrFail($cartId);

        $order = Order::create([
            'workspace_id' => $request->workspace_id,
            'order_price' => $cart->total_price
        ]);

        $orderId = $order->getKey();

        $productCarts = DB::table('products_carts')->where('cart_id', $cartId)->get();

        foreach ($productCarts as $product) {
            DB::table('products_orders')->insert([
                'product_id' => $product->product_id,
                'order_id' => $orderId,
                'product_cuantity' => $product->product_cuantity
            ]);
        }


        DB::table('products_carts')->where('cart_id', $cartId)->delete();
        $cart->total_price = 0;
        $cart->save();

        $orderRes = $this->show($orderId);
        return Response()->json($orderRes);

        //TODO: Store order in order history table, and do the orders to each supplier
    }

    public function getWorkspaceOrders(String $workspaceId)
    {
        $orders = Order::where('workspace_id', $workspaceId)->get();
        return Response()->json($orders);
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
                'products_orders.product_cuantity'
            )
            ->get();

        $workspace = Order::findOrFail($orderId)->workspace;
        $user = $workspace->users->first();

        return Response()->json([
            'order' => $order,
            'workspace' => $workspace,
            'user' => $user
        ]);
    }

    public function sendOrderEmail(Request $request)
    {
        /*
        Request needs:
            supplierId,
            workspaceId,
        */

        $workspace = Workspace::findOrFail($request->workspaceId);

        $supplier = Supplier::findOrFail($request->supplierId);
        $supplierEmail = $supplier->email;

        $orderId = $workspace->orders->last()->id;

        $user = $workspace->users->first();

        $orderURL = 'http://easysupply.duckdns.org/order/' . $orderId . "/" . $request->supplierId;

        $orderDetails = [
            // Información de la orden
            'name' => $user->firstName . " " . $user->lastName,
            'orderUrl' => $orderURL
        ];

        // Envía el correo electrónico
        Mail::to($supplierEmail)->send(new OrderMail($orderDetails));

        return response()->json(['message' => 'Order email sent successfully']);
    }
    public function sendOrderSMS(Request $request)
    {
        /*
        Request needs:
            supplierId,
            workspaceId,
        */

        $workspace = Workspace::findOrFail($request->workspaceId);

        $supplier = Supplier::findOrFail($request->supplierId);
        $supplierPhone = $supplier->phone;

        $orderId = $workspace->orders->last()->id;

        $user = $workspace->users->first();

        $orderURL = 'http://easysupply.duckdns.org/order/' . $orderId . "/" . $request->supplierId;

        $sid    = env("TWILIO_SID");
        $token  = env("TWILIO_AUTHTOKEN");
        $twilio = new Client($sid, $token);

        $message = $twilio->messages
            ->create(
                $supplierPhone, // to
                array(
                    "from" => "+12562977286",
                    "body" => "You have recived an order from: " .
                        $user->firstName . " " . $user->lastName .
                        ". Please check the link bellow to see details. Link: " . $orderURL,
                )
            );

        return response()->json(['message' => 'Order sms sent successfully']);
    }

    public function getTotalPriceForCurrentMonth($workspaceId)
    {
        // Obtener la fecha de inicio y fin del mes actual
        $startOfMonth = Carbon::now()->startOfMonth()->toDateTimeString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateTimeString();

        // Calcular el precio total de las órdenes del mes actual para el workspace dado
        $totalPrice = Order::where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('order_price');

        // Contar el número de órdenes del mes actual para el workspace dado
        $orderCount = Order::where('workspace_id', $workspaceId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->count();

        return response()->json([
            'workspace_id' => $workspaceId,
            'total_price' => $totalPrice,
            'order_count' => $orderCount,
            'month' => Carbon::now()->format('F Y')
        ]);
    }

    public function getMonthlyRevenueByWorkspace($workspaceId)
    {
        $monthlyData = Order::select(
            DB::raw('YEAR(created_at) as year'),
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(order_price) as total_revenue')
        )
            ->where('workspace_id', $workspaceId)
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $formattedData = [
            'revenue' => [
                'name' => 'Revenue',
                'data' => $monthlyData->pluck('total_revenue')->toArray()
            ],
            'total_revenue' => $monthlyData->sum('total_revenue')
        ];

        return $formattedData;

        return response()->json($formattedData);
    }

    public function productQuantitiesByMonth($workspaceId)
    {
        $productQuantitiesByMonth = Order::with('products')
            ->selectRaw('MONTH(orders.created_at) as month, products_orders.product_id, SUM(products_orders.product_cuantity) as total_quantity')
            ->join('products_orders', 'orders.id', '=', 'products_orders.order_id')
            ->join('products', 'products_orders.product_id', '=', 'products.id')
            ->where('orders.workspace_id', $workspaceId)
            ->groupBy('month', 'products_orders.product_id')
            ->orderBy('month')
            ->get();

        // Procesar los resultados para agruparlos por mes y producto
        $result = [];
        foreach ($productQuantitiesByMonth as $item) {
            $productName = Product::find($item->product_id)->name; // Obtenemos el nombre del producto
            $month = Carbon::create()->month($item->month)->format('F');

            // Si aún no existe una entrada para este producto, la creamos
            if (!isset($result[$productName])) {
                $result[$productName] = [
                    'name' => $productName,
                    'data' => [],
                ];
            }

            // Agregamos la cantidad total para este mes al array 'data' del producto
            $result[$productName]['data'][] = $item->total_quantity;
        }

        return array_values($result);
    }

    public function updateOrderStatus(Request $request, String $orderId)
    {
        $order = Order::findOrFail($orderId);
        if (!$order) return response()->json(['Order doesnt exits'], 404);
        $order->status = $request->status;
        $order->save();

        return response()->json(['Order status updated successfuly'], 200);
    }
}
