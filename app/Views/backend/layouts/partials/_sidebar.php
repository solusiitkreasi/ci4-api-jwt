

<div id="nav" class="nav-container d-flex">
    <div class="nav-content d-flex">
        <!-- Logo Start -->
        <div class="logo position-relative">
            <a href="<?= base_url('backend/dashboard') ?>">
            <!-- Logo can be added directly -->
            <img src="https://place-hold.it/110x45/00362b/fff/fff?text=TOL%20-%20API&fontsize=20&bold" alt="logo" />

            <!-- Or added via css to provide different ones for different color themes -->
            <!-- <div class="img"></div> -->
            </a>
        </div>
        <!-- Logo End -->

        <!-- User Menu Start -->
        <div class="user-container d-flex">
          <a href="#" class="d-flex user position-relative" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <img class="profile" alt="profile" src="<?= base_url('assets/admin/') ?>img/profile/profile.webp" />
              <div class="name"><b><?= strtoupper(session()->get('name')); ?></b></div>
          </a>
        </div>
          <!-- User Menu End -->

        <!-- Icons Menu Start -->
        <ul class="list-unstyled list-inline text-center menu-icons">
            <li>
                <a href="<?= base_url('logout'); ?>">
                    <i data-acorn-icon="logout" class="me-1" data-acorn-size="17" title="Logout"></i>
                    <div class="text-extra-small">Logout</div>
                </a>
            </li>
        </ul>
        <!-- Icons Menu End -->

        <!-- Menu Start -->
          <div class="menu-container flex-grow-1">
              <?php
              
              $menuModel = model('App\Models\MenuModel');

              $menu_sidebar = $menuModel->generateTree();
              
              echo $menu_sidebar; ?>

          </div>
        <!-- Menu End -->

        <!-- Mobile Buttons Start -->
        <div class="mobile-buttons-container">
            <a href="#" id="mobileMenuButton" class="menu-button">
                <i data-acorn-icon="menu"></i>
            </a>
        </div>
        <!-- Mobile Buttons End -->
    </div>

    <div class="nav-shadow"></div>
</div>