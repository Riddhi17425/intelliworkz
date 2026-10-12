New inquiry from the website

Name:    {{ $inquiry->name }}
Company: {{ $inquiry->company ?: '—' }}
E-mail:  {{ $inquiry->email }}
Phone:   {{ $inquiry->phone ?: '—' }}
Page:    {{ $inquiry->source ?: '—' }}
Subject: {{ $inquiry->subject ?: '—' }}

Message:
{{ $inquiry->message ?: '—' }}

Open in the admin panel: {{ route('admin.inquiries.show', $inquiry) }}
