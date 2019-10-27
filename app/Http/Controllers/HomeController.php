<?php

namespace App\Http\Controllers;

use Faker\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Str;
use Predis\ClientException;


class HomeController extends Controller
{
    public function index(Request $request)
    {
        $userKey = $request->userId ?? 1;

        if (!Redis::exists('user-' . $userKey)) {
            $faker = Factory::create('lt');

            Redis::set('user-' . $userKey, json_encode([
                'id' => $userKey,
                'firstName' => $faker->firstName,
                'lastName' => $faker->lastName,
                'shoppingCarts' => []
            ]));
        }

        $user = json_decode(Redis::get('user-' . $userKey));
        $shoppingCartIds = $user->shoppingCarts;

        $user->shoppingCarts = collect($shoppingCartIds)->map(function ($id) use ($userKey) {
            return json_decode(Redis::get(sprintf('user-%s:shoppingCart-%s', $userKey, $id))); // Composite key
        });


        $productIds = collect(json_decode(Redis::get('products')));
        $products = $productIds->mapWithKeys(function ($product) {
            return [$product => json_decode(Redis::get('product-' . $product))];
        });

        return view('home', compact('user', 'products'));
    }

    public function createShoppingCart(Request $request)
    {
        $request->validate([
            'userId' => 'required',
            'name' => 'required'
        ]);

        $shoppingCartId = (string)Str::uuid();

        $key = sprintf('user-%s:shoppingCart-%s', $request->userId, $shoppingCartId);
        $value = json_encode(['id' => $shoppingCartId, 'name' => $request->name, 'products' => []]);
        Redis::set($key, $value);

        $user = json_decode(Redis::get('user-' . $request->userId)); // Add new shopping cart key to users for reference
        $user->shoppingCarts[] = $shoppingCartId;


        Redis::set('user-' . $request->userId, json_encode($user));

        return redirect()->route('index', ['userId' => $user->id]);
    }

    public function createProduct(Request $request)
    {
        $request->validate([
            'userId' => 'userId',
            'name' => 'required',
            'price' => 'required',
            'quantity' => 'required'
        ]);

        $product = $request->except('_token');
        $productId = (string)Str::uuid();

        $product['id'] = $productId;

        $products = json_decode(Redis::get('products')) ?? [];


        $products[] = $productId;

        Redis::set('products', json_encode($products));
        Redis::set('product-' . $productId, json_encode($product));

        return redirect()->route('index', ['userId' => $request->userId]);
    }

    public function addProductToCart(Request $request)
    {
        $request->validate([
            'userId' => 'required',
            'productId' => 'required',
            'shoppingCartId' => 'required'
        ]);

        $productKey = 'product-' . $request->productId;

        $product = json_decode(Redis::get($productKey));
        if ($product->quantity !== 0) {

            $userShoppingCartKey = sprintf('user-%s:shoppingCart-%s', $request->userId, $request->shoppingCartId);

            Redis::watch($userShoppingCartKey, $productKey); // Watch these keys

            $shoppingCart = json_decode(Redis::get($userShoppingCartKey));

            $shoppingCart->products[] = $request->productId;
            $product->quantity--;

            try {
                Redis::pipeline(function ($pipe) use ($request, $shoppingCart, $product, $userShoppingCartKey, $productKey) {  // Transaction implementation
                    $pipe->set($userShoppingCartKey, json_encode($shoppingCart));
                    $pipe->set($productKey, json_encode($product));
                });
            } catch (ClientException $exception) {
                echo $exception->getMessage();
            }
        }

        return redirect()->route('index', ['userId' => $request->userId]);
    }

    public function removeProductFromCart(Request $request)
    {
        $request->validate([
            'userId' => 'required',
            'productId' => 'required',
            'shoppingCartId' => 'required'
        ]);

        $productKey = 'product-' . $request->productId;

        $userShoppingCartKey = sprintf('user-%s:shoppingCart-%s', $request->userId, $request->shoppingCartId);

        Redis::watch($userShoppingCartKey, $productKey); // Watch these keys

        $product = json_decode(Redis::get($productKey));

        $shoppingCart = json_decode(Redis::get($userShoppingCartKey));

        $productIndex = array_search($request->productId, $shoppingCart->products);

        array_splice($shoppingCart->products, $productIndex, 1);

        $product->quantity++;

        try {
            Redis::pipeline(function ($pipe) use ($request, $shoppingCart, $product, $userShoppingCartKey, $productKey) {  // Transaction implementation
                $pipe->set($userShoppingCartKey, json_encode($shoppingCart));
                $pipe->set($productKey, json_encode($product));
            });
        } catch (ClientException $exception) {
            echo $exception->getMessage();
        }


        return redirect()->route('index', ['userId' => $request->userId]);
    }
}
