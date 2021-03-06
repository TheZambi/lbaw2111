<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;


class User extends Authenticatable implements CanResetPassword, MustVerifyEmail
{
    use Notifiable;

    // Don't add create and update timestamps in database.
    public $timestamps  = false;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id','username', 'email', 'password','first_name','last_name',
    ];
    

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token'
    ];


    public function wishlist() {
        return $this->belongsToMany(Item::class,"wishlist", "user_id", "item_id");
    }

    public function wishlistItem($item_id) {
        return $this->belongsToMany(Item::class,"wishlist", "user_id", "item_id")->where('wishlist.item_id', $item_id)->get();
    }

    public function cart() {
        return $this->belongsToMany(Item::class,"cart", "user_id", "item_id")->withPivot('quantity')->get();
    }

    public function cartItem($item_id) {
        return $this->belongsToMany(Item::class,"cart", "user_id", "item_id")->withPivot('quantity')->where('cart.item_id', $item_id)->get();
    }

    public function cartTotal() {
        return $this->belongsToMany(Item::class,"cart", "user_id", "item_id")->withPivot('quantity')->sum(\DB::raw('quantity * (price - (price * get_discount(item.item_id, now())/100))'));
    }

    public function cartTotalShipping($shipping_option) {
        $shipping_option = Shipping::find($shipping_option);
        $cartTotal = $this->cartTotal();
        $cartTotal = floatval(preg_replace('/[^\d\.]/', '', $cartTotal));
        $shipping_price = floatval(preg_replace('/[^\d\.]/', '', $shipping_option['price']));

        $total = number_format($cartTotal + $shipping_price, 2, '.', ',');
        return '$' . $total;
    }

    public function cartTotalNumber() {
        return $this->belongsToMany(Item::class,"cart", "user_id", "item_id")->withPivot('quantity')->sum(\DB::raw('quantity')); 
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class, "user_id");
    }

    public function notifications() {
        return $this->belongsToMany(Item::class,"notification", "user_id", "item_id")->withPivot("notification_id")->withPivot("type")->get();
    }

    public function unseenNotifications() {
        return $this->belongsToMany(Item::class,"notification", "user_id", "item_id")->withPivot("notification_id")->withPivot("type")->where('is_seen',false)->get();
    }

    public function billingAddress() {
        return Address::find($this["billing_address"]);
    }

    public function shippingAddress() {
        return Address::find($this["shipping_address"]);
    }

    public function isBanned() {
      return !empty(DB::select("SELECT * FROM ban WHERE user_id = ?", array($this->user_id)));
    }

    public function banReason($id) {
        $ban = DB::select("SELECT * FROM ban WHERE user_id = ?", array($id))[0];
        if(!empty($ban)){
            if($ban->reason == "")
                return "No reason was given";
            return $ban->reason;
        }
        return "No reason was given";
    }

    public function image() {
        return $this->hasOne(Photo::class,"photo_id","img");
    }

    public function reviewed($item_id) {
        return Review::where('item_id', $item_id)->where('user_id', $this->user_id)->first();
    }

    
}
