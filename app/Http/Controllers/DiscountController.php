<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Discount;

class DiscountController extends Controller
{
    public function generate(Request $request)
    {
        $discount = $request->discount;

        $chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $voucher = "";
        for ($i = 0; $i < 7; $i++) {
            $voucher .= $chars[mt_rand(0, strlen($chars) - 1)];
        }

        Discount::create(['code' => $voucher, 'amount' => $discount]);

        return response()->json(["code" => $voucher]);
    }

    public function apply(Request $request)
    {
        $items = $request->items;
        $code = $request->code;

        $codeFromDb = Discount::where('code', $code)->first();
        $discountAmount = $codeFromDb->amount;

        $totalPrice = 0;
        $totalDiscount = 0;
        foreach ($items as $item) {
            $totalPrice += $item['price'];
        }

        if ($totalPrice > $discountAmount) {
            $percentsToDeduct = ($discountAmount * 100) / $totalPrice;
        } else {
            $percentsToDeduct = 100;
        }

        foreach ($items as &$item) {
            $item['price_with_discount'] = round($item['price'] - ($item['price'] * ($percentsToDeduct / 100)));
            $totalDiscount += $item['price'] - $item['price_with_discount'];
        }

        if ($totalDiscount < $discountAmount && $percentsToDeduct != 100) {
            $difference = $discountAmount - $totalDiscount;
            $items[0]['price_with_discount'] -= $difference;
        }

        if ($totalDiscount > $discountAmount && $percentsToDeduct != 100) {
            $difference = $totalDiscount - $discountAmount;
            $items[count($items) - 1]['price_with_discount'] += $difference;
        }

        return response()->json(["items" => $items, 'code' => $code, 'f' => $percentsToDeduct]);
    }
}
