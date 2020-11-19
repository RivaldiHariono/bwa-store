<?php

namespace App\Http\Controllers;

use App\Category;
use App\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $categories = Category::all();
        $products   = Product::with(['galleries'])->paginate(1);

        return view('pages.category',[
            'categories' => $categories,
            'products'   => $products
        ]);
    }

    public function detail(Request $request, $id)
    {
        $categories = Category::all();
        $category = Category::where('id',$id)->firstOrFail();
        $products   = Product::with(['galleries'])->where('categories_id',$category->id)->paginate(16);

        return view('pages.category',[
            'categories' => $categories,
            'products'   => $products
        ]);
    }
}
