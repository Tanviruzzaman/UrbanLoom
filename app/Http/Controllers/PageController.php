<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactMessageRequest;
use App\Mail\ContactMessageMail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PageController extends Controller
{
    /**
     * Static footer pages. Placeholder copy — swap in the real policies
     * before going live.
     */
    public function about(): View
    {
        return view('pages.about');
    }

    public function contact(): View
    {
        return view('pages.contact');
    }

    public function privacy(): View
    {
        return view('pages.privacy');
    }

    public function terms(): View
    {
        return view('pages.terms');
    }

    public function returns(): View
    {
        return view('pages.returns');
    }

    /**
     * Send a contact form submission to the store owner.
     */
    public function sendContact(ContactMessageRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('website');

        try {
            Mail::to(config('store.email'))->send(new ContactMessageMail($data));
        } catch (\Throwable $e) {
            Log::error('Contact form email failed.', [
                'email' => $data['email'],
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'Sorry, we could not send your message. Please call us instead on '.config('store.phone').'.');
        }

        return redirect()
            ->route('pages.contact')
            ->with('success', 'Thanks for reaching out! We usually reply within one working day.');
    }
}
