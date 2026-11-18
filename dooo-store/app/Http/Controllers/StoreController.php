<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function home(): View { return view('store.home'); }
    public function shop(): View { return view('store.shop'); }
    public function product(): View { return view('store.product'); }
    public function cart(): View { return view('store.cart'); }
    public function checkout(): View { return view('store.checkout'); }
    public function wishlist(): View { return view('store.wishlist'); }
    public function about(): View { return view('store.about'); }
    public function shipping(): View { return view('store.shipping'); }
    public function contact(): View { return view('store.contact'); }

    /** Old static links (shop.html, product.html?id=02 …) keep working. */
    public function legacy(string $page): RedirectResponse
    {
        $target = $page === 'index' ? url('/') : url($page);
        $qs = request()->getQueryString();

        return redirect()->to($target.($qs ? '?'.$qs : ''), 301);
    }
}
