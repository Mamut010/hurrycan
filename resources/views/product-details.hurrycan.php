<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Product Details</title>
  <link rel="stylesheet" href="{{ assetsUrl('css/reset.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/general.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/details.css') }}">
  <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
  <script defer src="{{ assetsUrl('/js/product-details.js') }}"></script>
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
      <input type="text" id="product-search-bar" placeholder="Search in Hurry Can" />
      <button type="button" id="product-search-button" class="search-button">🔍</button>
    </div>

    <nav>
      <ul class = "tablet-desktop">
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
    <div class="detail-container">
      <input type="hidden" id="product-id" value="{{ $product->id }}">

      <div class="product-intro">
        <div class="detail-pic">
          <div class="main-picture">
            <img id="display-img" src="{{ url($product->mainIllustrationPath) }}" alt="Product Illustration">
          </div>
          <div id="gallery" class="gallery">
            @foreach($product->illustrations as $illustration)
              <img src="{{ url($illustration->imagePath) }}" alt="{{ 'Illustration ' . $count }}">
              @if ($count >= 3)
                @break
              @endif
            @endforeach
          </div>
        </div>

        <div class="flex-description">
          <h1>
            <span id="product-name">{{ $product->name }}</span>
          </h1>
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
          <p>
            {{ $product->briefDescription ?? 'No available description' }}
          </p>
          <div class="quantity-selection">
            Quantitiy
            <div class="quantity-button">
              <div class="minus"><button type="button" id="minus-quantity-button">-</button></div>
              <div class="quantity-number" id="product-quantity">1</div>
              <div class="plus"><button type="button" id="plus-quantity-button">+</button></div>
            </div>
          </div>

          <div class="detail-button">
            <button type="button" id="add-to-cart-button">Add to Cart</button>
            <button type="button" id="buy-now-button">Buy Now</button>
          </div>
        </div>
      </div>

      <div class="product-information">
        <h2>Information</h2>
        <p>
          This is a {{ $product->detailDescription ?? 'No available detailed description' }}
        </p>
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
