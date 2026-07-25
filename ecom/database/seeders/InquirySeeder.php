<?php

namespace Database\Seeders;

use App\Models\Inquiry;
use Illuminate\Database\Seeder;

class InquirySeeder extends Seeder
{
    public function run(): void
    {
        Inquiry::create([
            'customer_id' => 1,
            'category' => 'return',
            'inquiry' => 'I would like to return a laptop I purchased last week. The screen has a dead pixel and I want a full refund.',
            'treated' => true,
        ]);

        Inquiry::create([
            'customer_id' => 1,
            'category' => 'shipping',
            'inquiry' => 'My order has been delayed for over a week. Can you check the shipping status and provide an updated delivery date?',
            'treated' => true,
        ]);

        Inquiry::create([
            'customer_id' => 1,
            'category' => 'product',
            'inquiry' => 'Do you have this product available in blue color? I checked the website and only saw black and white options.',
            'treated' => false,
        ]);

        Inquiry::create([
            'customer_id' => 1,
            'category' => 'payment',
            'inquiry' => 'I was charged twice for my last order. Please refund the duplicate payment as soon as possible.',
            'treated' => false,
        ]);

        Inquiry::create([
            'customer_id' => 1,
            'category' => 'account',
            'inquiry' => 'I forgot my password and the reset link is not being sent to my email. Can you help me regain access to my account?',
            'treated' => false,
        ]);
    }
}