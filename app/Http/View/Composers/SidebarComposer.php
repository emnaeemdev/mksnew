<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use App\Models\DocumentSection;
use App\Models\Post;
use App\Models\Category;
use App\Models\User;
use App\Models\Comment;
use App\Models\Document;
use App\Models\DocumentCustomField;
use App\Models\Inquiry;
use App\Models\NewsletterSubscription;
use App\Models\Nashra;
use App\Models\Podcast;

class SidebarComposer
{

    public function compose(View $view)
    {
        $view->with([
            'postsCount' => Post::count(),
            'categoriesCount' => Category::count(),
            'usersCount' => User::count(),
            'commentsCount' => Comment::count(),
            'documentsCount' => Document::count(),
            'documentSectionsCount' => DocumentSection::count(),
            'documentCustomFieldsCount' => DocumentCustomField::count(),
            'unreadInquiriesCount' => Inquiry::where('status', Inquiry::STATUS_NEW)->count(),
            'newsletterSubscriptionsCount' => NewsletterSubscription::count(),
            'nashrasCount' => Nashra::count(),
            'podcastsCount' => Podcast::count(),
            'activeSections' => DocumentSection::active()
                ->ordered()
                ->withCount('documents')
                ->get(),
            'activePostCategories' => Category::active()
                ->ordered()
                ->withCount('posts')
                ->get(),
        ]);
    }
}