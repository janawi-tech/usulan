<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Blog\Category as BlogPostCategory;
use App\Models\Blog\Post as BlogPost;
use App\Models\Banner\Category as BannerCategory;
use App\Models\Banner\Content as BannerPost;
use App\Models\Lab;
use App\Models\Usulan;
use App\Policies\ActivityPolicy;
use App\Policies\Blog\CategoryPolicy as BlogPostCategoryPolicy;
use App\Policies\Blog\PostPolicy as BlogPostPolicy;
use App\Policies\Banner\CategoryPolicy as BannerCategoryPolicy;
use App\Policies\Banner\ContentPolicy as BannerPostPolicy;
use App\Policies\ExceptionPolicy;
use App\Policies\LabPolicy;
use App\Policies\UsulanPolicy;
use BezhanSalleh\FilamentExceptions\Models\Exception;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Activity::class => ActivityPolicy::class,
        BlogPostCategory::class => BlogPostCategoryPolicy::class,
        BlogPost::class => BlogPostPolicy::class,
        BannerCategory::class => BannerCategoryPolicy::class,
        BannerPost::class => BannerPostPolicy::class,
        Exception::class => ExceptionPolicy::class,
        Lab::class => LabPolicy::class,
        Usulan::class => UsulanPolicy::class,
        'Spatie\Permission\Models\Role' => 'App\Policies\RolePolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
