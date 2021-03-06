<?php

namespace App\Http\Controllers;

use App\Http\Helpers\ApiHelper;
use App\Http\Helpers\CryptoHelper;
use App\Http\Helpers\UserHelper;
use App\Models\ApiRequest;
use App\Models\Game;
use App\Models\GameModule;
use App\Models\Subscription;
use App\Models\SubscriptionSettings;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class apiController extends Controller
{

    public function login(){
        $response = [
            'code' => env('API_CODE_UNKNOWN_ERROR'),
            'data' => null,
        ];

        $email = @$_POST['email'];
        $password = @$_POST['password'];
        $hwid = @$_POST['hwid'];
        $game_id = @$_POST['game_id'];

        if(!$email || !$password || !$hwid){
            http_response_code(403);
            die();
        }

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $current_time = time();

        $user = @User::where('email', $email)->where('password', $password)->get()->first();
        if(!$user){
            $response['code'] = env('API_CODE_USER_NOT_FOUND');
            return json_encode($response);
        }

        $user_settings = UserSettings::where('user_id', $user->id)->get()->first();
        if(!UserHelper::CheckUserActivity($user)){
            $response['code'] = env('API_CODE_USER_BLOCKED');
            return json_encode($response);
        }

        $game = @Game::where('id', $game_id)->get()->first();
        if(!$game){
            $response['code'] = env('API_CODE_GAME_NOT_FOUND');
            return json_encode($response);
        }

        $user_subscription = @Subscription::where('user_id', $user->id)->where('game_id', $game_id)->get()->first();
        if(!$user_subscription){
            $response['code'] = env('API_CODE_SUBSCRIPTION_EXPIRY');
            return json_encode($response);
        }

        if($user_subscription->hwid && $user_subscription->hwid != $hwid){
            $response['code'] = env('API_CODE_HWID_ERROR');
            return json_encode($response);
        }

        if(!$user_subscription->hwid){
            if(count(Subscription::where('game_id', $user_subscription->game_id)->where('hwid', $hwid)->get()) >= 1){
                $response['code'] = env('API_CODE_SUBSCRIPTION_DUPLICATE');
                return json_encode($response);
            }
            $user_subscription->hwid = $hwid;
        }

        $subscription_modules = @SubscriptionSettings::where('subscription_id', $user_subscription->id)->where('end_date', '>', $current_time)->get();
        if(!count($subscription_modules)){
            $response['code'] = env('API_CODE_SUBSCRIPTION_EXPIRY');
            return json_encode($response);
        }

        $response['code'] = env('API_CODE_OK');
        $response['data'] = [
            'nickname' => $user_settings->nickname ? $user_settings->nickname : 'NONAME',
            'user_id' => $user->id,
            'reg_date' =>  $user_settings->reg_date,
            'access_token' => '',
            'lifetime_subscription' => (bool)$user_subscription->is_lifetime,
            'subscription_modules' => []
        ];

        foreach($subscription_modules as $module){
            $subscription_module = [
              'name' => '',
              'end_date' => ''
            ];

            $subscription_module['name'] = GameModule::where('id', $module->module_id)->get()->first()->name;
            $subscription_module['end_date'] = date("m-d-Y H:i:s", $module->end_date);
            array_push($response['data']['subscription_modules'], $subscription_module);
        }

        $user_subscription->activation_date = date("Y-m-d H:i:s");
        $user_subscription->save();

        $api_request = new ApiRequest();
        $api_request->user_id = $user->id;
        $api_request->session_ip = $user_ip;
        $api_request->session_time = $current_time;
        $api_request->token = hash("sha256", base64_encode(openssl_random_pseudo_bytes(64)).time());
        $api_request->save();

        $response['data']['access_token'] = $api_request->token;

        return json_encode($response);
    }

    public function requestUpdates(Request $request){
        $response = [
            'code' => env('API_CODE_UNKNOWN_ERROR'),
            'data' => null,
        ];

        $game_id = @$request['game_id'];
        if(!$game_id){
            $response['code'] = env('API_CODE_GAME_NOT_FOUND');
            return json_encode($response);
        }

        $game = @Game::where('id', $game_id)->get()->first();

        if(!$game){
            $response['code'] = env('API_CODE_GAME_NOT_FOUND');
            return json_encode($response);
        }

        if($game->status != 1){
            $response['code'] = env('API_CODE_GAME_DISABLED');
            return json_encode($response);
        }

        $response['code'] = env('API_CODE_OK');
        $response['data'] = [
            'last_update' => date("Y-m-d H:i:s", $game->last_update)
        ];
        return json_encode($response);
    }

    public function requestDll(Request $request){
        $ip = "127.0.0.1";
        $access_token = @$request['access_token'];
        $game_id = @$request['game_id'];

        if(!ApiHelper::CheckToken($access_token, $game_id))
            return "";

        $game = @Game::where('id', $game_id)->get()->first();

        $zip = new \ZipArchive();
        if($zip->open(storage_path("/app/libs/$game->dll_path")) !== TRUE)
            die("");

        $zip->extractTo(storage_path("/app/libs/$ip"));
        $zip->close();

        $files = Storage::files("/libs/$ip");
        $libs = [];
        foreach($files as $file){
            $data = base64_encode(Storage::get($file));
            array_push($libs, CryptoHelper::strToHex($data, strlen($data)));
            Storage::delete($file);
        }
        Storage::deleteDirectory("/libs/$ip");

        $file_data = json_encode($libs);
        return $file_data;
    }

    public function requestModules(Request $request){
        $game_id = @$request['game_id'];
        $access_token = @$request['access_token'];

        if(!$access_token){
            return "0";
        }

        if(!ApiHelper::CheckToken($access_token, $game_id))
            return "1";

        $api_request = @ApiRequest::where('token', $access_token)->get()->first();
        $user = @User::where('id', $api_request->user_id)->get()->first();
        if(!$user)
            return "2";

        if(!UserHelper::CheckUserActivity($user))
            return "3";

        $user_sub = @Subscription::where('user_id', $api_request->user_id)->where('game_id', $game_id)->get()->first();
        if(!$user_sub)
            return "4";

        $subscription_modules = SubscriptionSettings::where('subscription_id', $user_sub->id)->get();
        $data = [
            'count' => count($subscription_modules),
            'is_lifetime' => (bool)$user_sub->is_lifetime,
            'modules' => []
        ];

        foreach($subscription_modules as $module){
            array_push($data['modules'],
                [
                    'end_date' =>  $module->end_date,
                    'id' => $module->module_id
                ]);
        }

        $response = [
            'code' => (int)env('API_CODE_OK'),
            'data' => $data,
        ];
        
        return json_encode($response);
    }

}
