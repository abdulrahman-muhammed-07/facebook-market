<?php

namespace App\Http\Controllers\Widget;

use App\Models\Variants;
use Illuminate\Http\Request;

class getDataForWidgetController
{
    public function getDataForWidget(Request $request)
    {
        $request->validate([
            'store_id' => 'required|email',
            'product_id' => 'required|alpha_num',
            'variant_id' => 'required|alpha_num'
        ]);

        $productVariantsData = Variants::where('user_store_id', $request->store_id)->where('variant_id', $request->variant_id)->select(['variant_id', 'sent_to_facebook_feed', 'facebook_feed_error'])->get();

        if (!$productVariantsData) {

            return response()->json([
                'status' => 'error',
                'message' => 'Product not sent to facebook yet'
            ]);
        }

        $productVariantsData = $productVariantsData->toArray();

        return response()->json([
            'status' => 'success',
            'message' => 'Product sent successfully',
            'data' => $productVariantsData
        ]);
    }
}
