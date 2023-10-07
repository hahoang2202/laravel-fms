<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;

class AuctionsSeeder extends Seeder{

	private $itemsPath = 'upload';

	private $counter = 0;
	private $totalItems = 12;
	private $faker = null;

	public function run(){
		$this->command->line("");
		$this->command->line("Creating Auctions...");

		\Storage::deleteDirectory($this->itemsPath);
		\Storage::makeDirectory($this->itemsPath);

		$this->faker = \Faker\Factory::create('en_US');

		$this->addAuction($this->faker, 'tools.jpg', 'Tools', 18.3);
		$this->addAuction($this->faker, 'bike.jpeg', 'Bike', 295.5);
		$this->addAuction($this->faker, 'canon.jpeg', 'Canon', 455.0);
		$this->addAuction($this->faker, 'chair.jpg', 'Chair', 82.0);
		$this->addAuction($this->faker, 'guitar_elvis.jpeg', 'Elvis Guitar', 893.0);
		$this->addAuction($this->faker, 'house.jpg', 'House', 210600.0);
		$this->addAuction($this->faker, 'ipad.jpg', 'Ipad', 485.0);
		$this->addAuction($this->faker, 'iphone.jpg', 'Iphone', 345.0);
		$this->addAuction($this->faker, 'macbook.jpg', 'Macbook', 1835.0);
		$this->addAuction($this->faker, 'smartwatch.jpeg', 'Smartwatch', 90.0);
		$this->addAuction($this->faker, 'typewriter.jpg', 'Typewriter', 125.0);
		$this->addAuction($this->faker, 'volkswagen.jpeg', 'Volkswagen', 7800.0);

	}

	private function copyProfilePhoto($filename){

		$upload_path = public_path('upload');
		if(!file_exists($upload_path)){
			mkdir($upload_path);
		}
		
		$generated_new_name = time() . '_' . $filename;
		// $targetDir = storage_path('app\\public\\' . $this->itemsPath);

	    	//$filename->store('upload');
		\File::copy(database_path('seeders/items_photos') . "\\$filename", $upload_path . '\\' . $generated_new_name);

		return $generated_new_name;

	    	/*
	    	$newFileName = Str::random(16) . ".jpg";

	    	File::copy(database_path('seeds/items_photos') . "/$filename", $targetDir . '/' . $newFileName);

	    	return $newFileName;*/

	    }

	    private $auctionCounter = 1;

	    private function addAuction(\Faker\Generator $faker, $filename, $title, $price, $softDelete = false){

	    	$newFileName = $this->copyProfilePhoto($filename);
	    	$createdAt = \Carbon\Carbon::now()->subDays(600);

	    	if($this->counter < 8){
	    		$startBase = \Carbon\Carbon::now()->subDays(5);
	    	}else{
	    		$startBase = \Carbon\Carbon::now()->subDays(1);
	    	}

	    	$start = $startBase->copy()->addMinutes(rand(0, 1440));
	    	$hasTerminated = $this->auctionCounter % 4 == 0;
	    	$this->auctionCounter++;
	    	$end = $startBase->copy()->addMinutes(rand(1440, 10080));

	    	$auction = [

	    		'name' => $title,
	    		'description' => $faker->realText(200),
	    		'photo_url' => $newFileName,
	    		'min_price' => $price,
	    		'start' => $start,
	    		'end' => $hasTerminated ? $end : null,
	    		'last_bid_price' => 0.00,
	    		'owner_id' => $this->getRandomUser(),
	    		'created_at' => $createdAt,
	    		'updated_at' => $faker->dateTimeBetween($createdAt),
	    		'deleted_at' => $softDelete ? $this->faker->dateTimeBetween($createdAt) : null,

	    	];

	    	$this->counter++;

	    	\DB::table('auctions')->insert($auction);
	    	$this->command->info("Created auction {$this->counter}/{$this->totalItems}: " . $auction['name']);

	    }

	    private function getRandomUser(){
	    	static $users;
	    	$users = $users != null ? $users : \DB::table('users')->pluck('id');
	    	return $users->random();
	    }


	}
