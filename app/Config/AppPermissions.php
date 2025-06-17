<?php

namespace Config;


class AppPermissions {
    public const MANAGE_USERS = 'manage-users';
    public const VIEW_PRODUCTS = 'view-products';
    // ...
}
// Penggunaan: ['filter' => ['jwtAuth', 'permission:' . AppPermissions::MANAGE_USERS]]