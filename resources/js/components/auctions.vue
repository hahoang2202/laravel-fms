<template>
	<div>

		<loading
			class="mt-5"
			:isRenderReady="isRenderReady"
		></loading>

		<div v-show="isRenderReady">
			<div
				class="container"
				v-if="hasAuctions"
			>
				<div class="row">
					<div class="col"></div>
					<div class="col-8">
						<h2 class="display-2 text-center">Auctions</h2>

						<div
							class="input-group"
							style="border-radius: 50px"
						>
							<input
								class="form-control"
								type="text"
								name="searchAuction"
								@keyup="searchAuction()"
								placeholder="Search..."
								v-model="search"
							/>
							<div class="input-group-append">
								<div class="dropdown">
									<button
										class="btn btn-light border border-gray dropdown-toggle"
										type="button"
										id="dropdownMenuButton"
										data-toggle="dropdown"
										aria-haspopup="true"
										aria-expanded="false"
										style="border-radius: 0px"
									>
										Order By : {{ orderBy.selected }}
									</button>
									<div
										class="dropdown-menu"
										aria-labelledby="dropdownMenuButton"
									>
										<a
											class="dropdown-item"
											style="padding-right: 80px"
											href="#"
											@click="orderButton(item)"
											v-for="(item, item_index) in orderBy.option"
											:key="item_index"
										>{{ item.name }}</a>
									</div>
								</div>
							</div>
						</div>

						<div
							class="mt-5 jumbotron"
							v-if="!hasSearchedAuctions"
						>
							<h4 class="display-4">
								There are no auctions with the name "{{ this.search }}"
							</h4>
						</div>
					</div>
					<div class="col"></div>
				</div>

				<div class="row">
					<div
						class="col-md-4"
						v-for="(auction, auction_index) in auctions"
						:key="auction_index"
					>
						<div
							class="card mt-5"
							:style="styleAuctionBorder(auction)"
						>
							<img
								class="card-img-top"
								width="300"
								height="300"
								:src="'upload\\' + auction.photo_url"
								alt="Auction image"
								style="border-radius-top: 10px"
							/>

							<div class="card-body">
								<h5 class="card-title">{{ auction.name }}</h5>
								<p class="card-text">{{ auction.description }}</p>
								<p>Start date: {{ auction.start }}</p>
								<p>Minimum price: {{ auction.min_price }}VND</p>
								<p v-if="
                    auction.last_bid_price != null ||
                    auction.last_bid_price != 0">
									Last bid price: {{ auction.last_bid_price }}VND
								</p>

								<div
									class="input-group mb-3"
									v-if="user"
								>
									<div v-if="auction.last_bid_price != 0">
										<input
											type="text"
											class="form-control"
											v-model="auction.bid_price"
											:placeholder="'Bid price: ' + auction.last_bid_price + 'VND'"
										/>
									</div>
									<div v-else>
										<input
											type="text"
											class="form-control"
											v-model="auction.bid_price"
											:placeholder="'Bid price: ' + auction.min_price + 'VND'"
										/>
									</div>
									<div
										class="
                      input-group-append
                      form-control
                      btn btn-outline-secondary
                    "
										style="
                      display: flex;
                      justify-content: center;
                      align-items: center;
                      border-color: #a0a4a9;
                    "
										@click="bidAuction(auction, auction.bid_price)"
									>
										Bid
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div
				class="container"
				v-if="!hasAuctions"
			>
				<div class="row">
					<div class="col"></div>
					<div class="col-8">
						<div
							class="jumbotron"
							style="margin-top: 250px"
						>
							<h1>It appears there are no active auctions...</h1>
							<h4 v-if="!user">Login or register to start one!</h4>
							<div
								v-else
								class="text-center"
							>
								<h3>Create one yourself!</h3>
								<br />
								<router-link to="create-auction">
									<button
										type="button"
										class="btn btn-primary"
									>
										Create Auction
									</button>
								</router-link>
							</div>
						</div>
					</div>
					<div class="col"></div>
				</div>
			</div>

			<div class="container mb-5"></div>
		</div>
	</div>
</template>

<script>

import { mapGetters } from "vuex";
import LoadingComponent from './utils/loading.vue';

export default {

	components: {
		'loading': LoadingComponent
	},

	data() {
		return {
			auctions: [],
			auctionsByUser: {},
			hasAuctions: false,
			search: "",
			hasSearchedAuctions: true,
			orderBy: {
				option: [
					{ name: "Earliest", data: "start desc" },
					{ name: "Oldest", data: "start asc" },
					{ name: "Name Asc", data: "name asc" },
					{ name: "Name Desc", data: "name desc" },
					{ name: "Price (Lowest to Highest)", data: "last_bid_price asc" },
					{ name: "Price (Highest to Lowest)", data: "last_bid_price desc" },
				],
				selected: "Earliest",
			},
			isRenderReady: false,
		};
	},

	computed: {
		...mapGetters({
			api_token: 'getUserToken',
			user: "getUser",
			wallet: "getWallet"
		}),
	},

	sockets: {

		auction_bidded(data) {

			if (data.message) {
				Vue.toasted.show(data.message);
			}

			if (data.auction) {
				//TODO Get wallet after bidding

				let auctions = this.auctions;
				if(this.auctionsByUser.has(data.auction.owner_id)){
					auctions = this.auctionsByUser.get(data.auction.owner_id);
				}

				auctions.find(auction => {
					if (auction.id === data.auction.id) {
						auction.last_bid_user_id = data.auction.last_bid_user_id;
						auction.last_bid_price = data.auction.last_bid_price;
						this.$axios.get('/api/wallet', { headers: { 'Authorization': `Bearer ${this.api_token}` } })
							.then(response => {
								this.$store.commit("setBalance", { balance: response.data.balance, reserved: response.data.reserved });
							}).catch(error => {
								console.log(error);
							});
					}
				});

			}
		},

		auction_closed(data) {

			let auctions = this.auctions;
			if(this.auctionsByUser.has(data.auction.owner_id)){
				auctions = this.auctionsByUser.get(data.auction.owner_id);
			}

			let closedAuctionIndex = auctions.findIndex(auction => {
				return data.auction.id === auction.id;
			});
	
			this.auctions.splice(closedAuctionIndex, 1);
			this.$axios.get("/api/wallet", {
				headers: {
					'Authorization': `Bearer ${this.api_token}`
				}
			}).then(response => {
				this.$store.commit('setBalance', { balance: response.data.balance, reserved: response.data.reserved });
			}).catch(error => {
				console.log(error);
			});

			if (data.message) {
				Vue.toasted.show(data.message);
			}
			
		}

	},

	methods: {
		async getAuctions(order) {
			this.hasSearchedAuctions = true;
			await this.$axios.get("/api/auctions?orderBy=" + order)
				.then(async (response) => {
					this.auctions = await response.data;
					this.hasAuctions = true;
				})
				.catch((error) => {
					console.log(error);
				});
		},

		styleAuctionBorder(auction) {
			let style = "border-radius: 10px;";

			if (this.user) {
				if (auction.last_bid_user_id == this.user.id) {
					style += " border-color: green; border-width: 5px;";
				}
				if (auction.owner_id == this.user.id) {
					style += "border-color: blue; border-width: 5px";
				}
			}

			return style;
		},

		bidAuction(auction, bidPrice) {

			if (!bidPrice || bidPrice == '' || bidPrice == 0) {
				return;
			}

			if(auction.last_bid_user_id === this.user.id){
				this.$toasted.show(`Current auction already bidded with ${auction.last_bid_price}`);
				return;
			}

			const options = {
				method: 'PUT',
				headers: { 'Authorization': `Bearer ${this.api_token}` },
				data: { bidPrice },
				url: `/api/auction/${auction.id}`
			};

			const last_user_id = auction.last_bid_user_id;

			this.$axios(options).then(response => {

				if (response.status != 200) {
					this.$toasted.error(response.data.message);
					return;
				}

				auction.last_bid_user_id = this.user.id;
				auction.last_bid_price = bidPrice;

				this.$toasted.success(`Auction ${auction.name} bidded with ${bidPrice}`);
				auction.bidded = true;

				this.$socket.emit('auction_bidded', auction, last_user_id);

				this.$store.commit('setBalance', { balance: response.data.balance, reserved: response.data.reserved });

			}).catch((error) => {
				console.log(error);
			}).finally(() => {
				auction.bid_price = '';
			});
		},

		searchAuction(order) {

			if (order === undefined) {
				for (var i = 0; i < this.orderBy.option.length; i++) {
					if (this.orderBy.option[i].name == this.orderBy.selected) {
						order = this.orderBy.option[i].data;
					}
				}
			}

			if (this.search != "") {
				this.$axios
					.get("api/searchAuction?search=" + this.search + "&orderBy=" + order)
					.then((response) => {
						if (response.status === 200) {
							this.hasSearchedAuctions = true;
							this.auctions = response.data.auctions;
						} else {
							this.hasSearchedAuctions = false;
							this.auctions = null;
						}
					})
					.catch((error) => {
						console.log(error);
					});
			} else {
				this.getAuctions(order);
			}
		},

		orderButton(order) {
			this.orderBy.selected = order.name;

			if (this.search != "") {
				this.searchAuction(order.data);
			} else {
				this.getAuctions(order.data);
			}
		},
	},

	async created() {

		await this.getAuctions();
		this.isRenderReady = true;

		this.auctionsByUser = new Map();
		this.auctions.forEach(auction => {
			if(!this.auctionsByUser.has(auction.owner_id)){
				this.auctionsByUser.set(auction.owner_id, []);
			}
			let auctionsList = this.auctionsByUser.get(auction.owner_id);
			auctionsList.push(auction);
		});

	},
};
</script>

<style type="text/css" media="screen">
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
	/* display: none; <- Crashes Chrome on hover */
	-webkit-appearance: none;
	margin: 0; /* <-- Apparently some margin are still there even though it's hidden */
}

input[type="number"] {
	-moz-appearance: textfield; /* Firefox */
}

.biddedAuction {
	border-radius: 10px;
	border-width: 5px;
	border-color: green;
}

.unbiddedAuction {
	border-radius: 10px;
}
</style>