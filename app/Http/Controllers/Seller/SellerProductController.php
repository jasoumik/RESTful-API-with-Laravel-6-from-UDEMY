<?php

namespace App\Http\Controllers\Seller;

use App\User;
use App\Seller;
use App\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\ApiController;
use Illuminate\Support\Facades\Storage;
use App\Transformers\ProductTransformer;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SellerProductController extends ApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('transform.input:' . ProductTransformer::class)->
        only(['store','update']);
        $this->middleware('scope:manage-products')->except(['index']);
        $this->middleware('can:view,seller')->
         only(['index']);
         $this->middleware('can:sell,seller')->
         only(['store']);
         $this->middleware('can:edit-product,seller')->
         only(['update']);
         $this->middleware('can:delete-product,seller')->
         only(['destroy']);
        // $this->middleware('scope:read-general')->
        //  only(['index']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Seller $seller)
    {
        if(request()->user()->tokenCan('read-general')||request()->user()->tokenCan('manage-products')){
            $products=$seller->products;
            return $this->showAll($products); 
        }
        throw new AuthorizationException('Invalid Scope(s)');
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
    public function store(Request $request, User $seller)
    {
        $rules=[
            'name'=>'required',
            'description'=>'required',
            'quantity'=>'required|integer|min:1',
            'image'=>'required|image',

        ];
        $this->validate($request,$rules);
        $data=$request->all();
        $data['status']=Product::UNAVAILABLE_PRODUCT;
        $data['image']=$request->image->store('');
        $data['seller_id']=$seller->id;

        $product=Product::create($data);
        return $this->showOne($product);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function show(Seller $seller)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function edit(Seller $seller)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Seller $seller, Product $product)
    {
        $rules=[
            'quantity'=>'integer|min:1',
            'status'=>'in:' . Product::AVAILABLE_PRODUCT . ',' . 
            Product::UNAVAILABLE_PRODUCT,
            'image'=>'image',
        ];
        $this->validate($request,$rules);
        $this->checkSeller($seller,$product);
        $product->fill($request->only([
            'name',
            'description',
            'quantity',
        ]));
        if($request->has('status')){
            $product->status =$request->status;
            if($product->isAvailable() && $product->categories()->count()==0){
                return $this->errorResponse('An active product must have at least 
                one category',409);
            }
        }
        if($request->hasFile('image')){
            Storage::delete($product->image);
            $product->image=$request->image->store('');
        }
        if($product->isClean()){
            return $this->errorResponse('Please Specify a different value to update',422);
        }

        $product->save();
        return $this->showOne($product);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Seller  $seller
     * @return \Illuminate\Http\Response
     */
    public function destroy(Seller $seller,Product $product)
    {
        $this->checkSeller($seller,$product);
        Storage::delete($product->image);
        $product->delete();
        return $this->showOne($product);
    }
    protected function checkSeller(Seller $seller,Product $product){
        if($seller->id != $product->seller_id){
            throw new HttpException(422,'The Seller is not the real seller of 
            the product');
        }
    }
}
