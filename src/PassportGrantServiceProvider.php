<?php
namespace Hamedov\PassportGrants;

use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Passport\Bridge\RefreshTokenRepository;
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

        // Enable user custom grants
        $this->enableCustomGrants();
    }

    public function register()
    {

    }

    public function enableCustomGrants()
    {
        $appKey = config('app.key');
        if ($appKey == null) {
            return;
        }

        // Get grants from auth config
        $grants = (array) config('auth.grants');

        // Loop through the grants and enable them one by one
        foreach ($grants as $grantClass) {
            $grant = new $grantClass(
                $this->app->make(RefreshTokenRepository::class)
            );

            $grant->setRefreshTokenTTL(Passport::refreshTokensExpireIn());

            app(AuthorizationServer::class)->enableGrantType(
                $grant, Passport::tokensExpireIn()
            );
        }
    }
}
