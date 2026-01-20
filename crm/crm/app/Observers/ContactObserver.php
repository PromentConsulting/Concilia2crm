<?php

namespace App\Observers;

use App\Models\Contact;

class ContactObserver
{
    public function creating(Contact $contact): void
    {
        // Inherit owner from account if not set
        if (!$contact->owner_user_id && $contact->account_id && $contact->account) {
            $contact->owner_user_id = $contact->account->owner_user_id;
            $contact->owner_team_id = $contact->account->owner_team_id;
        }
    }
}
