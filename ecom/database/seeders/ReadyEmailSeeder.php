<?php

namespace Database\Seeders;

use App\Models\ReadyEmail;
use Illuminate\Database\Seeder;

class ReadyEmailSeeder extends Seeder
{
    public function run(): void
    {
        ReadyEmail::create([
            'inquiry_id' => 1,
            'customer_id' => 1,
            'title' => 'Return Request - Laptop Dead Pixel',
            'email' => "Dear Customer,\n\nThank you for reaching out regarding your recent laptop purchase. We apologize for the inconvenience caused by the dead pixel issue.\n\nWe have processed your return request. Please follow these steps:\n\n1. Pack the laptop in its original box with all accessories.\n2. Attach the included return label to the package.\n3. Drop it off at your nearest shipping location.\n\nOnce we receive and inspect the item, your refund will be processed within 5-7 business days.\n\nIf you have any questions, feel free to reply to this email.\n\nBest regards,\nCustomer Support Team",
            'email_sent' => true,
        ]);

        ReadyEmail::create([
            'inquiry_id' => 2,
            'customer_id' => 1,
            'title' => 'Shipping Delay - Order Status Update',
            'email' => "Dear Customer,\n\nThank you for your patience regarding your recent order.\n\nWe have checked the shipping status and unfortunately your package is experiencing a delay due to unforeseen logistics issues. We have escalated this to our shipping partner.\n\nYour new estimated delivery date is within the next 3-5 business days. We will notify you once the package is on its way.\n\nAs a token of apology, we have applied a 10% discount coupon to your account for your next purchase.\n\nBest regards,\nCustomer Support Team",
            'email_sent' => true,
        ]);

        ReadyEmail::create([
            'inquiry_id' => 4,
            'customer_id' => 1,
            'title' => 'Duplicate Payment - Refund Initiated',
            'email' => "Dear Customer,\n\nWe have reviewed your account and confirmed that a duplicate charge was made on your last order.\n\nWe have initiated a full refund for the duplicate payment. The amount will reflect in your account within 5-10 business days depending on your bank.\n\nWe sincerely apologize for this error and have taken steps to prevent it from happening again.\n\nBest regards,\nBilling Support Team",
            'email_sent' => false,
        ]);

        ReadyEmail::create([
            'inquiry_id' => 5,
            'customer_id' => 1,
            'title' => 'Account Access - Password Reset Assistance',
            'email' => "Dear Customer,\n\nWe are sorry to hear that you are having trouble resetting your password.\n\nWe have manually triggered a password reset for your account. Please check your email inbox (and spam folder) for the reset link. The link will be valid for 24 hours.\n\nIf you continue to experience issues, please let us know and we will assist you further.\n\nBest regards,\nAccount Support Team",
            'email_sent' => false,
        ]);
    }
}