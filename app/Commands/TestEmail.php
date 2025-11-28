<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Libraries\EmailService;

/**
 * Command to test SendGrid email configuration
 *
 * Usage: php spark test:email your@email.com
 */
class TestEmail extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:email';
    protected $description = 'Send a test email to verify SendGrid configuration';
    protected $usage       = 'test:email [email_address]';
    protected $arguments   = [
        'email_address' => 'The email address to send the test email to'
    ];

    public function run(array $params)
    {
        // Get email from parameter or prompt
        $toEmail = $params[0] ?? CLI::prompt('Enter email address to send test email');

        if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
            CLI::error('Invalid email address provided');
            return;
        }

        CLI::write('Sending test email to: ' . $toEmail, 'yellow');
        CLI::newLine();

        // Create email service
        $emailService = new EmailService();

        // Send test email
        if ($emailService->sendTestEmail($toEmail)) {
            CLI::write('✓ Test email sent successfully!', 'green');
            CLI::write('Check your inbox at: ' . $toEmail, 'cyan');
            CLI::newLine();
            CLI::write('SendGrid configuration is working correctly', 'green');
        } else {
            CLI::error('✗ Failed to send test email');
            CLI::newLine();
            CLI::write('Error details:', 'red');
            CLI::write($emailService->getLastError());
            CLI::newLine();
            CLI::write('Please check:', 'yellow');
            CLI::write('  - SendGrid API Key is valid');
            CLI::write('  - .env file configuration');
            CLI::write('  - Internet connection');
        }
    }
}
