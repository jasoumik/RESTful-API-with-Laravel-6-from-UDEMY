<?php

namespace App\Transformers;

use App\Seller;
use League\Fractal\TransformerAbstract;

class SellerTransformer extends TransformerAbstract
{
    /**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $defaultIncludes = [
        //
    ];
    
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        //
    ];
    
    /**
     * A Fractal transformer.
     *
     * @return array
     */
    public function transform(Seller $seller)
    {
        return [
            'identifier'=>(int)$seller->id,
            'name'=>(string)$seller->name,
            'email'=>(string)$seller->email,
            'isVerified'=>(int)$seller->verified,
            
            'creationDate'=>$seller->created_at,
            'lastChange'=>$seller->updated_at,
            'deletedDate'=>isset($seller->deleted_at)?(string) 
            $seller->deleted_at : null,
            'links'=>[
                [
                    'rel'=>'self',
                    'href'=>route('sellers.show',$seller->id),
                ],
                [
                    'rel'=>'seller.buyers',
                    'href'=>route('sellers.buyers.index',$seller->id),
                ],
                [
                    'rel'=>'seller.categories',
                    'href'=>route('sellers.categories.index',$seller->id),
                ],
                
                [
                    'rel'=>'seller.transactions',
                    'href'=>route('sellers.transactions.index',$seller->id),
                ],
                [
                    'rel'=>'seller.products',
                    'href'=>route('sellers.products.index',$seller->id),
                ],
                [
                    'rel'=>'buyer.user',
                    'href'=>route('users.show',$seller->id),
                ],
            ],
        ];
    }
}
