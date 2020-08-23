<?php

namespace App\Transformers;

use App\Transaction;
use League\Fractal\TransformerAbstract;

class TransactionTransformer extends TransformerAbstract
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
    public function transform(Transaction $transaction)
    {
        return [
            'identifier'=>(int)$transaction->id,
            'quantity'=>(int)$transaction->quantity,
            'buyer'=>(int)$transaction->buyer_id,
            'transaction'=>(int)$transaction->transaction_id,
            
            'creationDate'=>$transaction->created_at,
            'lastChange'=>$transaction->updated_at,
            'deletedDate'=>isset($transaction->deleted_at)?(string) 
            $transaction->deleted_at : null,
            'links'=>[
                [
                    'rel'=>'self',
                    'href'=>route('transactions.show',$transaction->id),
                ],
                
                [
                    'rel'=>'transaction.categories',
                    'href'=>route('transactions.categories.index',$transaction->id),
                ],
                [
                    'rel'=>'transaction.seller',
                    'href'=>route('transactions.sellers.index',$transaction->id),
                ],
                
                [
                    'rel'=>'buyer',
                    'href'=>route('buyers.show',$transaction->buyer_id),
                ],
                [
                    'rel'=>'product',
                    'href'=>route('products.show',$transaction->product_id),
                ],
            ],
        ];

    }
}
