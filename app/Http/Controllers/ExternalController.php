<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ExternalController extends Controller
{
    /**
     * Show the external website home page
     *
     * @return \Illuminate\View\View
     */
    public function home()
    {
        return view('external.home');
    }

    /**
     * Show the about page
     *
     * @return \Illuminate\View\View
     */
    public function about()
    {
        return view('external.about');
    }

    /**
     * Show the collections page
     *
     * @return \Illuminate\View\View
     */
    public function collections()
    {
        return view('external.collections');
    }

    /**
     * Show the services page
     *
     * @return \Illuminate\View\View
     */
    public function services()
    {
        return view('external.services');
    }

    /**
     * Show the contact page
     *
     * @return \Illuminate\View\View
     */
    public function contact()
    {
        return view('external.contact');
    }

    /**
     * Handle contact form submission
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:1000',
        ], [
            'firstName.required' => 'First name is required.',
            'lastName.required' => 'Last name is required.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'subject.required' => 'Please select a subject.',
            'message.required' => 'Message is required.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Here you would typically send an email or save to database
            // For now, we'll just simulate success
            
            // Example email sending (uncomment when mail is configured):
            /*
            Mail::send('emails.contact', $request->all(), function ($message) use ($request) {
                $message->to('info@elegantjewelry.com.au')
                        ->subject('New Contact Form Submission - ' . $request->subject)
                        ->from($request->email, $request->firstName . ' ' . $request->lastName);
            });
            */

            return redirect()->back()->with('success', 
                'Thank you for your message! We will get back to you within 24 hours.');
                
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 
                'Sorry, there was an error sending your message. Please try again or call us directly.')
                ->withInput();
        }
    }

    /**
     * Show the help page
     *
     * @return \Illuminate\View\View
     */
    public function help()
    {
        return view('external.help');
    }

    /**
     * Show the size guide page
     *
     * @return \Illuminate\View\View
     */
    public function sizeGuide()
    {
        return view('external.size-guide');
    }

    /**
     * Show the care instructions page
     *
     * @return \Illuminate\View\View
     */
    public function careInstructions()
    {
        return view('external.care-instructions');
    }

    /**
     * Show the warranty page
     *
     * @return \Illuminate\View\View
     */
    public function warranty()
    {
        return view('external.warranty');
    }

    /**
     * Show the privacy policy page
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('external.privacy');
    }

    /**
     * Show the terms of service page
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('external.terms');
    }
}