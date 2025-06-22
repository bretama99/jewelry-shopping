<?php
// File: app/Http/Requests/CheckoutRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'customer_name' => 'required|string|min:2|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|min:10|max:20|regex:/^[\d\s\-\+\(\)]+$/',
            'billing_address' => 'required|string|min:10|max:500',
            'shipping_address' => 'nullable|string|min:10|max:500',
            'payment_method' => 'required|in:cash,card,bank_transfer,paypal',
            'notes' => 'nullable|string|max:1000',
            'terms_accepted' => 'required|accepted',
            'newsletter_subscribe' => 'nullable|boolean'
        ];
    }

    public function messages()
    {
        return [
            'customer_name.required' => 'Full name is required.',
            'customer_name.min' => 'Name must be at least 2 characters.',
            'customer_name.max' => 'Name cannot exceed 255 characters.',

            'customer_email.required' => 'Email address is required.',
            'customer_email.email' => 'Please enter a valid email address.',
            'customer_email.max' => 'Email cannot exceed 255 characters.',

            'customer_phone.required' => 'Phone number is required.',
            'customer_phone.min' => 'Phone number must be at least 10 digits.',
            'customer_phone.max' => 'Phone number cannot exceed 20 characters.',
            'customer_phone.regex' => 'Please enter a valid phone number.',

            'billing_address.required' => 'Billing address is required.',
            'billing_address.min' => 'Address must be at least 10 characters.',
            'billing_address.max' => 'Address cannot exceed 500 characters.',

            'shipping_address.min' => 'Shipping address must be at least 10 characters.',
            'shipping_address.max' => 'Shipping address cannot exceed 500 characters.',

            'payment_method.required' => 'Please select a payment method.',
            'payment_method.in' => 'Please select a valid payment method.',

            'notes.max' => 'Notes cannot exceed 1000 characters.',

            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate phone number format more thoroughly
            $phone = $this->input('customer_phone');
            if ($phone) {
                // Remove all non-digit characters for length check
                $digitsOnly = preg_replace('/\D/', '', $phone);

                if (strlen($digitsOnly) < 10) {
                    $validator->errors()->add('customer_phone',
                        'Phone number must contain at least 10 digits.');
                }

                if (strlen($digitsOnly) > 15) {
                    $validator->errors()->add('customer_phone',
                        'Phone number cannot contain more than 15 digits.');
                }
            }

            // Validate addresses contain essential information
            $billingAddress = $this->input('billing_address');
            if ($billingAddress) {
                if (!preg_match('/\d/', $billingAddress)) {
                    $validator->errors()->add('billing_address',
                        'Billing address should include a street number.');
                }
            }

            $shippingAddress = $this->input('shipping_address');
            if ($shippingAddress && !preg_match('/\d/', $shippingAddress)) {
                $validator->errors()->add('shipping_address',
                    'Shipping address should include a street number.');
            }

            // Validate email format more strictly
            $email = $this->input('customer_email');
            if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $validator->errors()->add('customer_email',
                    'Please enter a valid email address.');
            }
        });
    }

    protected function prepareForValidation()
    {
        // Clean up phone number
        if ($this->has('customer_phone')) {
            $phone = $this->input('customer_phone');
            // Allow digits, spaces, hyphens, plus, and parentheses
            $phone = preg_replace('/[^\d\s\-\+\(\)]/', '', $phone);
            $this->merge(['customer_phone' => $phone]);
        }

        // Clean up name
        if ($this->has('customer_name')) {
            $name = $this->input('customer_name');
            // Remove extra spaces and trim
            $name = preg_replace('/\s+/', ' ', trim($name));
            $this->merge(['customer_name' => $name]);
        }

        // Clean up email
        if ($this->has('customer_email')) {
            $email = strtolower(trim($this->input('customer_email')));
            $this->merge(['customer_email' => $email]);
        }

        // Use billing address as shipping if shipping is empty
        if (!$this->has('shipping_address') || empty($this->input('shipping_address'))) {
            $this->merge(['shipping_address' => $this->input('billing_address')]);
        }

        // Ensure newsletter_subscribe is boolean
        $this->merge(['newsletter_subscribe' => $this->boolean('newsletter_subscribe')]);
    }

    public function attributes()
    {
        return [
            'customer_name' => 'full name',
            'customer_email' => 'email address',
            'customer_phone' => 'phone number',
            'billing_address' => 'billing address',
            'shipping_address' => 'shipping address',
            'payment_method' => 'payment method',
            'terms_accepted' => 'terms and conditions',
        ];
    }
}
