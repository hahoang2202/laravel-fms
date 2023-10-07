<template>
	<div class="container">
		<div class="row">
			<div class="col"></div>
			<div class="col-10">
				<nav class="navbar navbar-toggleable-md navbar-light bg-faded">
					<ul class="nav nav-tabs">
						<li
							class="nav-item"
							v-for="(item, index) in auctionTypes"
							:key="index"
						>
							<a
								:class="
									activeTab == item.type
										? 'nav-link active btn activeTab'
										: 'nav-link btn'
								"
								@click="changeActiveAuctions(item.type)"
								data-toggle="collapse"
								:data-target="'#confirmation-' + auctionToClose"
								aria-expanded="false"
								aria-controls="confirmation"
								>{{ item.label }}</a
							>
						</li>
					</ul>

					<ul class="navbar-nav">
						<li class="nav-item" style="float: right">
							<router-link to="/create-auction">
								<a href="#" class="btn btn-primary">Create Auction</a>
							</router-link>
						</li>
					</ul>
				</nav>
			</div>
			<div class="col"></div>
		</div>

		<loading class="mt-5" :isRenderReady="isRenderReady"></loading>

		<div v-if="isRenderReady">
			<div v-if="hasAuctions">
				<div
					class="row mt-3"
					v-for="(auction, auction_index) in auctions"
					:key="auction_index"
				>
					<div class="col"></div>
					<div class="col-8">
						<div class="card">
							<div class="card-header">
								<h5 class="mt-2">
									<strong>{{ auction.name }}</strong>
								</h5>
							</div>
							<div class="card-body">
								<p class="card-text">{{ auction.description }}</p>
								<img
									class="border border-gray border-rounded"
									style="float: right; position: relative"
									:src="'upload\\' + auction.photo_url"
									alt="Auction image"
									width="200"
									height="150"
								/>

								Start date: {{ auction.start }}
								<span
									class="border border-danger rounded ml-3"
									v-if="auction.end != null"
									>End date: {{ auction.end }}</span
								>
								<br />
								Minumum price: {{ auction.min_price }}
								<br />
								<p v-if="auction.last_bid_price != null">
									Last bid: {{ auction.last_bid_price }}
								</p>

								<div v-if="canClose">
									<button
										type="button"
										class="btn btn-danger"
										v-if="auction.end == null"
										data-toggle="collapse"
										:data-target="'#confirmation-' + auction.id"
										aria-expanded="false"
										aria-controls="confirmation"
										@click="auctionToClose = auction.id"
									>
										Close Auction
									</button>
								</div>

								<br />
								<br />
								<br />
								<div class="collapse" :id="'confirmation-' + auction.id">
									<div class="container">
										<h3>Are you sure you want to close the auction?</h3>

										<button
											type="button"
											class="btn btn-success"
											@click="closeAuction(auction)"
											style="
												margin-left: 20px;
												padding-left: 100px;
												padding-right: 100px;
											"
											data-toggle="collapse"
											:data-target="'#confirmation-' + auction.id"
										>
											Yes
										</button>

										<button
											type="button"
											class="btn btn-danger"
											data-toggle="collapse"
											:data-target="'#confirmation-' + auction.id"
											style="padding-left: 100px; padding-right: 100px"
											@click="console.log(auctionToClose)"
										>
											No
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col"></div>
				</div>
			</div>

			<div class="jumbotron row mt-3" v-if="!hasAuctions">
				<h4 class="display-4">{{ this.message }}</h4>
			</div>
		</div>
	</div>
</template>

<script type="text/javascript">

import { mapGetters } from 'vuex';

export default {

	components: {
		Loading: () => import("./utils/loading.vue"),
	},

	data() {
		return {
			auctions: [],
			activeTab: 'all',
			auctionTypes: {
				all: {
					type: 'all',
					label: 'All Auctions'
				},
				active: {
					type: 'active',
					label: 'Active Auctions'
				},
				closed: {
					type: 'closed',
					label: 'Closed Auctions'
				},
				won: {
					type: 'won',
					label: 'Won Auctions',
				},
				bidded: {
					type: 'bidded',
					label: 'Bidded Auctions'
				}
			},
			hasAuctions: false,
			message: "",
			canClose: true,
			auctionToClose: "",
			remember_token: this.$store.getters.getUserToken,
			isRenderReady: false,
		};
	},

	sockets: {

		auction_closed(data) {

			let closedAuction = this.auctions.find(auction => {

				if (data.auction.id === auction.id) {
					console.log("Auction found!", auction);
					auction = closedAuction;
					console.log("Changed auction!", auction);
					console.log("All auctions", this.auctions);

					this.$axios.get("/api/wallet", {
						headers: {
							'Authorization': `Bearer ${this.api_token}`
						}
					}).then(response => {
						this.$store.commit('setBalance', { balance: response.data.balance, reserved: response.data.reserved });
					}).catch(error => {
						console.log(error);
					});
				}
			});

			if (data.message) {
				Vue.$toasted.show(data.message);
			}

		}
	},

	computed: {
		...mapGetters({
			user: 'getUser',
			api_token: 'getUserToken'
		})
	},

	methods: {

		getUserAuctions(type) {

			this.isRenderReady = false;

			let url = "api/auctions/" + this.$store.state.user + "?set=" + type;

			this.$axios
				.get(url, {
					headers: { Authorization: "Bearer " + this.remember_token },
				})
				.then((response) => {
					if (response.data.code == 206) {
						this.auctions = null;
						this.hasAuctions = false;
						this.message = response.data.message;
					} else {
						this.auctions = response.data.auctions;
						this.hasAuctions = true;
						this.message = "";
						this.canClose = response.data.close;
						this.auctionToClose = "";
					}
				})
				.catch((error) => {
					console.log(error);
				}).finally(() => {
					this.isRenderReady = true;
				});
		},

		changeActiveAuctions(type) {
			this.activeTab = type;
			this.getUserAuctions(type);
		},

		closeAuction(auction) {

			const options = {
				method: 'PUT',
				url: `/api/auction/${auction.id}/close`,
				headers: {
					'Authorization': `Bearer ${this.api_token}`
				}
			}

			this.$axios(options).then((response) => {
				auction.end = true;
				Vue.toasted.show(response.data.message);
				this.$store.commit('setBalance', response.data.balance);
				this.getUserAuctions();

				this.$axios.get("/api/wallet", {
					headers: {
						'Authorization': `Bearer ${this.api_token}`
					}
				}).then(response => {
					this.$store.commit('setBalance', { balance: response.data.balance, reserved: response.data.reserved });
          this.$socket.emit('auction_closed', auction);
				}).catch(error => {
					console.log(error);
				});

			})
				.catch((error) => {
					console.log(error);
				});
		},
	},

	mounted() {
		this.getUserAuctions();
	},
};
</script>

<style scoped>
.activeTab {
	background-color: gray;
}
</style>