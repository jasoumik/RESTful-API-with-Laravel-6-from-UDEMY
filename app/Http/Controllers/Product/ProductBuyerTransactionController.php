<?php

namespace App\Http\Controllers\Product;


use App\User;
use App\Product;
use App\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ApiController;
use App\Transformers\TransactionTransformer;

class ProductBuyerTransactionController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:' . TransactionTransformer::class)->
        only(['store']);
        $this->middleware('scope:purchase-product')->
        only(['store']);
        $this->middleware('can:purchase,buyer')->
         only(['store']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, Product $product, User $buyer)
    {
        $rules=[
            'quantity'=>'required|integer|min:1',
        ];
        $this->validate($request,$rules);
        if($buyer->id == $product->seller_id){
            return $this->errorResponse('The Buyer Must be Diffrnt from the Seller',409);
        }
        if(!$buyer->isVerified()){
            return $this->errorResponse('The Buyer Must be a verified user',409);
        }
        if(!$product->seller->isVerified()){
            return $this->errorResponse('The Seller Must be a verified user',409);
        }
        if(!$product->isAvailable()){
            return $this->errorResponse('The Product is not available',409);
        }
        if($product->quantity < $request->quantity){
            return $this->errorResponse('The Product doesnt have enough units',409);
        }
        return DB::transaction(function () use($request,$product,$buyer) {
            $product->quantity -=$request->quantity;
            $product->save();
            
            $transaction =Transaction::create([
                'quantity'=>$request->quantity,
                'buyer_id'=>$buyer->id,
                'product_id'=>$product->id,
            ]);
            return $this->showOne($transaction,201);
        });
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        //
    }
}
