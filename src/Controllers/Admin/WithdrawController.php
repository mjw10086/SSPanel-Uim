<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Withdraw;
use App\Utils\Tools;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Slim\Http\Response;
use Slim\Http\ServerRequest;
use function in_array;
use function json_decode;
use function time;

final class WithdrawController extends BaseController
{
    private static array $details = [
        'field' => [
            'op' => 'Operation',
            'status' => 'Withdrawal Status',
            'id' => 'Withdrawal ID',
            'user_id' => 'User ID',
            'transfer_id' => 'Transfer ID',
            'type' => 'Withdrawal Type',
            'amount' => 'Amount',
            'to_account' => 'Destination Account',
            'addition_msg' => 'Additional Message',
            'note' => 'Note',
            'create_time' => 'Creation Time',
            'update_time' => 'Update Time'
        ],
    ];

    /**
     * @throws Exception
     */
    public function index(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        return $response->write(
            $this->view()
                ->assign('details', self::$details)
                ->fetch('admin/withdraw/index.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function detail(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $id = $args['id'];
        $withdraw = (new Withdraw())->find($id);

        return $response->write(
            $this->view()
                ->assign('withdraw', $withdraw)
                ->fetch('admin/withdraw/view.tpl')
        );
    }

    /**
     * @throws Exception
     */
    public function update(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $id = $args['id'];
        $status = $this->antiXss->xss_clean($request->getParam('status'));
        $transfer_id = $this->antiXss->xss_clean($request->getParam('transfer_id')) ?? "";
        $note = $this->antiXss->xss_clean($request->getParam('note')) ?? "";

        $withdraw = (new Withdraw())->where('id', $id)->first();
        $withdraw->status = $status ?? $withdraw->status;
        $withdraw->transfer_id = $transfer_id;
        $withdraw->note = $note;
        $withdraw->save();

        return $response->withJson([
            'ret' => 1,
            'msg' => '更新成功',
        ]);
    }

    public function ajax(ServerRequest $request, Response $response, array $args): Response|ResponseInterface
    {
        $withdraws = (new Withdraw())->orderByRaw("
            CASE
                WHEN status = 'pending' THEN 1
                WHEN status = 'rejected' THEN 2
                WHEN status = 'success' THEN 3
                ELSE 4
            END
        ")->get();

        foreach ($withdraws as $withdraw) {
            if ($withdraw->status === 'pending') {
                $withdraw->status = '<span class="status status-orange">' . $withdraw->status . '</span>';
            } else if ($withdraw->status === 'rejected') {
                $withdraw->status = '<span class="status status-red">' . $withdraw->status . '</span>';
            } else if ($withdraw->status === 'success') {
                $withdraw->status = '<span class="status status-green">' . $withdraw->status . '</span>';
            }
            $withdraw->op .= '
            <a class="btn btn-blue" href="/admin/withdraw/' . $withdraw->id . '/view">Operation</a>';
        }

        return $response->withJson([
            'withdraws' => $withdraws,
        ]);
    }
}
