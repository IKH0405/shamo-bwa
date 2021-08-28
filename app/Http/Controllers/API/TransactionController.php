<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id =  $request->input('id');
        $limit = $request->input('limit');
        $status = $request->input('status');

            if (id){
                $transaction = Transaction::with(['item,product'])->find($id);

                if($transaction)
                {
                    return ResponseFormatter::success(
                        $transaction,
                        'Data transaksi berhasil diambil'
                    );
                }
                else {
                    return ResponseFormatter::error(
                        null,
                        'Data trasaksi tidak ada',404
                    );
                }
            }

            $transaction = Transaction::with(['item','product'])->where('users_id',Auth::user()->id);
            if ($status) {
                $transaction->where('status',$status);
            }
            ResponseFormatter::succes->pagimate($limit);
    }
    public function checkout(Request $request)
    {
        $request->validate([
            'item' => 'required|array',
            'item.*.id' => 'exists::products.id',
            'total_price'=>'required',
            'shipping_price'=>'required',
            'status'=> 'required:PENDING,SUCCESS,FAILED,SHIPPING,SHIPPED'
            
                
            
        ]);
        $transaction = Transaction::create({
            'users_id'=> Auth::user()->id,
            'address'=> $request->address,
            'total_price'=>$request->total_price,
            'shipping_price'=>$request->shipping_price,
            'status'=>$request->request->status,
        });
        foreach ($request->item as $product ) {
            TransactionItem::create([
                'users_id'=>Auth::user()->id,
                'products_id'=> $product['id'],
                'transaction_id'=>$transaction->id,
                'quantity'=>  $product['quantity']
            ]);

           
        }
        return ResponseFormatter::success($transaction->load('items.product'),Transaksi berhasil);

    }
}
