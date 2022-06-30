<?php

namespace Database\Seeders;

use App\Domain\Channel\Models\Channel;
use App\Domain\Channel\Models\ChannelReasonsOfRejection;
use App\Domain\Company\Models\Company;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AddAccountsSeeder extends Seeder
{
    /**
     * @var array
     */
    private $allChannels;
    /**
     * @var array
     */
    private $rejections;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('account_users')->truncate();
        DB::table('accounts')->truncate();

        DB::table('accounts')->insert(
            [
                'id' => 1,
                'name' => 'Информада',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('accounts')->insert(
            [
                'id' => 2,
                'name' => 'Лидогенерация Ворота',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('accounts')->insert(
            [
                'id' => 3,
                'name' => 'Лидогенератор Остекление',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('accounts')->insert(
            [
                'id' => 4,
                'name' => 'Лидогенерация Дома',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
        DB::table('accounts')->insert(
            [
                'id' => 5,
                'name' => 'Лидогенерация Потолки',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        $this->allChannels = [];
        $this->rejections = [];
        foreach (Channel::all() as $channel) {
            $this->allChannels[$channel->id] = $channel->attributesToArray();
            $this->rejections[$channel->id] = ChannelReasonsOfRejection::
            where('channel_id', $channel->id)
                ->pluck('reasons_of_rejection_id')
                ->toArray();
        }
        ChannelReasonsOfRejection::query()->delete();
        $this->moveCompaniesToAccount(Company::all(), 3);

        $user = User::findOrFail(38);
        $companiesIds = $user->getCompanyForUser(38)->pluck('id');
        $companies = Company::whereIn('id', $companiesIds)->get();
        $this->moveCompaniesToAccount($companies, 1);

        $user = User::findOrFail(34);
        $companiesIds = $user->companies->pluck('id')->toArray();
        $companies = Company::whereIn('id', $companiesIds)->get();
        $this->moveCompaniesToAccount($companies, 2);

        $user = User::where('email', '258958@56.ru')->first();
        $companiesIds = $user->companies->pluck('id')->toArray();
        $companies = Company::whereIn('id', $companiesIds)->get();
        $this->moveCompaniesToAccount($companies, 4);

        $user = User::where('email', 'mrkt.npz@gmail.com')->first();
        $companiesIds = $user->companies->pluck('id')->toArray();
        $companies = Company::whereIn('id', $companiesIds)->get();
        $this->moveCompaniesToAccount($companies, 5);

        $this->cleanChannels();

        $users = User::all();
        foreach ($users as $user) {
            if ($user->id == 2) {
                $this->addUser(2, 3, 'admin');
            } elseif ($user->id == 34) {
                $this->addUser(34, 2, 'admin');
            } elseif ($user->id == 38) {
                $this->addUser(38, 1, 'admin');
            } elseif ($user->email == '258958@56.ru') {
                $this->addUser($user->id, 4, 'admin');
            } elseif ($user->email == 'mrkt.npz@gmail.com') {
                $this->addUser($user->id, 5, 'admin');
            } else {
                $this->addUser($user->id, 3, $user->getRole()->name);
            }
        }
    }

    public function updateCompany($item_id, $account_id)
    {
        $company = Company::where('id', $item_id)->first();
        $company->account_id = $account_id;
        $company->save();
    }

    public function addUser($user_id, $account_id, $role)
    {
        DB::table('account_users')->insert(
            [
                'user_id' => $user_id,
                'account_id' => $account_id,
                'role' => $role,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }

    /**
     * @param $companies
     * @param int $accountId
     * @param $allChannels
     * @return mixed
     */
    private function moveCompaniesToAccount($companies, int $accountId)
    {
        foreach ($companies as $company) {
            $company->account_id = $accountId;
            if (! $company->channel) {
                $dispatcher = $company::getEventDispatcher();
                $company::unsetEventDispatcher();
                $company->save();
                $company::setEventDispatcher($dispatcher);
                continue;
            }
            $oldChannelId = $company->channel_id;
            $channel = Channel::where('account_id', $accountId)
                ->where('name', $company->channel->name)
                ->first();
            if (! $channel) {
                echo "Processing company #{$company->id}\n";
                $channelInfo = $this->allChannels[$company->channel_id];
                unset($channelInfo['id']);
                $channelInfo['account_id'] = $accountId;
                $channelInfo['slug'] = Str::slug($channelInfo['name']).'-'.$accountId;
                $channel = Channel::create($channelInfo);
                foreach ($this->rejections[$company->channel_id] as $rejectionId) {
                    $rj = new ChannelReasonsOfRejection();
                    $rj->channel_id = $channel->id;
                    $rj->reasons_of_rejection_id = $rejectionId;
                    $rj->save();
                }
            }
            $company->channel_id = $channel->id;
            $company->save();
            $this->allChannels[$company->channel_id] = $channel->attributesToArray();
            $this->rejections[$company->channel_id] = $this->rejections[$oldChannelId];
        }
    }

    private function cleanChannels()
    {
        Channel::where('account_id', 0)->delete();
        foreach (Channel::all() as $channel) {
            $companiesCount = Company::where('account_id', $channel->account_id)
                ->where('channel_id', $channel->id)
                ->count();
            if ($companiesCount == 0) {
                $channel->delete();
            }
        }
    }
}
