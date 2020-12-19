<?php

namespace App\Http\Controllers;

use App\Category;
use App\Http\Requests\Admin\ProductRequest;
use Illuminate\Http\Request;
use App\Product;
use App\ProductGallery;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DashboardProductController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $products = Product::with(['galleries','category'])
                    ->where('users_id',Auth::user()->id)
                    ->get();
        return view('pages.dashboard-products',[
            'product' => $products
        ]);
    }

    public function detail(Request $request, $id)
    {
        $product = Product::with(['galleries','user','category'])->findOrFail($id);
        $categories = Category::all();

        return view('pages.dashboard-products-details',[
            'category' => $categories,
            'product'  => $product
        ]);
    }

    public function uploadGallery(Request $request){
        $data = $request->all();

        $data['photos'] = $request->file('photos')->store('assets/product','public');
        
        ProductGallery::create($data);
        return redirect()->route('dashboard-product-detail',$request->products_id);
    }

    public function deleteGallery(Request $request, $id){
        $item = ProductGallery::findOrFail($id);
        $item->delete();
        return redirect()->route('dashboard-product-detail',$item->products_id);
    }


    public function create()
    {
        $categories = Category::all();
        return view('pages.dashboard-products-create',[
            'category' => $categories
        ]);
    }

    public function store(ProductRequest $request)
    {
        $data = $request->all();
        $data['slug'] = Str::slug($request->name);
        
        $product = Product::create($data);

        if($request->file('photo'))
            $photo = $request->file('photo')->store('assets/product','public');
        else
            $photo = '';
            
        $gallery = [
            'products_id' => $product->id,
            'photos' => $photo
                
        ];

        ProductGallery::created($gallery);

        return redirect()->route('dashboard-product');
    }

    public function update(ProductRequest $request, $id)
    {
        $data = $request->all();        

        $item = Product::findOrFail($id);
        
        $data['slug'] = Str::slug($request->name);

        $item->update($data);
        return redirect()->route('dashboard-product');
    }
    
}
