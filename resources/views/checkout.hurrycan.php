<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Checkout</title>
  <link rel="stylesheet" href="css/reset.css">
  <link rel="stylesheet" href="{{ assetsUrl('css/reset.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/general.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/checkout.css') }}">
  <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
  <script defer src="{{ assetsUrl('/js/checkout.js') }}"></script>
</head>
<body>
  <header>
  <div class="logo">
      <div class="logo-placeholder">
        <a href="{{ url('home') }}">
          <img src="{{ assetsUrl('images/hurrycanlogo.png') }}" alt="Hurrycan Logo">
        </a>
      </div>
    </div>

    <nav>
      <ul class="sidenav" id="menu-links">
        <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
        <li><a href="{{ url('home') }}">Home</a></li>
        <li><a href="{{ url('products') }}">Products</a></li>
        <li><a href="{{ url('policies') }}">Policies</a></li>
        <li><a href="{{ url('contact') }}">Contact</a></li>
      </ul>
      <a class="menu-icon" onclick="openNav()"><div>&#9776;</div></a>
    </nav>

    <div class="search-container">
      <input type="text" id="product-search-bar" placeholder="Search in Hurry Can"/>
      <button type="button" id="product-search-button" class="search-button">🔍</button>
    </div>

    <nav>
      <ul class="tablet-desktop">
        <li><a href="{{ url('home') }}">Home</a></li>
        <li><a href="{{ url('products') }}">Products</a></li>
        <li><a href="{{ url('policies') }}">Policies</a></li>
        <li><a href="{{ url('contact') }}">Contact</a></li>
      </ul>
    </nav>

    <div class="user-menu">
      <div id="auth-menu">
         <a data-url="{{ url('auth/login') }}">Login</a>
         |
         <a data-url="{{ url('auth/sign-up') }}">Sign Up</a>
      </div>
      <div class="cart-icon"><a href="{{ url('carts/user-cart') }}">🛒</a></div>
    </div>
  </header>
  
  <main class="main-layout">
    <div class="left-sidebar"></div> <!-- Placeholder for left column -->
    <div class="cart-list">
      @if (!$cart || empty($cart->cartProducts))
        Your cart is empty. Let's go shopping!
      @else
        <h1>Your Cart</h1>
        @foreach ($cart->cartProducts as $cartProduct)
          @php
            $product = $cartProduct->product;
            $total = $product->price * $cartProduct->quantity;
          @endphp

          <div class="cart-container">
            <img src="{{ url($product->mainIllustrationPath) }}" alt="{{ $product->name }}">

            <div class="cart-prod-detail">
              <div class="cart-prod-name">
                <h2>{{ $product->name }}</h2>
              </div>
              <div class="cart-info">
                <div class="cart-price">
                  <h2>${{ $product->price }}</h2>
                </div>
                <div class="cart-quantity">
                  <h2>{{ $cartProduct->quantity }}</h2>
                </div>
              </div>
            </div>

            <div class="cart-item-total">
              <h2>${{ $total }}</h2>
            </div>
          </div>
        @endforeach
  
        <div class="cart-total">
          <div class="netprice">
            Net total
          </div>

          <div class="totalprice">
            ${{ $cart->totalPrice }}
          </div>
        </div>

        <div class="cart-button">
          <a href="{{ url('carts/user-cart') }}"><button>Back</button></a>
          <button id="confirm-cart-button">Confirm</button>
        </div>
      @endif
    </div>

    <div class="right-sidebar"></div> <!-- Placeholder for right column -->
  </main>

  <footer>
    <p>&copy; Copyright 2024. All Rights Reserved.</p>
    <p><a href="mailto:hurrycan@hurrcan.com.sg">hurrycan@hurrycan.com.sg</a></p>
  </footer>
</body>
</html>
