<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\Blog\Category as BlogPostCategory;
use App\Models\Blog\Post as BlogPost;
use App\Policies\ActivityPolicy;
use App\Policies\Blog\CategoryPolicy as BlogPostCategoryPolicy;
use App\Policies\Blog\PostPolicy as BlogPostPolicy;
use App\Policies\ExceptionPolicy;
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
        Exception::class => ExceptionPolicy::class,
        \Spatie\Permission\Models\Role::class => \App\Policies\RolePolicy::class,
        \Datlechin\FilamentMenuBuilder\Models\Menu::class => \App\Policies\MenuPolicy::class,
        \App\Models\Banner\Content::class => \App\Policies\Banner\ContentPolicy::class,
        \App\Models\Banner\Category::class => \App\Policies\Banner\CategoryPolicy::class,
        \TomatoPHP\FilamentMediaManager\Models\Media::class => \App\Policies\MediaPolicy::class,
        \TomatoPHP\FilamentMediaManager\Models\Folder::class => \App\Policies\FolderPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
