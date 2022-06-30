<?php

namespace App\Cabinet\Channel\Controllers;

use App\Domain\Account\Models\Account;
use App\Domain\Channel\Models\Channel;
use App\Domain\User\Models\User;
use App\Support\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class ChannelsController.
 */
class ChannelsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $channels = User::current()->channels;

        return view(
            'pages.channels',
            [
                'channels' => $channels,
            ]
        );
    }

    protected function store($accountId, Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|max:191',
            ]
        );

        $data = $request->all();
        $channel = $request->id ? Channel::find($request->id) : new Channel();
        $channel->account_id = Account::current()->id;
        $channel->fill($data);

        if ($channel->save()) {
            return redirect()->back()->withMessage(
                "Аккаунт {$channel->name} успешно ".($request->id ? 'добавлен' : 'отредактирован')
            );
        }

        return redirect()->back()->withMessage('Ошибка добавления аккаунта. Попробуйте позже');
    }

    public function edit($accountId, $id)
    {
        $channel = Channel::find($id);

        if (! $channel) {
            return response()->json(
                [
                    'status' => 'error',
                    'data' => [
                        'message' => 'Канал не существует',
                    ],
                ]
            );
        }

        return response()->json(
            [
                'status' => 'success',
                'data' => [
                    'channel' => [
                        'id' => $channel->id,
                        'name' => $channel->name,
                        'slug' => $channel->slug,
                    ],
                ],
            ]
        );
    }

    public function destroy(Request $request)
    {
        if (! $request->has('id')) {
            redirect('channels')->withMessage('Id канала не найден.');
        }

        $channel = Channel::find($request->id);

        if (! $channel) {
            redirect('channels')->withMessage('Возникла ошибка при обновлении канала.');
        }

        $channel->delete();

        return 'Deleted';
    }
}
