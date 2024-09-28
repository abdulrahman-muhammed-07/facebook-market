<?php

namespace App\Http\Controllers\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;

class DataIndexController extends Controller
{
    public function getProductsWithStatuses(Request $request)
    {
        $perPagePagination = 25;

        if ($request->per_page) {

            $perPagePagination = $request->per_page;

            if ($perPagePagination > 25) {

                $perPagePagination = 25;
            }
        }

        $status = $request->status ?? 'success';

        /**
         * 
         * accepted statues for database retrieve the data =>
         * 
         * [
         * 'success'
         * 'failed_with_errors
         * 'pending'
         * 'excluded'
         * 'with_warnings'
         * ]
         * 
         */

        $successUploadedProducts = Products::where('store_id', '=', $request->store_id)->where('product_upload_status', '=', $status)->paginate($perPagePagination);

        return response()->json(
            [
                'status' => 'success',
                'message' => 'products with status "' .  $status . '"',
                'data' => $successUploadedProducts
            ]
        );
    }
}
