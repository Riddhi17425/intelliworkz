{{-- Side enquiry button: a tab fixed to the right edge of every page that slides a short enquiry form in from the right.
     Texts and the on/off switch live in Admin > Header & Footer content ("Side enquiry button"); the form posts to the same
     inquiries.store route as the contact page, so enquiries land in Admin > Inquiries tagged "side-<page>". Not shown on the contact page, which has the full form. --}}
@php
    $qeG = isset($G) ? $G : fn (string $k, string $d = '') => $d;
    $qeSlug = isset($page) ? (string) $page->slug : '';
    $qeTopics = collect();
    foreach (['services', 'products'] as $h) {
        if (!empty($footerMenu[$h])) {
            $qeTopics = $h === 'products' ? $qeTopics->push($footerMenu[$h]->title) : $qeTopics->merge($footerMenu[$h]->items->pluck('label'));
        }
    }
    $qeTopics = $qeTopics->map(fn ($t) => trim(strip_tags(html_entity_decode((string) $t))))->filter()->unique()->values();
@endphp
@if((string) $qeG('enquiry.show', '1') !== '0' && $qeSlug !== 'contact-us')
<button type="button" class="qe-tab" aria-controls="quick-enquiry" aria-expanded="false" data-qe-open>
    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12a8.5 8.5 0 0 1-12.3 7.6L3 21l1.4-5.4A8.5 8.5 0 1 1 21 12z"/><path d="M8.5 10.5h7M8.5 14h4.5"/></svg>
    <span>{!! $qeG('enquiry.1', 'Enquire Now') !!}</span>
</button>

<div class="qe-backdrop" data-qe-close hidden></div>
<aside class="qe-panel" id="quick-enquiry" role="dialog" aria-modal="true" aria-labelledby="qe-title" aria-hidden="true">
    <div class="qe-panel__head">
        <div>
            <p class="qe-panel__eyebrow">{!! $qeG('enquiry.5', 'Quick Enquiry') !!}</p>
            <h2 class="qe-panel__title" id="qe-title">{!! $qeG('enquiry.2', 'Let’s talk about your project') !!}</h2>
        </div>
        <button type="button" class="qe-panel__close" aria-label="Close" data-qe-close>&times;</button>
    </div>
    <p class="qe-panel__text">{!! $qeG('enquiry.3', 'Share a few details and our team will get back to you within one working day.') !!}</p>

    <form class="qe-form" action="{{ route('inquiries.store') }}" method="post" data-inquiry-form novalidate>
        @csrf
        <input type="hidden" name="source" value="{{ \Illuminate\Support\Str::limit('side-' . (\Illuminate\Support\Str::slug($qeSlug) ?: 'site'), 110, '') }}">
        <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hp-field" aria-hidden="true">
        <label class="qe-field"><span>Full Name <i>*</i></span><input type="text" name="name" placeholder="Enter your name" autocomplete="name"></label>
        <div class="qe-row">
            <label class="qe-field"><span>Email ID <i>*</i></span><input type="email" name="email" placeholder="you@company.com" autocomplete="email"></label>
            <label class="qe-field"><span>Contact Number <i>*</i></span><input type="tel" name="phone" placeholder="+91 98765 43210"></label>
        </div>
        <label class="qe-field"><span>Company Name</span><input type="text" name="company" placeholder="Your company" autocomplete="organization"></label>
        @if($qeTopics->isNotEmpty())
        <label class="qe-field"><span>I’m interested in</span>
            <select name="subject">
                <option value="">Choose a service</option>
                @foreach($qeTopics as $t)<option value="{{ $t }}">{{ $t }}</option>@endforeach
                <option value="Something else">Something else</option>
            </select>
        </label>
        @endif
        <label class="qe-field"><span>Your Message</span><textarea name="message" rows="3" placeholder="Tell us briefly what you need"></textarea></label>
        <button type="submit" class="btn btn--cta qe-form__send">{!! $qeG('enquiry.4', 'Send Enquiry') !!}</button>
        <p class="qe-form__privacy"><svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="4" y="11" width="16" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>We respect your privacy. Your information is safe with us.</p>
    </form>
</aside>
@endif
