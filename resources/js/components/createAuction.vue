<template>
	<div class="container">
		<div class="row">
			<div class="col"></div>
			<div class="col-6">

				<form class="form-group" style="padding-top: 120px" enctype="multipart/form-data">

					<h2 class="display-4 text-center">Create an Auction</h2>

					<label for="name">Name</label>
					<input
						type="text"
						class="form-control"
						required
						v-model="auction.name"
					/>

					<label for="description">Description</label>
					<textarea
						type="text"
						class="form-control"
						rows="4"
						cols="40"
						required
						v-model="auction.description"
					></textarea>

					<label for="min_price">Minimum Price</label>
					<input
						type="number"
						class="form-control"
						required
						v-model="auction.min_price"
					/>

					<div class="custom-file mt-3 mb-3">

						<input
							type="file"
							class="custom-file-input"
							id="customFile"
							@change="onFileSelected"
						/>
						<label class="custom-file-label"	for="customFile">
							{{this.fileName}}
						</label>

					</div>

					<button type="button" @click.prevent="submit(auction)" class="btn btn-success form-control">
						Submit
					</button>

				</form>
				<alertBox
					:message="alert.message"
					:show="alert.show"
					:type="alert.type"
					:errors="errors"
					@close="alert.show = false"
				>
				</alertBox>
			</div>
			<div class="col"></div>
		</div>
	</div>
</template>

<script type="text/javascript">

import { mapGetters } from 'vuex';

export default {
	components: {
		AlertBox: () => import("./utils/alertBox.vue"),
	},

	data() {
		return {
			fileName: "Choose item's photo",

			auction: {
				name: "",
				description: "",
				min_price: 0,
				photo_url: null,
			},

			errors: [],

			alert: {
				message: "",
				show: false,
				type: "alert alert-danger",
			},

		};
	},

	computed: {
		...mapGetters({
			api_token: 'getUserToken',
			user: 'getUser',
		})
	},

	methods: {
		onFileSelected(event) {
			this.fileName = event.target.files[0].name;
			this.auction.photo_url = event.target.files[0];
		},

		checkForm(auction) {

			this.errors = [];
			this.alert.show = false;

			if (!auction.name) {
				this.errors.push("You must insert the name of your auction");
			}

			if (!auction.description) {
				this.errors.push("You must insert the description of your auction")
			}

			if (!auction.min_price || auction.min_price <= 0) {
				this.errors.push("You must insert the minimum price of your auction")
			}

			if (!auction.photo_url || !auction.photo_url) {
				this.errors.push("You must insert an image for your photo")
			}

			if (this.errors.length) {
				this.alert.show = true;
				return false;
			}

			return true;

		},

		// sleep(milliseconds) {
		// 	const date = Date.now();
		// 	let currentDate = null;
		// 	do {
		// 		currentDate = Date.now();
		// 	} while (currentDate - date < milliseconds);
		// },

		submit(auction) {

			if (this.checkForm(auction)) {

				let formData = new FormData();
				formData.append("file", auction.photo_url);
				formData.append("name", auction.name);
				formData.append("description", auction.description);
				formData.append("min_price", auction.min_price);
				formData.append("email", this.user.email);

				this.$toasted.show(`Creating auction ${auction.name}`);

				this.$axios
					.post("api/auction", formData, {
						headers: { Authorization: "Bearer " + this.api_token },
					})
					.then((response) => {
						if (response.status == 400) {
							this.alert.message = response.data.message;
							this.alert.type = "alert alert-danger";
							this.alert.show = true;
						} else {
							Vue.toasted.success(response.data.message);
							this.$router.push("/my-auctions");
						}
					})
					.catch((error) => {
						console.log(error);
					});
			}
		},
	},
};
</script>

<style type="text/css" media="screen">
</style>