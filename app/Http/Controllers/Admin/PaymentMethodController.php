<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController
{
    /**
     * Display a listing of payment methods
     */
    public function index()
    {
        $methods = PaymentMethod::orderBy('display_order')->get();
        return view('admin.payment-methods.index', compact('methods'));
    }

    /**
     * Show the form for creating a new payment method
     */
    public function create()
    {
        return view('admin.payment-methods.create');
    }

    /**
     * Store a newly created payment method in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods',
            'description' => 'required|string',
            'placeholder' => 'required|string',
            'display_number' => 'nullable|string|max:255',
            'display_order' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'required|boolean',
        ]);

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $iconPath = $request->file('icon')->store('payment-methods', 'public');
            $validated['icon'] = $iconPath;
        }

        PaymentMethod::create($validated);

        return redirect()->route('admin.payment-methods.index')
                        ->with('success', 'Payment method created successfully.');
    }

    /**
     * Show the form for editing the specified payment method
     */
    public function edit(PaymentMethod $paymentMethod)
    {
        return view('admin.payment-methods.edit', compact('paymentMethod'));
    }

    /**
     * Update the specified payment method in storage
     */
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:payment_methods,name,' . $paymentMethod->id,
            'description' => 'required|string',
            'placeholder' => 'required|string',
            'display_number' => 'nullable|string|max:255',
            'display_order' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'is_active' => 'required|boolean',
        ]);

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon if exists
            if ($paymentMethod->icon && \Storage::disk('public')->exists($paymentMethod->icon)) {
                \Storage::disk('public')->delete($paymentMethod->icon);
            }
            $iconPath = $request->file('icon')->store('payment-methods', 'public');
            $validated['icon'] = $iconPath;
        }

        $paymentMethod->update($validated);

        return redirect()->route('admin.payment-methods.index')
                        ->with('success', 'Payment method updated successfully.');
    }

    /**
     * Remove the specified payment method from storage
     */
    public function destroy(PaymentMethod $paymentMethod)
    {
        // Delete icon if exists
        if ($paymentMethod->icon && \Storage::disk('public')->exists($paymentMethod->icon)) {
            \Storage::disk('public')->delete($paymentMethod->icon);
        }

        $paymentMethod->delete();

        return redirect()->route('admin.payment-methods.index')
                        ->with('success', 'Payment method deleted successfully.');
    }
}
