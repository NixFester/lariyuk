<?php

namespace App\Console\Commands;

use App\Models\ApologyToken;
use Illuminate\Console\Command;

class GenerateApologyTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apology:generate-tokens';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Generate one-time re-registration tokens for all emails in emails.txt';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $emailsFile = base_path('emails.txt');
        
        if (!file_exists($emailsFile)) {
            $this->error("❌ emails.txt not found at project root!");
            return 1;
        }

        $emails = array_filter(array_map('trim', file($emailsFile)));
        $totalEmails = count($emails);

        if ($totalEmails === 0) {
            $this->error("❌ No emails found in emails.txt!");
            return 1;
        }

        $this->info("🚀 Starting token generation...");
        $this->info("📧 Total emails to process: $totalEmails\n");

        $tokenMapping = [];
        $success = 0;
        $failed = 0;

        $bar = $this->output->createProgressBar($totalEmails);
        $bar->start();

        foreach ($emails as $email) {
            try {
                // Validate email
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->line("\n⚠️  Invalid email: $email");
                    $failed++;
                    $bar->advance();
                    continue;
                }

                // Check if token already exists for this email
                $existing = ApologyToken::where('email', $email)->first();
                if ($existing && !$existing->used) {
                    $this->line("\nℹ️  Token already exists for $email (not used yet)");
                    $success++;
                    $tokenMapping[$email] = $existing->token;
                    $bar->advance();
                    continue;
                }

                // Generate new token
                $token = \Str::random(32);

                // Create token record
                ApologyToken::create([
                    'email' => $email,
                    'token' => $token,
                    'expires_at' => now()->addDays(7),
                ]);

                $tokenMapping[$email] = $token;
                $success++;
                $bar->advance();

            } catch (\Exception $e) {
                $this->line("\n❌ Error for $email: " . $e->getMessage());
                $failed++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line("\n");

        // Display summary
        $this->info("✅ Token generation complete!");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->line("Success: $success");
        $this->line("Failed: $failed");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Save mapping to file
        $this->saveTokenMapping($tokenMapping);

        // Show sample links
        $this->info("\n📋 Sample Re-registration Links:");
        $this->line("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        
        $sampleCount = min(5, count($tokenMapping));
        foreach (array_slice($tokenMapping, 0, $sampleCount) as $email => $token) {
            $url = route('checkout.reregister', $token);
            $this->line("📧 $email");
            $this->line("   🔗 $url\n");
        }

        if (count($tokenMapping) > 5) {
            $this->line("... and " . (count($tokenMapping) - 5) . " more");
            $this->line("\n💾 Full mapping saved to: storage/apology_tokens_mapping.txt");
        }

        $this->info("\n✅ Next steps:");
        $this->line("1. Review the mapping file in storage/apology_tokens_mapping.txt");
        $this->line("2. Copy the re-registration links");
        $this->line("3. Send apology emails via cPanel");
        $this->line("4. Use APOLOGY_EMAIL_TEMPLATE.md for email content\n");

        return 0;
    }

    /**
     * Save token mapping to file for reference
     */
    private function saveTokenMapping(array $tokenMapping): void
    {
        $output = "EMAIL | TOKEN | RE-REGISTRATION URL\n";
        $output .= str_repeat("━", 120) . "\n";

        foreach ($tokenMapping as $email => $token) {
            $url = route('checkout.reregister', $token);
            $output .= "$email | $token | $url\n";
        }

        $output .= "\n\n";
        $output .= "GENERATED AT: " . now()->toDateTimeString() . "\n";
        $output .= "TOTAL TOKENS: " . count($tokenMapping) . "\n";
        $output .= "TOKEN EXPIRY: 7 days from now\n";

        $filePath = storage_path('apology_tokens_mapping.txt');
        file_put_contents($filePath, $output);

        $this->info("\n💾 Token mapping saved to: $filePath");
    }
}
