<?php

namespace Hamedov\PassportGrants;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\UserRepository;
use League\OAuth2\Server\AuthorizationServer;
use Hamedov\PassportGrants\GrantMakeCommand;

class PassportGrantServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                GrantMakeCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->app->register(\Illuminate\Hashing\HashServiceProvider::class);
        $this->app->register(\Laravel\Passport\PassportServiceProvider::class);
        $this->enableCustomGrants();
    }

    public function enableCustomGrants()
    {
        $appKey = config('app.key');
        if ($appKey == null) {
            return;
        }

        // Get grants from auth config
        $grants = (array) config('auth.grants');

        try {
            $authorizationServer = resolve(AuthorizationServer::class);
            // Loop through the grants and enable them one by one
            foreach ($grants as $grantClass) {
                $grant = new $grantClass(
                    $this->app->make(RefreshTokenRepository::class),
                    $this->app->make(UserRepository::class)
                );

                $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

                $authorizationServer->enableGrantType(
                    $grant, Passport::tokensExpireIn()
                );
            }
        } catch (\Exception $e) {
            \Log::error('Passport grants error: ', $e->getMessage());
        }
    }
}
