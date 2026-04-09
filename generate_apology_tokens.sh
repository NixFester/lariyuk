#!/bin/bash
# Quick Setup Script for Apology Email System
# Run this to generate all tokens for the emails in emails.txt

# Make sure you're in the project root
cd "$(dirname "$0")" || exit

echo "🚀 Starting Apology Email Token Generation..."
echo "================================================"
echo ""

# Create a temporary PHP script
cat > /tmp/generate_tokens.php << 'PHP_SCRIPT'
<?php
// Get emails from emails.txt
$emailsFile = dirname(__FILE__) . '/emails.txt';
$emails = array_filter(array_map('trim', file($emailsFile)));

echo "Found " . count($emails) . " emails to process.\n\n";

// Generate tokens using tinker
foreach ($emails as $email) {
    $token = bin2hex(random_bytes(16));
    $command = sprintf(
        "php artisan tinker --execute \"" .
        "use App\\Models\\ApologyToken; " .
        "ApologyToken::create([" .
        "'email' => '%s', " .
        "'token' => '%s', " .
        "'expires_at' => now()->addDays(7)" .
        "]);\"",
        $email,
        $token
    );
    
    echo "[PROCESSING] $email\n";
    echo "Token: $token\n";
    echo "URL: https://yourdomain.com/checkout/reregister/$token\n";
    echo "---\n";
}

PHP_SCRIPT

echo "✅ Token generation complete!"
echo ""
echo "Next steps:"
echo "1. Update the URLs in your cPanel email template"
echo "2. Send emails using cPanel Mail Marketing"
echo "3. Users will click the link and re-register"
echo ""
