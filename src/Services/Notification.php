<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmailQueue;
use App\Models\User;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Client\ClientExceptionInterface;
use Telegram\Bot\Exceptions\TelegramSDKException;

final class Notification
{
    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyAdmin($title = '', $msg = '', $template = 'warn.tpl'): void
    {
        $admins = (new User())->where('is_admin', 1)->get();

        foreach ($admins as $admin) {
            if ($admin->contact_method === 1 || $admin->im_type === 0) {
                Mail::send(
                    $admin->email,
                    $title,
                    $template,
                    [
                        'user' => $admin,
                        'title' => $title,
                        'text' => $msg,
                    ]
                );
            } else {
                IM::send($admin->im_value, $msg, $admin->im_type);
            }
        }
    }

    /* @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyUserMole($user, $title = '', $msg = '', $template = 'warn.tpl'): void
    {
        if ($user->contact_method === 0) {
            return;
        }
        if ($user->contact_method === 1 || $user->im_type === 0) {
            $array = [
                'user' => $user,
                'title' => $title,
                'text' => $msg,
            ];

            (new EmailQueue())->add($user->email, $title, $template, $array);
            return;
        }
        if ($user->contact_method === 2) {
            IM::send($user->im_value, $msg, $user->im_type);
            return;
        }
        if ($user->contact_method === 3) {
            $array = [
                'user' => $user,
                'title' => $title,
                'text' => $msg,
            ];

            (new EmailQueue())->add($user->email, $title, $template, $array);

            if ($user->im_type !== 0) {
                IM::send($user->im_value, $msg, $user->im_type);
            }
            return;
        }
    }

    /* @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyUserTicket($user, $ticket, $title = '', $msg = '', $template = 'warn.tpl'): void
    {
        $array = [
            'user' => $user ?? "",
            'title' => $title,
            'text' => $msg,
            'ticket' => $ticket,
        ];

        (new EmailQueue())->add($ticket->email, $title, $template, $array);
        return;
    }


    /**
     * @throws GuzzleException
     * @throws ClientExceptionInterface
     */
    public static function notifyUserInSystem($user, $msg = ''): void
    {
        $notification = new \App\Models\Notification();
        $notification->user_id = $user->id;
        $notification->content = $msg;
        $notification->save();

        return;
    }

    /**
     * @throws GuzzleException
     * @throws ClientExceptionInterface
     */
    public static function fetchUserNotificationInSystem($user)
    {
        $list = (new \App\Models\Notification())->where('user_id', 1)->where('has_read', false)->get();

        foreach ($list as $notification) {
            $notification->update([
                'has_read' => true
            ]);
        }

        return $list;
    }
    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     * @throws ClientExceptionInterface
     */
    public static function notifyUser($user, $title = '', $msg = '', $template = 'warn.tpl'): void
    {
        if ($user->contact_method === 1 || $user->im_type === 0) {
            $array = [
                'user' => $user,
                'title' => $title,
                'text' => $msg,
            ];

            (new EmailQueue())->add($user->email, $title, $template, $array);
        } else {
            IM::send($user->im_value, $msg, $user->im_type);
        }
    }



    /**
     * @throws GuzzleException
     * @throws TelegramSDKException
     */
    public static function notifyAllUser($title = '', $msg = '', $template = 'warn.tpl'): void
    {
        $users = User::all();

        foreach ($users as $user) {
            if ($user->contact_method === 1 || $user->im_type === 0) {
                $array = [
                    'user' => $user,
                    'title' => $title,
                    'text' => $msg,
                ];

                (new EmailQueue())->add($user->email, $title, $template, $array);
            } else {
                IM::send($user->im_value, $msg, $user->im_type);
            }
        }
    }
}
