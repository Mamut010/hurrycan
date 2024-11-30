<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hurry Can</title>
  <link rel="stylesheet" href="{{ assetsUrl('css/reset.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/general.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/index.css') }}">
  <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
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
    <div class="homepage">
       <div id="banner">
            <img src="{{ assetsUrl('images/hurrycanlogin.png') }}" alt="placeholder">
       </div>
      
       <div id="hotDeals">
          @foreach($topDeal as $product)
            <div data-url="{{ url('products/' . $product->id) }}">
              <img src="{{ url($product->mainIllustrationPath) }}" alt="{{ $product->name }}"/>
            </div>
            @if($count >= 4)
              @break
            @endif
          @endforeach
       </div>
       <div>
            <h1>ON SALE</h1>
       </div>

       <div id="onsale">
            @foreach($topDeal as $product)
              <div class="product-card" data-url="{{ url('products/' . $product->id) }}">
                <div class="product-image">
                  <img src="{{ url($product->mainIllustrationPath) }}" alt="{{ $product->name }}"/>
                </div>
                <h4>
                  {{ $product->name }}
                </h4>
                <div class="price-container">
                  @if ($product->discount > 0)
                    <div class="old-price-container">
                        <s class="price">
                          ${{ $product->originalPrice }}
                        </s>
                    </div>
                    <div class="new-price-container">
                        <b class="new-price">
                          ${{ $product->price }}
                        </b>
                        <span class="discount">
                          -{{ $product->discount }}%
                        </span>
                    </div>
                  @else
                    <div class="old-price-container">
                        <p class="price">
                          ${{ $product->price }}
                        </p>
                    </div>
                  @endif
                </div>
                <p>
                  ⭐
                  {{ $product->averageRating ?? '-' }}
                  /10
                </p>
              </div>
              @if($count >= 5)
                @break
              @endif
            @endforeach
       </div>
    </div>
    <div class="right-sidebar"></div> <!-- Placeholder for right column -->
  </main>

  <footer>
    <p>&copy; Copyright 2024. All Rights Reserved.</p>
    <p><a href="mailto:hurrycan@hurrcan.com.sg">hurrycan@hurrycan.com.sg</a></p>
  </footer>
</body>
</html>
