<?php

namespace App\Http\Controllers;

use App\Mail\NewInquiry;
use App\Models\ActivityLog;
use App\Models\ContactInquiry;
use App\Models\SiteSetting;
use App\Support\Html;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/** Receives the enquiry / contact form of every page. */
class InquiryController extends Controller
{
    public const SUCCESS_MESSAGE = 'Thank you! Your message has been sent. We will get back to you shortly.';

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        // honeypot: real visitors never see or fill this field. Pretend it worked so bots learn nothing.
        if (filled($request->input('website'))) {
            return $this->ok($request);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'company' => ['nullable', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+\-\s().]{6,40}$/', function ($attr, $value, $fail) {
                // a real number has 7–15 digits (international format); "------" or "((((((" is not a number
                $digits = preg_match_all('/\d/', (string) $value);
                if ($digits < 7 || $digits > 15) {
                    $fail('Please enter a valid contact number (7 to 15 digits).');
                }
            }],
            'subject' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:3000'],
            'source' => ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9-]*$/'],
        ], [
            'name.required' => 'Please enter your name.',
            'email.required' => 'Please enter your e-mail address.',
            'email.email' => 'That e-mail address does not look right.',
            'phone.required' => 'Please enter your contact number.',
            'phone.regex' => 'Please enter a valid phone number (digits, +, spaces and dashes only).',
        ]);

        $inquiry = new ContactInquiry();
        $inquiry->fill([
            'name' => $this->plain($data['name']),
            'company' => $this->plain($data['company'] ?? null),
            'email' => $data['email'],
            'phone' => $this->plain($data['phone']),
            'subject' => $this->plain($data['subject'] ?? null),
            'message' => $this->plain($data['message'] ?? null, true),
            'source' => $data['source'] ?? null,
        ]);
        $inquiry->ip_address = $request->ip();
        $inquiry->user_agent = mb_substr((string) $request->userAgent(), 0, 255);
        $inquiry->save();

        ActivityLog::record('inquiry', "New inquiry from {$inquiry->name}" . ($inquiry->source ? " ({$inquiry->source})" : ''), $inquiry);
        $this->notify($inquiry);

        return $this->ok($request);
    }

    /** Stored as plain text; the admin and the e-mail escape it on output. */
    private function plain(?string $value, bool $keepLines = false): ?string
    {
        if ($value === null) {
            return null;
        }
        $text = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = $keepLines ? preg_replace("/[ \t]+/", ' ', str_replace("\r\n", "\n", $text)) : preg_replace('/\s+/u', ' ', $text);
        $text = trim((string) $text);
        return $text === '' ? null : $text;
    }

    private function notify(ContactInquiry $inquiry): void
    {
        $to = SiteSetting::get('contact_email');
        if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }
        try {
            Mail::to($to)->send(new NewInquiry($inquiry));
        } catch (\Throwable $e) {
            // a mail problem must never lose the visitor's message — it is already saved
            Log::error('Inquiry e-mail could not be sent: ' . $e->getMessage());
        }
    }

    private function ok(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => self::SUCCESS_MESSAGE]);
        }
        return redirect()->to(url()->previous() . '#form-result')->with('inquiry_status', self::SUCCESS_MESSAGE);
    }
}
