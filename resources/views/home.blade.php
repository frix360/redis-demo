@extends('layouts.master')

@section('content')
    <div class="row">
        <div class="col-md-6 text-center">
            <div class="panel panel-default">
                <div class="panel-heading">Sukurti pirkinių krepšelį</div>

                <div class="panel-body">
                    <form method="post" action="{{route('shoppingCart.create')}}">
                        @csrf

                        <div class="form-group">
                            <label class="control-label" for="name">Pavadinimas</label>
                            <input id="name" class="form-control" type="text" name="name">
                        </div>

                        <input type="hidden" name="userId" value="{{$user->id}}">
                        <input type="submit" value="Sukurti" class="btn btn-default">
                    </form>
                </div>
            </div>

            <h4>Vartotojo {{$user->firstName . " " . $user->lastName}} pirkinių krepšeliai</h4>


            @foreach($user->shoppingCarts as $shoppingCart)

                <div class="panel panel-default">
                    <div class="panel-heading">{{$shoppingCart->name ?? ""}}</div>
                    <div class="panel-body">
                        <ul class="list-group">
                            @foreach($shoppingCart->products as $item)
                                <li class="list-group-item">{{$products[$item]->name}}.
                                    Kaina: {{$products[$item]->price}}
                                    <form style="display: inline" method="post" action="{{route('products.removeFromCart')}}">
                                        @csrf
                                        <input type="hidden" name="productId" value="{{$item}}">
                                        <input type="hidden" name="shoppingCartId" value="{{$shoppingCart->id}}">
                                        <input type="hidden" name="userId" value="{{$user->id}}">

                                        <div class="form-group">
                                            <input class="btn btn-default" type="submit" value="Pašalinti iš krepšelio">
                                        </div>
                                    </form></li>


                            @endforeach
                        </ul>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="col-md-6 text-center">
            <div class="panel panel-default">
                <div class="panel-heading">Pridėti prekę</div>

                <div class="panel-body">
                    <form method="post" action="{{route('products.create')}}">
                        @csrf
                        <div class="form-group">
                            <label for="name" class="control-label">Pavadinimas</label>
                            <input id="name" name="name" class="form-control" type="text">
                        </div>

                        <div class="form-group">
                            <label for="name" class="control-label">Kaina</label>
                            <input id="name" name="price" class="form-control" type="number" step="0.01">
                        </div>

                        <div class="form-group">
                            <label for="quantity" class="control-label">Kiekis</label>
                            <input id="quantity" name="quantity" class="form-control" type="number">
                        </div>

                        <div class="form-group">
                            <input class="btn btn-default" value="Sukurti" type="submit">
                        </div>


                    </form>
                </div>
            </div>

            <h4>Prekių sąrašas</h4>


            <ul class="list-group">
                @foreach($products as $product)
                    <li class="list-group-item">{{$product->name}}. Kaina: {{$product->price}}
                        Kiekis: {{$product->quantity}}
                        <form method="post" action="{{route('products.addToCart')}}">
                            @csrf
                            <input type="hidden" name="userId" value="{{$user->id}}">
                            <input type="hidden" name="productId" value="{{$product->id}}">
                            <label for="cart">Pridėti į krepšelį</label>
                            <div class="form-group">
                                <select id="cart" class="form-control" name="shoppingCartId">
                                    <option value=""></option>
                                    @foreach($user->shoppingCarts as $cart)
                                        <option value="{{$cart->id}}">{{$cart->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <input class="btn btn-default" value="Pridėti" type="submit">
                            </div>

                        </form>
                    </li>
                @endforeach
            </ul>

        </div>


    </div>
@endsection
