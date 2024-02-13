<?php

declare(strict_types=1);

namespace App\Services;

final class MockData
{

    public static function getData(): array
    {
        $current_plan = [
            "plan_id" => "cb8e277d-4ecb-43f7-8b15-9ba0f6f2d381",
            "name" => "Basic Plan",
            "price" => 2.5,
            "data_quota" => 52428800,
            "data_used" => 41943040,
            "devices_limit" => 5,
            "activation_date" => 1703438093,
            "next_payment_date" => 1706448093,
            "next_reset_date" => 1703438093,
            "status" => "running"
        ];

        // {* user devices *}
        // {* status -> activated, deactivated *}
        $user_devices = [
            "devices" => [
                ["id" => "1ad98eaa-aebc-4dda-af35-1965d2c22316", "name" => "iPhone 12 Pro", "status" => "activated"],
                ["id" => "35d82401-c122-4d8f-8e18-95362923caee", "name" => "Macbook Air M2", "status" => "deactivated"],
                ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "deactivated"],
                ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "activated"],
                ["id" => "c1201e1d-617f-4ed3-a65d-ff4bb7927d54", "name" => "Samsung Galaxy A1", "status" => "activated"]
            ],
            "activated_count" => 3,
            "limited_count" => 5
        ];

        // {* user balance info *}
        $user_balance = [
            "balance" => 15,
            "expected_suffice_till" => 1706448093
        ];

        // {* user account info *}
        $account_info = [
            "user_id" => "a24990d1-9254-455f-a26d-f776e8d86536",
            "OAuth" => [
                "google" => ["activated" => false],
                "apple" => ["activated" => true],
                "telegram" => ["username" => "telegramusername", "activated" => true]
            ],
            "email" => "email@gmail.com",
            "join_data" => 1703438093
        ];

        // {* user notification setting *}
        $notification_setting = [
            "email" => true,
            "telegram" => false
        ];

        // {* user billing history *}
        $billing_history = [
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "plan_name" => "Basic Plan", "price" => 2.5, "status" => "success"]
        ];

        // {* user billing detail *}
        $billing_detail = [
            "id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc",
            "date" => 1703438093,
            "plan_name" => "Basic Plan",
            "price" => 2.5,
            "status" => "success",
            "payment_method" => "cryptomus"
        ];

        // {* user top up & withdraw history *}
        $balance_history = [
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "withdraw"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "top_up"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "top_up"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "top_up"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "top_up"],
            ["id" => "441d92a5-be88-46ed-a7e0-114ee8e40ebc", "date" => 1703438093, "amount" => 2.5, "type" => "top_up"]
        ];

        // {* available plan list *}
        $available_plans = [
            [
                "id" => "1ad98eaa-aebc-4dda-af35-196512c22316",
                "name" => "Starter",
                "price" => 1.5,
                "devices_limit" => 5,
                "description" => "Pay 1 month at a time <br/> VAT may apply",
                "features" => [
                    ["include" => true, "item" => "10GB per month include"],
                    ["include" => true, "item" => "Up to 2 devices"],
                    ["include" => true, "item" => "Cancel & refund anytime"],
                    ["include" => false, "item" => "Money-back if blocked"]
                ]
            ],
            [
                "id" => "cb8e277d-4ecb-43f7-8b15-9ba0f6f2d381",
                "name" => "Basic",
                "price" => 2.5,
                "devices_limit" => 5,
                "description" => "Pay 1 month at a time <br/> VAT may apply",
                "features" => [
                    ["include" => true, "item" => "10GB per month include"],
                    ["include" => true, "item" => "Up to 2 devices"],
                    ["include" => true, "item" => "Cancel & refund anytime"],
                    ["include" => true, "item" => "Money-back if blocked"]
                ]
            ],
            [
                "id" => "1ad98eaa-aebc-4dda-af35-1965d2322316",
                "name" => "Progress",
                "price" => 3.5,
                "devices_limit" => 5,
                "description" => "Pay 1 month at a time <br/> VAT may apply",
                "features" => [
                    ["include" => true, "item" => "10GB per month include"],
                    ["include" => true, "item" => "Up to 2 devices"],
                    ["include" => true, "item" => "Cancel & refund anytime"],
                    ["include" => true, "item" => "Money-back if blocked"]
                ]
            ]
        ];

        // {* announcement list *}
        $announcements = [
            ["id" => "4f6ae007-88b5-48d0-b134-b901cca6b7bc", "create_date" => 1703438093, "title" => "Yo, wazzup", "content" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "4f6ae007-88b5-48d0-b134-b901cca6b7bc", "create_date" => 1703438093, "title" => "Yo, wazzup", "content" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "4f6ae007-88b5-48d0-b134-b901cca6b7bc", "create_date" => 1703438093, "title" => "Yo, wazzup", "content" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"]
        ];

        // {* faq list *}
        $faq_list = [
            ["id" => "1122", "create_date" => 1703438093, "question" => "Lorem ipsum dolor sit amet, adipiscing elit. Etiam nec blandit dolor?", "answer" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "3232", "create_date" => 1703438093, "question" => "Lorem ipsum dolor sit amet, adipiscing elit. Etiam nec blandit dolor?", "answer" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "435", "create_date" => 1703438093, "question" => "Lorem ipsum dolor sit amet, adipiscing elit. Etiam nec blandit dolor?", "answer" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "231", "create_date" => 1703438093, "question" => "Lorem ipsum dolor sit amet, adipiscing elit. Etiam nec blandit dolor?", "answer" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
            ["id" => "12321", "create_date" => 1703438093, "question" => "Lorem ipsum dolor sit amet, adipiscing elit. Etiam nec blandit dolor?", "answer" => "Lorem ipsum dolor sit amet, vim at ancillae maiestatis, id veri ullum usu, nec viderer platonem disputando ea. Vis ex legendos posidonium. Postea nemore singulis no pro. Vel ex praesent intellegat incorrupte, mei eu facilis verterem expetenda. Te quot mollis temporibus vis, mea an discere alterum ancillae. Ut summo error nullam per. Probo voluptaria per ne, cum adipisci reprimique ei, voluptatum repri"],
        ];

        $mock_data = [
            "current_plan" => $current_plan,
            "user_devices" => $user_devices,
            "user_balance" => $user_balance,
            "account_info" => $account_info,
            "notification_setting" => $notification_setting,
            "billing_history" => $billing_history,
            "billing_detail" => $billing_detail,
            "balance_history" => $balance_history,
            "available_plans" => $available_plans,
            "announcements" => $announcements,
            "faq_list" => $faq_list
        ];

        return $mock_data;
    }
}
