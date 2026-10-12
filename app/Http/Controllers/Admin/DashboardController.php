<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactInquiry;
use App\Models\ContentField;
use App\Models\ContentList;
use App\Models\MenuItem;
use App\Models\Page;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pages = Page::query()
            ->selectRaw("count(*) as total, coalesce(sum(status = 'published'), 0) as published, coalesce(sum(status <> 'published'), 0) as draft")
            ->first();

        // inquiries received per day for the last 14 days (zero-filled)
        $since = now()->subDays(13)->startOfDay();
        $perDay = ContactInquiry::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE(created_at) as d, count(*) as c')
            ->groupBy('d')->pluck('c', 'd');
        $trend = collect(range(0, 13))->map(function ($i) use ($since, $perDay) {
            $day = $since->copy()->addDays($i);
            return ['label' => $day->format('d M'), 'count' => (int) ($perDay[$day->toDateString()] ?? 0)];
        });

        $headerItems = fn () => MenuItem::whereHas('group', fn ($q) => $q->where('location', 'header'));

        return view('admin.dashboard', [
            'pages' => $pages,
            'services' => $headerItems()->count(),
            'servicesLive' => $headerItems()->where('is_published', true)->count(),
            'inquiryTotal' => ContactInquiry::count(),
            'inquiryUnread' => ContactInquiry::unread()->count(),
            'inquiryWeek' => ContactInquiry::where('created_at', '>=', now()->subDays(7))->count(),
            'fieldCount' => ContentField::count(),
            'listCount' => ContentList::count(),
            'trend' => $trend,
            'trendMax' => max(1, $trend->max('count')),
            'recentInquiries' => ContactInquiry::latest()->limit(6)->get(),
            'activity' => ActivityLog::with('user')->latest('id')->limit(8)->get(),
            'recentPages' => Page::latest('updated_at')->limit(5)->get(),
        ]);
    }
}
