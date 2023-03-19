<?php

namespace App\Http\Controllers\frontend;

use App\Models\Brand;
use App\Models\Slider;
use App\Models\Product;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use App\Models\SubSubCategory;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {

        $data['sliders']            = Slider::where('status', true)->latest('id')->where('status', 1)->get();
        $data['products']           = Product::where('status', true)->latest('id')->take(15)->where('status', 1)->get();
        $data['categories']         = Category::with('products')->where('status', true)->oldest('id')->where('status', 1)->get();
        
    
        HomeController::setarabic();
        
        return view('frontend.index', $data);
    } 
    public function terms()
    {
        return view('frontend.terms');
    } 
    public function privacy()
    {
        return view('frontend.privacy');
    } 

    public function setarabic()
    {
        if(session()->has('language')){

         
        }
        else{
             session()->get('language');
            session()->forget('language');
            session()->put('language', 'bangla');
        }
    }
    public function detailsbn($category, $slug)
    {
        HomeController::setarabic();
        $product                    = Product::where('product_slug_bn', $slug)->firstOrFail();
        $id                         = $product->id;

        $category                   =Category::where('id',$product->category_id)->firstOrFail();
        $data['product']            = $product;
        $data['category']            = $category;
        $subcategory_id             = $product->subcategory_id; 
        $subcategory = SubCategory::where('id', $subcategory_id)->firstOrFail();
        $data['subcategory']        = $subcategory;

        $data['previous_product']   = Product::where('id', '<', $id)->where('status', 1)->orderBy('id', 'desc')->first();
        $data['next_product']       = Product::where('id', '>', $id)->where('status', 1)->orderBy('id', 'desc')->first();
        $color                      = $product->product_color;
        $data['product_color']      = explode(',', $color);
        $size                       = $product->size;
        $data["size"]               = explode(',', $size);
        return view('frontend.shop.details', $data);
    } 
    public function detailsen($category, $slug)
    {
        HomeController::setarabic();
        $product                    = Product::where('product_slug_en', $slug)->firstOrFail();

        $id                         = $product->id; 
        $subcategory_id             = $product->subcategory_id; 
        $data['product']            = $product;
        $category                   =Category::where('id',$product->category_id)->firstOrFail();
        $data['category']            = $category;

        $subcategory = SubCategory::where('id', $subcategory_id)->firstOrFail();
        $data['subcategory']        = $subcategory;
        $data['previous_product']   = Product::where('id', '<', $id)->where('status', 1)->orderBy('id', 'desc')->first();
        $data['next_product']       = Product::where('id', '>', $id)->where('status', 1)->orderBy('id', 'desc')->first();
        $color                      = $product->product_color;
        $data['product_color']      = explode(',', $color);
        $size                       = $product->size;
        $data["size"]               = explode(',', $size);
        return view('frontend.shop.details', $data);
    }

    public function category()
    {
        HomeController::setarabic();
        $category = Category::where('status', 1)->inRandomOrder()->first();
        return view('frontend.shop.category',compact('category'));
    }
    function viewProduct($id)
    {
        HomeController::setarabic();
        $product                = Product::findOrFail($id);
        $category               = $product->category;
        $color                  = $product->product_color;
        $product_color          = explode(',', $color);

        $size = $product->size;
        $size = explode(',', $size);

        return response()->json(array(
            'product'           => $product,
            'color'             => $product_color,
            'size'              => $size,
            'category'          => $category,
        ));
    }

    public function categoryproductsen($category, Request $request)
    {
        HomeController::setarabic();
        $colors             = Product::productFilter()['colors'];
        $sizes              = Product::productFilter()['sizes'];
        if ($request->ajax()) {
            $data           = $request->all();
            $url            = $data['url'];
            if (session()->get('language') === 'bangla') {
                $category   = Category::where('category_slug_bn', $url)->firstOrFail();
            } else {
                $category   = Category::where('category_slug_en', $url)->firstOrFail();
            }

            $id             = $category->id;
            $products       = Product::where('category_id', $id);
            if (isset($data['brand']) && !empty($data['brand'])) {
                $products->where('brand_id', $data['brand']);
            }
            if (isset($data['color']) && !empty($data['color'])) {
                $color      = $data['color'];
                $products->where('product_color', 'LIKE', '%' . $color . "%");
            }

            if (isset($data['size']) && !empty($data['size'])) {
                $size       = $data['size'];
                $products->where('size', 'LIKE', '%' . $size . "%");
            }
            if (isset($data['min_price']) && !empty($data['min_price'])) {
                $min_price  = $data['min_price'];
                $products->where('price', '>=', $min_price);
            }
            if (isset($data['max_price']) && !empty($data['max_price'])) {
                $max_price  = $data['max_price'];
                $products->where('price', '<=', $max_price);
            }

            if (isset($data['sort']) && !empty($data['sort'])) {
                if ($data['sort'] == "product_latest") {
                    $products->orderBy('id', 'desc');
                } else if ($data['sort'] == "product_name_a_to_z") {
                    $products->orderBy('product_name_en', 'Asc');
                } else if ($data['sort'] == "product_name_z_to_a") {
                    $products->orderBy('product_name_en', 'desc');
                } else if ($data['sort'] == "price_low") {
                    $products->orderBy('price', 'Asc');
                } else if ($data['sort'] == "price_high") {
                    $products->orderBy('price', 'desc');
                } else {
                    $products->orderBy('id', 'desc');
                }
            }

            $products = $products->paginate(30);
            return view('frontend.shop.listing')->with(compact('products'));
        } else {
            $category = Category::where('category_slug_en', $category)->with('products')->firstOrFail();
            return view('frontend.shop.category', compact('category', 'colors', 'sizes'));
        }
    }

    public function categoryproductsbn($category, Request $request)
    {
        HomeController::setarabic();
        $colors             = Product::productFilter()['colors'];
        $sizes              = Product::productFilter()['sizes'];
        if ($request->ajax()) {
            $data       = $request->all();
            $url            = $data['url'];
            if (session()->get('language') === 'bangla') {
                $category   = Category::where('category_slug_bn', $url)->firstOrFail();
            } else {
                $category   = Category::where('category_slug_en', $url)->firstOrFail();
            }

            $id             = $category->id;
            $products       = Product::where('category_id', $id);
            if (isset($data['brand']) && !empty($data['brand'])) {
                $products->where('brand_id', $data['brand']);
            }
            if (isset($data['color']) && !empty($data['color'])) {
                $color      = $data['color'];
                $products->where('product_color', 'LIKE', '%' . $color . "%");
            }

            if (isset($data['size']) && !empty($data['size'])) {
                $size       = $data['size'];
                $products->where('size', 'LIKE', '%' . $size . "%");
            }
            if (isset($data['min_price']) && !empty($data['min_price'])) {
                $min_price = $data['min_price'];
                $products->where('price', '>=', $min_price);
            }
            if (isset($data['max_price']) && !empty($data['max_price'])) {
                $max_price = $data['max_price'];
                $products->where('price', '<=', $max_price);
            }
            if (isset($data['sort']) && !empty($data['sort'])) {
                if ($data['sort'] == "product_latest") {
                    $products->orderBy('id', 'desc');
                } else if ($data['sort'] == "product_name_a_to_z") {
                    $products->orderBy('product_name_en', 'Asc');
                } else if ($data['sort'] == "product_name_z_to_a") {
                    $products->orderBy('product_name_en', 'desc');
                } else if ($data['sort'] == "price_low") {
                    $products->orderBy('price', 'Asc');
                } else if ($data['sort'] == "price_high") {
                    $products->orderBy('price', 'desc');
                } else {
                    $products->orderBy('id', 'desc');
                }
            }

            $products = $products->paginate(30);
            return view('frontend.shop.listing')->with(compact('products'));
        } else {
            $category = Category::where('category_slug_bn', $category)->with('products')->firstOrFail();
            return view('frontend.shop.category', compact('category', 'colors', 'sizes'));
        }
    }


    public function subcategoryproductsen($category, $subcategory, Request $request)
    {
        HomeController::setarabic();
        $colors                 = Product::productFilter()['colors'];
        $sizes                  = Product::productFilter()['sizes'];
        $brands                 = Brand::where('status', 1)->get();
        if ($request->ajax()) {
            $data               = $request->all();
            $url                = $data['url'];
            if (session()->get('language') === 'bangla') {
                $subcategory    = SubCategory::where('subcategory_slug_bn', $url)->firstOrFail();
            } else {
                $subcategory    = SubCategory::where('subcategory_slug_en', $url)->firstOrFail();
            }
            $id                 = $subcategory->id;
            $products           = Product::where('subcategory_id', $id);
            if (isset($data['brand']) && !empty($data['brand'])) {
                $products->where('brand_id', $data['brand']);
            }
            if (isset($data['color']) && !empty($data['color'])) {
                $color          = $data['color'];
                $products->where('product_color', 'LIKE', '%' . $color . "%");
            }
            if (isset($data['size']) && !empty($data['size'])) {
                $size           = $data['size'];
                $products->where('size', 'LIKE', '%' . $size . "%");
            }

            if (isset($data['min_price']) && !empty($data['min_price'])) {
                $min_price      = $data['min_price'];
                $products->where('price', '>=', $min_price);
            }
            if (isset($data['max_price']) && !empty($data['max_price'])) {
                $max_price      = $data['max_price'];
                $products->where('price', '<=', $max_price);
            }
            if (isset($data['sort']) && !empty($data['sort'])) {
                if ($data['sort'] == "product_latest") {
                    $products->orderBy('id', 'desc');
                } else if ($data['sort'] == "product_name_a_to_z") {
                    $products->orderBy('product_name_en', 'Asc');
                } else if ($data['sort'] == "product_name_z_to_a") {
                    $products->orderBy('product_name_en', 'desc');
                } else if ($data['sort'] == "price_low") {
                    $products->orderBy('price', 'Asc');
                } else if ($data['sort'] == "price_high") {
                    $products->orderBy('price', 'desc');
                } else {
                    $products->orderBy('id', 'desc');
                }
            }

            $products   = $products->paginate(10);
            return view('frontend.shop.listing')->with(compact('products'));
        } else {
            $subcategory = SubCategory::where('subcategory_slug_en', $subcategory)->with('products')->firstOrFail();
            return view('frontend.shop.subcategory', compact('subcategory', 'colors', 'sizes', 'brands'));
        }
    }
    public function subcategoryproductsbn($category, $subcategory, Request $request)
    {
        HomeController::setarabic();
        $colors             = Product::productFilter()['colors'];
        $sizes              = Product::productFilter()['sizes'];
        $brands             = Brand::where('status', 1)->get();
        if ($request->ajax()) {
            $data           = $request->all();
            $url            = $data['url'];
            if (session()->get('language') === 'bangla') {
                $subcategory= SubCategory::where('subcategory_slug_bn', $url)->firstOrFail();
            } else {
                $subcategory= SubCategory::where('subcategory_slug_en', $url)->firstOrFail();
            }
            $id             = $subcategory->id;
            $products       = Product::where('subcategory_id', $id);
            if (isset($data['brand']) && !empty($data['brand'])) {
                $products->where('brand_id', $data['brand']);
            }
            if (isset($data['color']) && !empty($data['color'])) {
                $color      = $data['color'];
                $products->where('product_color', 'LIKE', '%' . $color . "%");
            }

            if (isset($data['size']) && !empty($data['size'])) {
                $size       = $data['size'];
                $products->where('size', 'LIKE', '%' . $size . "%");
            }
            if (isset($data['min_price']) && !empty($data['min_price'])) {
                $min_price  = $data['min_price'];
                $products->where('price', '>=', $min_price);
            }
            if (isset($data['max_price']) && !empty($data['max_price'])) {
                $max_price = $data['max_price'];
                $products->where('price', '<=', $max_price);
            }
            if (isset($data['sort']) && !empty($data['sort'])) {
                if ($data['sort'] == "product_latest") {
                    $products->orderBy('id', 'desc');
                } else if ($data['sort'] == "product_name_a_to_z") {
                    $products->orderBy('product_name_en', 'Asc');
                } else if ($data['sort'] == "product_name_z_to_a") {
                    $products->orderBy('product_name_en', 'desc');
                } else if ($data['sort'] == "price_low") {
                    $products->orderBy('price', 'Asc');
                } else if ($data['sort'] == "price_high") {
                    $products->orderBy('price', 'desc');
                } else {
                    $products->orderBy('id', 'desc');
                }
            }

            $products = $products->paginate(10);
            return view('frontend.shop.listing')->with(compact('products'));
        } else {
            $subcategory = SubCategory::where('subcategory_slug_bn', $subcategory)->with('products')->firstOrFail();
            return view('frontend.shop.subcategory', compact('subcategory', 'colors', 'sizes', 'brands'));
        }
    }


    public function subsubcategoryproductsbn($category, $subcategory, $subsubcategory, Request $request)
    {
        HomeController::setarabic();
        $colors         = Product::productFilter()['colors'];
        $sizes          = Product::productFilter()['sizes'];
        $brands         = Brand::where('status', 1)->get();
        if ($request->ajax()) {
            $data       = $request->all();
            $url        = $data['url'];
            if (session()->get('language') === 'bangla') {
                $subsubcategory = SubSubCategory::where('subsubcategory_slug_bn', $url)->firstOrFail();
            } else {
                $subsubcategory = SubSubCategory::where('subsubcategory_slug_en', $url)->firstOrFail();
            }
            $id         = $subsubcategory->id;
            $products   = Product::where('subsubcategory_id', $id);
            if (isset($data['brand']) && !empty($data['brand'])) {
                $products->where('brand_id', $data['brand']);
            }
            if (isset($data['color']) && !empty($data['color'])) {
                $color  = $data['color'];
                $products->where('product_color', 'LIKE', '%' . $color . "%");
            }
            if (isset($data['size']) && !empty($data['size'])) {
                $size   = $data['size'];
                $products->where('size', 'LIKE', '%' . $size . "%");
            }
            if (isset($data['min_price']) && !empty($data['min_price'])) {
                $min_price = $data['min_price'];
                $products->where('price', '>=', $min_price);
            }
            if (isset($data['max_price']) && !empty($data['max_price'])) {
                $max_price = $data['max_price'];
                $products->where('price', '<=', $max_price);
            }
            // Sort product
            if (isset($data['sort']) && !empty($data['sort'])) {
                if ($data['sort'] == "product_latest") {
                    $products->orderBy('id', 'desc');
                } else if ($data['sort'] == "product_name_a_to_z") {
                    $products->orderBy('product_name_en', 'Asc');
                } else if ($data['sort'] == "product_name_z_to_a") {
                    $products->orderBy('product_name_en', 'desc');
                } else if ($data['sort'] == "price_low") {
                    $products->orderBy('price', 'Asc');
                } else if ($data['sort'] == "price_high") {
                    $products->orderBy('price', 'desc');
                } else {
                    $products->orderBy('id', 'desc');
                }
            }

            $products = $products->paginate(10);
            return view('frontend.shop.listing')->with(compact('products'));
        } else {


            if (session()->get('language') === 'bangla') {
              $subcategor = SubCategory::where('subcategory_slug_bn', $subcategory)->firstOrFail();   
                $subsubcategory = SubSubCategory::where('subcategory_id',$subcategor->id)->where('subsubcategory_slug_bn',$subsubcategory)->with('products')->firstOrFail();
            
            } else {
                $subcategor = SubCategory::where('subcategory_slug_en', $subcategory)->firstOrFail();
                $subsubcategory = SubSubCategory::where('subcategory_id',$subcategor->id)->where('subsubcategory_slug_en',$subsubcategory)->with('products')->firstOrFail();
            
            }
            

     
            
       
        


          return view('frontend.shop.subsubcategory', compact('subsubcategory', 'brands', 'colors', 'sizes'));
        }
    }


    public function commingsoon(){
        HomeController::setarabic();
        return view('frontend.partials.commingsoon');
    }
}
