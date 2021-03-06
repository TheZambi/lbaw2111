<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;    
use App\Models\Item;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Auth;

class UserProfileController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        $categories = Category::all()->sortBy("category_id");
        
        return view('pages.userProfile')->with("user", $user)->with("categories", $categories);
    }

    public function showSelf()
    {
        $user = Auth::user();

        $categories = Category::all()->sortBy("category_id");
        
        return view('pages.userProfile')->with("user", $user)->with("categories", $categories);
    }
}
