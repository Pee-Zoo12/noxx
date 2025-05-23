<div class="container">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav">
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/">Home</a>
              </li>
              <li class="nav-item dropdown">
                  <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                      Categories
                  </a>
                  <ul class="dropdown-menu" aria-labelledby="categoriesDropdown">
                      <?php
                      $products = readJsonFile(PRODUCTS_FILE);
                      $categories = [];
                      
                      foreach ($products['products'] as $product) {
                          if (isset($product['category_id']) && !in_array($product['category_id'], $categories)) {
                              $categories[] = $product['category_id'];
                          }
                      }
                      
                      $categoryNames = [
                          '1' => "Men's Wear",
                          '2' => "Women's Wear",
                          '3' => 'Footwear',
                          '4' => 'Accessories'
                      ];
                      
                      foreach ($categories as $categoryId) {
                          $categoryName = $categoryNames[$categoryId] ?? "Category $categoryId";
                          echo '<li><a class="dropdown-item" href="' . SITE_URL . '/products.php?category=' . urlencode($categoryId) . '">' . htmlspecialchars($categoryName) . '</a></li>';
                      }
                      
                      if (empty($categories)) {
                          echo '<li><a class="dropdown-item" href="#">No categories available</a></li>';
                      }
                      ?>
                  </ul>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/products.php">All Products</a>
              </li>
              <li class="nav-item">
                  <a class="nav-link" href="<?php echo SITE_URL; ?>/about.php">About Us</a>
              </li>
              
          </ul>
      </div>
  </div>
</nav>