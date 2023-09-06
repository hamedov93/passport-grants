# Passport grants
Add custom grants to laravel passport.

# Installation
`composer require hamedov/passport-grants`

# Supported passport versions

| Passport version | Passport grants version |    Notes    |
|:----------------:|:-----------------------:|:-----------:|
|       ^7.0       |           ^1.0          |  --     --  |
|       ^8.0       |           ^2.0          |  --     --  |
|       ^9.0       |           ^3.0          |  --     --  |
|       ^10.0      |           ^4.0          |  --     --  |
|       ^11.0      |           ^5.0          |[Follow link](https://github.com/laravel/passport/blob/11.x/UPGRADE.md)|

# Usage

- Create a new grant:

We will be using facebook login as an example here
```
php artisan make:grant Facebook
```

This will create a new grant class in App\Grants folder

- Specify unique identifier for your grant
```
protected $identifier = 'facebook';
```
- Specify The parameters you will be sending in access token request for user authentication instead of username and password
```
protected $authParams = [
    'jwt_token',
];
```
The access token request should look like this:
```
$response = $http->post(env('APP_URL') . '/oauth/token', [
    'form_params' => [
        'grant_type' => 'facebook',
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'jwt_token' => 'facebook access token',
        'scope' => '',
        'guard' => 'api',
    ],
]);
```

- Next add your authentication logic to `getUserEntityByAuthParams` method

You will receive an empty instance of the authenticated model as first parameter.

The second parameter is an associative array containing values of parameters specified in `authParams` property.

- The method implementation should be something like this:
```
protected function getUserEntityByAuthParams(Model $model, $authParams,
    $guard, $grantType, ClientEntityInterface $clientEntity)
{
    // Do your logic to authenticate the user.
    // Return false or void if authentication fails.
    // This will throw OAuthServerException.
    $facebookAccessToken = $authParams['jwt_token'];
    // Contact facebook server to make sure the token is valid and get the corresponding user profile.
    $profile = file_get_contents('https://graph.facebook.com/me?fields=name,email&access_token='.$facebookAccessToken);
    $profile = (array) json_decode($profile);
    if ( ! isset($profile['email'])) {
        // We cannot identify the user without his email address
        return;
    }
    
    // Retrieve user or any authenticatable model by email or create new one.
    $user = $model->firstOrCreate(['email' => $profile['email']], ['name' => $profile['name']]);

    return new User($user->getAuthIdentifier());
}
```

- You can use the previous example with any authenticatable entity.

- Add the new grant to grants section of config/auth.php
```
'grants' => [
    App\Grants\Facebook::class,
],
```

# License
Released under the Mit license, see [LICENSE](https://github.com/hamedov93/passport-multiauth/blob/master/LICENSE)
