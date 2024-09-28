<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;

class ShowErrorLogsController extends Controller
{
    public function ShowProductsWIthErrors(Request $request)
    {
        $products = Products::with('variants')
            ->where('user_store_id', $request->store_id)
            ->whereHas('variants', function ($query) {
                $query->where('facebook_feed_error', "!=", null);
            })
            ->paginate(10);

        if (!$products) {

            return response()->json([
                'status' => 'error',
                'message' => 'No Error Logs for this store'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Show Error Logs',
            'data' => $products
        ]);
    }

    public function ShowAllProducts(Request $request)
    {
        $products = Products::with('variants')
            ->where("user_store_id", $request->store_id)
            ->paginate(10);

        if (!$products) {

            return response()->json([
                'status' => 'error',
                'message' => 'No Error Logs for this store'
            ]);
        }

        return response()->json([

            'status' => 'success',
            'message' => 'Show Error Logs',
            'data' => $products

        ]);
    }
}
