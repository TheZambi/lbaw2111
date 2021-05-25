<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    public $timestamps  = false;
    protected $table = 'purchase';
    protected $primaryKey = 'purchase_id';

    public function purchasedItems() {
        return $this->hasMany(PurchaseItem::class,"purchase_id");
    }

    public function purchaseTotal() {
        return $this->hasMany(PurchaseItem::class,"purchase_id")->sum(\DB::raw('quantity * price'));
    }

    public function getDate() {
        return date('Y-m-d', strtotime($this["date"]));
    }

    public function billingAddress() {
        return Address::find($this["billing_address"]);
    }

    public function shippingAddress() {
        return Address::find($this["shipping_address"]);
    }
}
