<?php

namespace App\Livewire\Reminders;

use Livewire\Component;

class ReminderList extends Component
{
    public function markAsCompleted($id)
    {
        $reminder = \App\Models\Reminder::findOrFail($id);
        $reminder->update(['status' => 'completed']);
        session()->flash('reminder_message', 'Reminder marked as completed.');
    }

    public function dismiss($id)
    {
        $reminder = \App\Models\Reminder::findOrFail($id);
        $reminder->update(['status' => 'dismissed']);
        session()->flash('reminder_message', 'Reminder dismissed.');
    }

    public function render()
    {
        $reminders = \App\Models\Reminder::with(['client', 'order'])
            ->where('status', 'pending')
            ->orderBy('trigger_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.reminders.reminder-list', [
            'reminders' => $reminders
        ]);
    }
}
