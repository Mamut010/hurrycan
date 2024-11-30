<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Products</title>
  <link rel="stylesheet" href="{{ assetsUrl('css/reset.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/general.css') }}">
  <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
  <script defer src="{{ assetsUrl('/js/products.js') }}"></script>
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
      <input type="text" id="product-search-bar" placeholder="Search in Hurry Can" value="{{ $keyword }}"/>
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
    <div class="main-content">
      <!-- Filter Section -->
      <section class="filter-container">
        <h3>FILTER</h3>
        <div id="shop-filter-container" class="filter-section">
          <h4>Shops</h4>
          @foreach($shops as $shop)
            <label><input type="checkbox" value="{{ $shop->user->name }}"/>
              {{ $shop->user->name }}
            </label><br>
          @endforeach
        </div>
        <div id="price-filter-container" class="filter-section">
          <h4>Price</h4>
          <label><input type="checkbox" value="10" data-operator="lt"/> Less than $10</label><br>
          <label><input type="checkbox" value="10" data-value="100"/> From $10 to $100</label><br>
          <label><input type="checkbox" value="100" data-value="500"/> From $100 to $500</label><br>
          <label><input type="checkbox" value="500" data-value="1000"/> From $500 to $1000</label><br>
          <label><input type="checkbox" value="1000" data-operator="gt"/> More than $1000</label><br>
        </div>
        <div id="rating-filter-container" class="filter-section">
          <h4>Rating</h4>
          <input type="range" min="0" max="10" step="1" />
          <label><input type="radio" name="rating" value="gt"/> And Up</label><br>
          <label><input type="radio" name="rating" value="lt"/> And Down</label><br>
        </div>
      </section>
      <!-- Product Section -->
      <section class="product-section">
        <h2>
          Results for "{{ $keyword }}"
        </h2>
        <div class="searchRes">
          <p>
            @if ($itemCount >= 2)
            {{ $itemCount }} items found
            @elseif ($itemCount === 1)
              1 item found
            @else
              Sorry, no item found
            @endif
          </p>
          <button type="button" id="clear-filters-button">Clear All Filters</button>
        </div>
        <div class="sorting">
          <label for="sort">Sort By:</label>
          <select id="sort">
            <option>Most Relevant</option>
          </select>
        </div>

        <div class="product-grid">
            @foreach($products as $product)
              <div class="product-card" data-url="{{ url('products/' . $product->id) }}">
                <div class="product-image">
                  <img src="{{ url($product->mainIllustrationPath) }}" alt="{{ $product->name }}"/>
                </div>
                <h4>{{ $product->name }}</h4>
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
            @endforeach
        </div>
      </section>
    </div>
    <div class="right-sidebar"></div> <!-- Placeholder for right column -->
  </main>
  
  <footer>
    <p>&copy; Copyright 2024. All Rights Reserved.</p>
    <p><a href="mailto:hurrycan@hurrcan.com.sg">hurrycan@hurrycan.com.sg</a></p>
  </footer>
</body>
</html>
