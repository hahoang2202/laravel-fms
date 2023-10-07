<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Auction;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\Wallet as ModelsWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserWalletController extends Controller{

    public function getUser($email){
        return User::where('email', $email)->first();
    }

    public static function getUserWallet($user_id){
        return ModelsWallet::where('holder_id', $user_id)->first();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getBalance(Request $request){
        $user = Auth::user();
        $wallet = $this->getUserWallet($user->id);

        return response()->json(["balance" => $wallet->balance, "reserved" => $wallet->reserved]);
    }

    public function deposit(Request $request){
        $user = Auth::user();
        $deposit = $request->deposit_ammount;
        $user->wallet->deposit($deposit);
        return $user->balance;
    }

    public function transfer($data){

        $auction = $data->auction;
        $ammount = $auction->last_bid_price;

        $owner = Auth::user();
        $user = User::find($auction->last_bid_user_id);

        $owner_wallet = ModelsWallet::where('holder_id', $owner->id)->first();
        $user_wallet = ModelsWallet::where('holder_id', $user->id)->first();

        if($user->id != $owner->id){
            $user_wallet->transfer($owner_wallet, $ammount);
        }
        
        return $owner_wallet->balance;

    }

    public function withdraw(Request $request){

        $user = Auth::user();
        $wallet = ModelsWallet::where('holder_id', $user->id)->first();

        if($wallet->balance > 0 && $request->withdraw_ammount <= $wallet->balance){
            $wallet->withdraw($request->withdraw_ammount);
            return response()->json(["balance" => $wallet->balance]);
        }else{
            return response()->json(["message" => "Cannot withdraw more than current balance"], 402);
        }
    }

}
