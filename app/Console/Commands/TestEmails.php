<?php

namespace App\Console\Commands;

use App\Mail\ApplicationConfirmation;
use App\Mail\ApplicationNotification;
use App\Mail\LostAndFoundConfirmation;
use App\Mail\LostAndFoundNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmails extends Command
{
    protected $signature = 'mail:test {email : The email address to send test emails to} {--type= : Send only a specific type (application-confirmation, application-notification, lost-found-confirmation, lost-found-notification)}';

    protected $description = 'Send test emails with sample data';

    public function handle(): int
    {
        $email = $this->argument('email');
        $type = $this->option('type');

        $applicationData = [
            'firstname' => 'Max',
            'lastname' => 'Muster',
            'email' => 'max.muster@example.com',
            'phone' => '079 123 45 67',
            'street' => 'Musterstrasse 123',
            'zip' => '8000',
            'city' => 'Zürich',
            'german_skills' => 'Muttersprache',
            'permit' => 'Schweizer:in',
            'job_title' => 'Busfahrer/in 100%',
            'entry_id' => 'test-entry-id',
        ];

        $lostFoundData = [
            'firstname' => 'Anna',
            'lastname' => 'Beispiel',
            'email' => 'anna.beispiel@example.com',
            'phone' => '078 987 65 43',
            'date' => '30.01.2026',
            'time' => '14:30',
            'bus_line' => 'VBG 640',
            'description' => 'Blauer Regenschirm mit Holzgriff, vergessen auf dem hinteren Sitz.',
        ];

        $sent = [];

        if (!$type || $type === 'application-confirmation') {
            Mail::to($email)->send(new ApplicationConfirmation($applicationData));
            $sent[] = 'Application Confirmation';
        }

        if (!$type || $type === 'application-notification') {
            Mail::to($email)->send(new ApplicationNotification($applicationData));
            $sent[] = 'Application Notification';
        }

        if (!$type || $type === 'lost-found-confirmation') {
            Mail::to($email)->send(new LostAndFoundConfirmation($lostFoundData));
            $sent[] = 'Lost & Found Confirmation';
        }

        if (!$type || $type === 'lost-found-notification') {
            Mail::to($email)->send(new LostAndFoundNotification($lostFoundData));
            $sent[] = 'Lost & Found Notification';
        }

        foreach ($sent as $mailType) {
            $this->info("✓ Sent: {$mailType}");
        }

        $this->newLine();
        $this->info("Test emails sent to: {$email}");

        return Command::SUCCESS;
    }
}
