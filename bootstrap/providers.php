<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\RepositoryServiceProvider::class,

    // TelescopeServiceProvider sengaja TIDAK didaftarkan di sini.
    // laravel/telescope ada di require-dev, jadi pada production (composer install --no-dev)
    // class induknya tidak ada dan aplikasi fatal error di setiap request.
    // AppServiceProvider::register() sudah mendaftarkannya khusus environment local,
    // lengkap dengan guard class_exists().
];
