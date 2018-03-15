<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;

/**
 * api用户认证服务
 * @package App\Providers
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {
            $device = $request->header('Device');
            $platform = $request->header('Platform') ?: 0;
            if (empty($device)) {
                return null;
            }
            $user = User::firstOrCreate([
                'device' => $device,
                'platform' => $platform,
            ]);
            if (!$user) {
                return null;
            }
            return $user;
        });
    }
}
