<?php

namespace App\Transformers;

use App\Buyer;
use League\Fractal\TransformerAbstract;

class BuyerTransformer extends TransformerAbstract
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
    public function transform(Buyer $buyer)
    {
        return [
            'identifier'=>(int)$buyer->id,
            'name'=>(string)$buyer->name,
            'email'=>(string)$buyer->email,
            'isVerified'=>(int)$buyer->verified,
            
            'creationDate'=>$buyer->created_at,
            'lastChange'=>$buyer->updated_at,
            'deletedDate'=>isset($buyer->deleted_at)?(string) 
            $buyer->deleted_at : null,
            'links'=>[
                [
                    'rel'=>'self',
                    'href'=>route('buyers.show',$buyer->id),
                ],
                [
                    'rel'=>'buyer.productss',
                    'href'=>route('buyers.products.index',$buyer->id),
                ],
                [
                    'rel'=>'buyer.categories',
                    'href'=>route('buyers.categories.index',$buyer->id),
                ],
                
                [
                    'rel'=>'buyer.transactions',
                    'href'=>route('buyers.transactions.index',$buyer->id),
                ],
                [
                    'rel'=>'seller',
                    'href'=>route('sellers.index',$buyer->seller_id),
                ],
                [
                    'rel'=>'buyer.user',
                    'href'=>route('users.show',$buyer->id),
                ],
            ],
        ];
    }
}
