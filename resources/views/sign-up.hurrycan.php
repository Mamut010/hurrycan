<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registration</title>
  <link rel="stylesheet" href="{{ assetsUrl('css/reset.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/general.css') }}">
  <link rel="stylesheet" href="{{ assetsUrl('css/login.css') }}">
  <script defer src="{{ assetsUrl('/js/scripts.js') }}"></script>
  <script defer src="{{ assetsUrl('/js/sign-up.js') }}"></script>
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

    <div class="left-sidebar"></div>
    
    <div class="wrapper">
      <div class="login">
        <form id="sign-up-form">
          <h1>Registration</h1>
          <div class="input-box">
              <input type="text" name="name" placeholder="Your Full Name (Required)">
          </div>

          <div class="input-box">
            <input type="text" name="username" placeholder="Username (Required)">
          </div>

          <div class="input-box">
            <input type="text" name="email" placeholder="Your e-mail">
          </div>

          <div class="input-box">
            <input type="text" name="phoneNumber" placeholder="Your phone">
          </div>

          <div class="input-box">
              <input type="password" name="password" placeholder="Password (Required)">
          </div>

          <div class="input-box">
            <input type="password" name="passwordConfirmation" placeholder="Confirm Password (Required)">
          </div>

          <button type="submit" class="btn">Sign Up</button>
          <div class="register-link">
          <p>Already have an account?
              <a href="{{ url('auth/login') }}">Login</a></p>
          </div>
        </form>
      </div>
    </div>
    <div class="right-sidebar"></div>
  </main>

  <footer>
    <p>&copy; Copyright 2024. All Rights Reserved.</p>
    <p><a href="mailto:hurrycan@hurrcan.com.sg">hurrycan@hurrycan.com.sg</a></p>
  </footer>
</body>
</html>