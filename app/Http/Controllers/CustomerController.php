<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Customer::query()->latest()->paginate(15));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', 'unique:customers,user_id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'max:30', 'unique:customers,phone'],
            'password' => ['nullable', 'string', 'min:8'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $customer = Customer::query()->create($validated);

        return response()->json(['message' => 'Customer created.', 'data' => $customer], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return response()->json($customer->load(['shipments.status', 'payments']));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'user_id' => ['nullable', 'exists:users,id', 'unique:customers,user_id,' . $customer->id],
            'name' => ['sometimes', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', 'unique:customers,email,' . $customer->id],
            'phone' => ['sometimes', 'string', 'max:30', 'unique:customers,phone,' . $customer->id],
            'password' => ['nullable', 'string', 'min:8'],
            'address' => ['nullable', 'string'],
            'city' => ['nullable', 'string', 'max:100'],
            'photo' => ['nullable', 'string', 'max:255'],
        ]);

        $customer->update($validated);

        return response()->json(['message' => 'Customer updated.', 'data' => $customer->fresh()]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return response()->json(['message' => 'Customer deleted.']);
    }
}
