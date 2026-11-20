<?php

namespace App\Providers;

use App\Support\FoundItems;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Statamic\Statamic;
use Statamic\Events\EntrySaved;
use Statamic\Events\EntryDeleted;
use Statamic\Facades\StaticCache;

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
        // Das CP-Menü «Formulare» (config/statamic/collection-groups.php) bricht ab, wenn eine
        // Collection fehlt. «Fundmeldungen» entsteht sonst erst mit der ersten Meldung.
        Statamic::booted(function () {
            if (! $this->app->runningInConsole() && request()->is(trim(config('statamic.cp.route'), '/') . '*')) {
                FoundItems::ensureSetup();
            }
        });
    }
}
