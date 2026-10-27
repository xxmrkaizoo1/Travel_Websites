<?php

namespace App\Http\Requests\Admin;

use App\Enums\BookingStatus;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    /**
     * Admin-only routes already require an administrator.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_admin;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')->where('is_admin', false)],
            'destination' => ['required', 'string', 'max:120'],
            'package_name' => ['nullable', 'string', 'max:160'],
            'travel_date' => ['required', 'date'],
            'return_date' => ['nullable', 'date', 'after_or_equal:travel_date'],
            'adults' => ['required', 'integer', 'min:1', 'max:200'],
            'children' => ['nullable', 'integer', 'min:0', 'max:200'],
            'amount' => ['nullable', 'required_if:status,confirmed,completed', 'numeric', 'min:0', 'max:9999999.99'],
            'status' => ['required', Rule::enum(BookingStatus::class)],
            'notes' => ['nullable', 'string', 'max:2000'],
            'enquiry_id' => ['nullable', 'integer', Rule::exists('enquiries', 'id')->whereNull('booking_id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'user_id.required' => 'Choose the client this booking is for.',
            'user_id.exists' => 'Choose a client account from the list.',
            'return_date.after_or_equal' => 'The return date cannot be before the travel date.',
            'amount.required_if' => 'Enter the final amount before confirming or completing the booking.',
            'enquiry_id.exists' => 'That enquiry has already been turned into a booking.',
        ];
    }

    /**
     * Booking attributes, without the enquiry link.
     *
     * @return array<string, mixed>
     */
    public function bookingData(): array
    {
        return [...$this->safe()->except('enquiry_id'), 'children' => (int) $this->validated('children', 0)];
    }
}
