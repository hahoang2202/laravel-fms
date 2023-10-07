
![Auction's logo](https://i.imgur.com/kbH0ofx.png)

# Auctions Web Page

This is a Web Application developed with the porpuse of buying and selling items trough online auctions.
It was created solely as practise, having no real world use.
It was made with **Laravel** and **Vue.js** frameworks, as well as **Bootstrap** for CSS.

# Features

This aplication allows users to register and login with their accounts.
Every user, authenticated or not, can visualize all available auctions, but only authenticated users can bid those auctions, as well as create new ones.
To bid an auction, the user must have a wallet balance equal or greater that the auctions price. Then, the bidded price is reserved in the user's wallet, and this money can no longer be used to bid other auctions unless other user bids the same auction with a greater value. 
When the owner of a bidded auction closes it's auction, the value of this auction is transfered from the reserved space of a user to it's owner. 
This application also uses **Socket.io** for real time changes in auctions.

## How to

Deploy:
	 
	php artisan migrate:fresh --seed
	php artisan serve
	npm run watch
  
With socket io:
	
	node WebSocketServer/server.js
  
  ##### You may need to update with composer
--- 
Use:
	 
>Login with any user from user0 to user5 -> "'user0'@mail.com" and password "123"
>
>OR
>
>Register a new account
		 
>Increase balance
>
>Open another tab to visualize real time data exchange

## Future features

 - Email integration for account confirmation and others.
 - Debit, Credit and Paypal deposit
 - ...

## Used packages
In backend: 
 - Laravel Wallet

In frontend: 

 - Axios
 - Bootstrap
 - Socket.io
 - Vue-toasted
 - Vuex
### Brief explanations
![User's own auction](https://i.imgur.com/KxnMMgB.png)
#### The blue border around an auction means the shown auction was created and belongs to the user

![User's bidded auction](https://i.imgur.com/OJqrZD6.png)

#### The green border around an auction mean the shown auctions was bidded by the current user.

----------

![Auctions Preview](https://i.imgur.com/ulMP0so.png)

------------
