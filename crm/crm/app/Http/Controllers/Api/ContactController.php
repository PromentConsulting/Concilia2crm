<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $q = Contact::query()->with('account');

        if ($request->filled('search')) {
            $s = $request->string('search');
            $q->where(function($qq) use ($s) {
                $qq->where('first_name', 'like', "%{$s}%")
                   ->orWhere('last_name', 'like', "%{$s}%")
                   ->orWhere('email', 'like', "%{$s}%")
                   ->orWhere('job_title', 'like', "%{$s}%");
            });
        }

        return $q->paginate(25);
    }

    public function store(StoreContactRequest $request)
    {
        $contact = Contact::create($request->validated());
        return response()->json($contact, 201);
    }

    public function show(Contact $contact)
    {
        $contact->load(['account','additionalEmails','phones','categories']);
        return $contact;
    }

    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $contact->update($request->validated());
        return $contact;
    }

    public function destroy(Contact $contact)
    {
        $contact->delete();
        return response()->noContent();
    }
}
