<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contact;
use App\Observers\ContactObserver;

class CrmServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Contact::observe(ContactObserver::class);
    }
}
