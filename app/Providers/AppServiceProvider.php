<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\VisitorSession;

use Livewire\Livewire; // ✅ ADD THIS
use App\Http\Livewire\ChatBox; // ✅ ADD THIS

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function boot()
    {
        // ✅ Manually register Livewire component
        Livewire::component('chat-box', ChatBox::class);

        if (app()->runningInConsole()) return;

        View::composer('*', function () {
            try {
                $path = Request::path();

                if (
                    !Request::ajax() &&
                    !Request::is('admin/*') &&
                    !str_starts_with($path, 'api') &&
                    !preg_match('/\.(png|jpg|jpeg|gif|svg|ico|css|js|webp)$/i', $path)
                ) {
                    $ip = Request::ip();
                    $url = Request::fullUrl();
                    $user = auth()->user();

                    $exists = VisitorSession::where('ip_address', $ip)
                        ->where('url', $url)
                        ->whereBetween('visited_at', [now()->subMinutes(1), now()])
                        ->exists();

                    if (!$exists) {
                        VisitorSession::create([
                            'ip_address' => $ip,
                            'user_id' => $user ? $user->id : null,
                            'user_agent' => Request::userAgent(),
                            'url' => $url,
                            'visited_at' => now(),
                        ]);
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Visitor tracking failed: ' . $e->getMessage());
            }
        });
    }
}
