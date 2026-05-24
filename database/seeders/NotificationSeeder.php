<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all customer users
        $customers = User::where('role', 'customer')->get();

        if ($customers->isEmpty()) {
            $this->command->warn('No customer users found. Please create customer users first.');
            return;
        }

        foreach ($customers as $customer) {
            // Create sample notifications matching the screenshot
            $notifications = [
                [
                    'type' => 'dataset_published',
                    'title' => 'New Dataset Published',
                    'description' => 'November 2024 dataset for ZIP 90210 is now available',
                    'icon' => 'fas fa-database',
                    'icon_color' => 'text-blue-600',
                    'is_read' => false,
                    'created_at' => now()->subDays(8), // 2024-11-14
                ],
                [
                    'type' => 'data_update',
                    'title' => 'Data Update',
                    'description' => 'October 2024 dataset has been updated with improved accuracy metrics',
                    'icon' => 'fas fa-exclamation-circle',
                    'icon_color' => 'text-green-600',
                    'is_read' => false,
                    'created_at' => now()->subDays(10), // 2024-11-12
                ],
                [
                    'type' => 'subscription_renewal',
                    'title' => 'Subscription Renewal',
                    'description' => 'Your subscription will renew on December 1, 2024',
                    'icon' => 'fas fa-calendar',
                    'icon_color' => 'text-orange-600',
                    'is_read' => true,
                    'created_at' => now()->subDays(12), // 2024-11-10
                ],
                [
                    'type' => 'platform_update',
                    'title' => 'Platform Update',
                    'description' => 'We\'ve added new filtering options to help you find properties faster',
                    'icon' => 'fas fa-bullhorn',
                    'icon_color' => 'text-purple-600',
                    'is_read' => true,
                    'created_at' => now()->subDays(14), // 2024-11-08
                ],
            ];

            foreach ($notifications as $notificationData) {
                Notification::create([
                    'user_id' => $customer->id,
                    'type' => $notificationData['type'],
                    'title' => $notificationData['title'],
                    'description' => $notificationData['description'],
                    'icon' => $notificationData['icon'],
                    'icon_color' => $notificationData['icon_color'],
                    'is_read' => $notificationData['is_read'],
                    'read_at' => $notificationData['is_read'] ? now() : null,
                    'created_at' => $notificationData['created_at'],
                    'updated_at' => $notificationData['created_at'],
                ]);
            }
        }

        $this->command->info('Sample notifications created for all customer users.');
    }
}
