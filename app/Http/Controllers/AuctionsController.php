<?php

namespace App\Http\Controllers;

use App\Auction;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use App\User;
use App\Http\Controllers\UserWalletController;
use Bavix\Wallet\Models\Wallet;

class AuctionsController extends Controller {

	public function index(Request $request) {

		if ($request->orderBy == 'undefined') {
			$request->orderBy = 'start desc';
		}

		$order = explode(" ", $request->orderBy);

		$auctions = Auction::where('end', null)
			->orWhere('end', 0)
			->orderBy($order[0], $order[1])
			->get();

		if ($order[0] == 'last_bid_price') {

			foreach ($auctions as $auction) {	

				if ($auction->last_bid_price == 0 || $auction->last_bid_price == null) {
					$auction['orderByPrice'] = $auction->min_price;
				} else {
					$auction['orderByPrice'] = $auction->last_bid_price;
				}
			}

			if ($order[1] == 'asc') {
				return $auctions->sortBy('orderByPrice')->values()->all();
			} else {
				return $auctions->sortByDesc('orderByPrice')->values()->all();
			}
		}

		return $auctions;
	}

	public function searchAuction(Request $request) {

		if ($request->orderBy == 'undefined') {
			$request->orderBy = 'start desc';
		}

		$order = explode(" ", $request->orderBy);

		$auctions = Auction::where('name', 'LIKE', '%' . $request->search . '%')
			->orderBy($order[0], $order[1])
			->get();

		if ($order[0] == 'last_bid_price') {

			foreach ($auctions as $auction) {

				if ($auction->last_bid_price == 0 || $auction->last_bid_price == null) {
					$auction['orderByPrice'] = $auction->min_price;
				} else {
					$auction['orderByPrice'] = $auction->last_bid_price;
				}
			}

			if ($order[1] == 'asc') {
				return $auctions->sortBy('orderByPrice')->values()->all();
			} else {
				return $auctions->sortByDesc('orderByPrice')->values()->all();
			}
		}

		if (count($auctions) == 0) {
			return response()->json(["message" => "There are no auctions available with that name"], 206);
		}

		return response()->json(["auctions" => $auctions]);
	}

	public function store(Request $request) {

		$upload_path = public_path('upload');
		if ($request->file != null) {
			$file_name = $request->file->getClientOriginalName();
			$generated_new_name = time() . '_' . $request->file->getClientOriginalExtension();
		} else {
			return response()->json(["message" => "You must insert an image for your auctions"], 400);
		}

		$validator = $request->validate([

			'name' => 'required',
			'description' => 'required',
			'min_price' => 'required|digits_between:1,10000',
			'file' => 'required|file|image',

		]);

		$auction = new Auction();
		$user = Auth::user();

		$auction->owner_id = $user->id;
		$auction->name = $request->name;
		$auction->description = $request->description;
		$auction->min_price = $request->min_price;
		$request->file->move($upload_path, $generated_new_name);
		$auction->photo_url = $generated_new_name;
		$auction->start = Carbon::now();

		$auction->save();

		return response()->json(['message' => "You have successfully created the auction " . $auction->name]);
	}

	public function userAuctions(Request $request) {

		$user = Auth::user();
		$close = true;

		switch ($request->set) {

			case 'active':

				$auctions = Auction::where('owner_id', $user->id)
					->where('end', null)
					->orderBy('created_at', 'ASC')
					->get();

				$message = "You dont have any active auctions yet";

				break;

			case 'closed':

				$auctions = Auction::where('owner_id', $user->id)->where('end', '<>', null)->get();
				$message = "You dont have closed auctions yet";

				break;

			case 'won':

				$auctions = Auction::where('last_bid_user_id', $user->id)->where('end', '<>', null)->get();
				$message = "You havent won any auctions yet";

				break;

			case 'bidded':

				$auctions = Auction::where('last_bid_user_id', $user->id)->where('end', null)->get();
				$message = "You dont have any bidded auctions";
				$close = false;

				break;

			default:

				$auctions = Auction::where('owner_id', $user->id)->get();
				$message = "You dont have any auctions yet";

				break;
		}

		if (count($auctions) > 0) {
			return ["auctions" => $auctions, "close" => $close];
		} else {
			return ["message" => $message, "code" => 206];
		}
	}

	public function bidAuction(Request $request) {

		$auction = Auction::find($request->id);
		$user = Auth::user();

		if($auction->last_bid_user_id == $user->id){
			return response()->json(["mesagge" => "Current auction already bidded with " . $auction->last_bid_price], 206);
		}

		if ($user->id == $auction->owner_id) {
			return response()->json(["message" => "You cant bid your own auctions"], 206);
		}

		if ($auction->last_bid_price == null) {
			if ($request->bidPrice <= $auction->min_price) {
				return response()->json(["message" => "You must bid greater than " . $auction->min_price . " VND"], 206);
			}
		} else {
			if ($request->bidPrice <= $auction->last_bid_price) {
				return response()->json(["message" => "You must bid greater than " . $auction->last_bid_price . " VND"], 206);
			}
		}

		$user_wallet = UserWalletController::getUserWallet($user->id);
		$balance = $user_wallet->balance;	

		if ($balance < $request->bidPrice) {
			return response()->json(["message" => "You dont have enough funds to bid this auction"], 206);
		}

		if($auction->last_bid_user_id != null){
			$last_user = User::find($auction->last_bid_user_id);
			$last_user_wallet = UserWalletController::getUserWallet($last_user->id);

			$last_user_wallet = Wallet::where('holder_id', $auction->last_bid_user_id)->first();
			$last_user_wallet->reserved = $last_user_wallet->reserved - $auction->last_bid_price;
			//FIXME THIS CANNOT BE DEPOSIT, BALANCE SHOULD BE UPDATED ANOTHER WAY
			$last_user_wallet->deposit($auction->last_bid_price);
			$last_user_wallet->save();
		}

		$auction->last_bid_price = $request->bidPrice;
		$auction->last_bid_user_id = $user->id;

		$reserved = $user_wallet->reserved + $request->bidPrice;
		//FIXME THIS CANNOT BE WHITDRAW, BALANCE SHOULD BE UPDATED ANOTHER WAY
		$user_wallet->withdraw($request->bidPrice);

		$user_wallet->reserved = $reserved;

		$user_wallet->save();
		error_log(print_r($user_wallet, TRUE)); 

		$auction->save();

		return response()->json([
			"message" => "Auction " . $auction->name . " bidded with " . $request->bidPrice . " VND", 
			"balance" => $user_wallet->balance,
			"reserved" => $reserved
		]);

	}

	public function closeAuction(Request $request) {

		$auction = Auction::findOrFail($request->id);

		if ($auction->end != null) {
			return response()->json(["message" => "Auction already closed"], 400);
		}

		$owner = Auth::user();
		if ($auction->owner_id != $owner->id) {
			return response()->json(["message" => "You cant close this auction"], 203);
		}

		$auction->end = Carbon::now();

		$owner_wallet = UserWalletController::getUserWallet($auction->owner_id);
		$user_wallet = UserWalletController::getUserWallet($auction->last_bid_user_id);

		$payment = $auction->last_bid_price;
		if($user_wallet->reserved < $payment){
			return response()->json(["message" => "Unknown error ocured, try again later"], 500);
		}

		//FIXME THIS CANNOT BE DEPOSIT, BALANCE SHOULD BE UPDATED ANOTHER WAY
		$user_wallet->deposit($payment);
		$user_wallet->reserved -= $payment;
		$user_wallet->save();

		$user_wallet->transfer($owner_wallet, $payment);
		$auction->save();

		return response()->json(["message" => "Auction ended", "balance" => $owner_wallet->balance]);

	}
}
