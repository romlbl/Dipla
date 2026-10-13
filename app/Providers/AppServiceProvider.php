<?php

namespace App\Providers;

use App\Models\Product;
use App\Policies\ProductPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Company;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {  
        Gate::policy(Product::class, ProductPolicy::class);

        if (config('app.env') === 'production') {
            \URL::forceScheme('https');
        }
        $this->configureDefaults();

        RateLimiter::for('geocode', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });

        Event::listen(Login::class, function (Login $event): void {
            if ($event->guard === 'web') {
                session()->put('just_logged_in', true);
            }
        });

        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $routeName = $notifiable instanceof Company
                ? 'company.password.reset'
                : 'password.reset';

            $url = url(route($routeName, [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('Réinitialisation de votre mot de passe Dipla')
                ->greeting('Bonjour,')
                ->line('Vous avez demandez à réinitialiser votre mot de passe.')
                ->action('Choisir un nouveau mot de passe', $url)
                ->line('Ce lien expire dans 60 minutes.')
                ->line("Si ce n'est pas vous, ignorez ce mail.")
                ->salutation('L\'équipe Dipla');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    protected function configureRatelimiting(): void
    {
        RateLimiter::for('geocode', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip());
        });
    }

}
