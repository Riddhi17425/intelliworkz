<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ContactInquiry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InquiryController extends Controller
{
    private const SORTS = ['created_at', 'name', 'email', 'source'];

    public function index(Request $request): View
    {
        $sort = in_array($request->query('sort'), self::SORTS, true) ? $request->query('sort') : 'created_at';
        $dir = $request->query('dir') === 'asc' ? 'asc' : 'desc';

        $inquiries = $this->filtered($request)->orderBy($sort, $dir)->orderByDesc('id')->paginate(20)->withQueryString();

        return view('admin.inquiries.index', [
            'inquiries' => $inquiries,
            'sort' => $sort,
            'dir' => $dir,
            'sources' => ContactInquiry::query()->whereNotNull('source')->distinct()->orderBy('source')->pluck('source'),
            'totals' => ['all' => ContactInquiry::count(), 'unread' => ContactInquiry::unread()->count()],
        ]);
    }

    public function show(ContactInquiry $inquiry): View
    {
        if (!$inquiry->is_read) {
            $inquiry->markRead();                // opening an inquiry counts as reading it
        }
        return view('admin.inquiries.show', [
            'inquiry' => $inquiry,
            'previous' => ContactInquiry::where('id', '>', $inquiry->id)->orderBy('id')->value('id'),
            'next' => ContactInquiry::where('id', '<', $inquiry->id)->orderByDesc('id')->value('id'),
        ]);
    }

    public function toggleRead(ContactInquiry $inquiry): RedirectResponse
    {
        $inquiry->markRead(!$inquiry->is_read);
        return back()->with('success', $inquiry->is_read ? 'Marked as read.' : 'Marked as unread.');
    }

    public function destroy(ContactInquiry $inquiry): RedirectResponse
    {
        $name = $inquiry->name;
        $inquiry->delete();
        ActivityLog::record('deleted', "Deleted inquiry from {$name}");

        return redirect()->route('admin.inquiries.index')->with('success', "The inquiry from {$name} was deleted.");
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:read,unread,delete'],
            'ids' => ['required', 'array', 'min:1', 'max:200'],
            'ids.*' => ['integer'],
        ], ['ids.required' => 'Tick at least one inquiry first.']);

        $query = ContactInquiry::whereIn('id', $data['ids']);

        if ($data['action'] === 'delete') {
            Gate::authorize('delete-content');
            $n = $query->count();
            $query->get()->each->delete();
            ActivityLog::record('deleted', "Deleted {$n} inquiries");
            return back()->with('success', "{$n} " . str('inquiry')->plural($n) . ' deleted.');
        }

        $read = $data['action'] === 'read';
        $n = $query->update(['is_read' => $read, 'read_at' => $read ? now() : null]);
        return back()->with('success', "{$n} " . str('inquiry')->plural($n) . ' marked as ' . ($read ? 'read' : 'unread') . '.');
    }

    public function export(Request $request): StreamedResponse
    {
        $query = $this->filtered($request)->orderByDesc('created_at');
        ActivityLog::record('updated', 'Exported inquiries to CSV');
        $name = 'inquiries-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");                      // BOM so Excel reads UTF-8
            fputcsv($out, ['Received', 'Name', 'Company', 'E-mail', 'Phone', 'Subject', 'Message', 'Page', 'Status']);
            $query->chunkById(200, function ($rows) use ($out) {
                foreach ($rows as $r) {
                    fputcsv($out, array_map([$this, 'csvSafe'], [
                        $r->created_at->format('Y-m-d H:i'), $r->name, $r->company, $r->email, $r->phone, $r->subject, $r->message, $r->source, $r->is_read ? 'Read' : 'Unread',
                    ]));
                }
            });
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Spreadsheet formulas typed into a form must not execute when the CSV is opened. */
    private function csvSafe(?string $value): string
    {
        $value = (string) $value;
        return ($value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) ? "'" . $value : $value;
    }

    private function filtered(Request $request): Builder
    {
        return ContactInquiry::query()
            ->search($request->query('q'))
            ->when($request->query('status') === 'unread', fn ($q) => $q->where('is_read', false))
            ->when($request->query('status') === 'read', fn ($q) => $q->where('is_read', true))
            ->when($request->filled('source'), fn ($q) => $q->where('source', $request->query('source')))
            ->when($this->date($request->query('from')), fn ($q, $d) => $q->where('created_at', '>=', $d->startOfDay()))
            ->when($this->date($request->query('to')), fn ($q, $d) => $q->where('created_at', '<=', $d->endOfDay()));
    }

    private function date(mixed $value): ?\Illuminate\Support\Carbon
    {
        try {
            return $value ? \Illuminate\Support\Carbon::parse((string) $value) : null;
        } catch (\Throwable) {
            return null;
        }
    }
}
