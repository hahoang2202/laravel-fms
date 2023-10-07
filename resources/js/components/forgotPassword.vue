<template>
	
	<div class="container">
		<div class="row">
			<div class="col"></div>
			<div class="col-6">
				
				<div class="form-group" style="margin-top: 150px;">
					<form>
						<alertBox :message="alert.message" :type="alert.type" :show="alert.show"></alertBox>	
						<h3 class="text-center"><p class="text-center">Please enter your email 
							<p>We will send you a password reset</p></p></h3></label> 
						<input type="email" v-model="email" class="form-control" 
							placeholder="something@email.com" required="true">
						<br>
						<button type="button" @click.prevent=" submit()" class="form-control btn btn-light border border-gray">
							Send email
						</button>
					</form>
				</div>

			</div>
			<div class="col"></div>
		</div>
	</div>

</template>


<script type="text/javascript">

	import alertBox from './utils/alertBox.vue';
	
	export default {

		data() {

			return {

				alert: {
					message: "",
					show: false,
					type: "",
				},

				email: "",

			}

		},

		methods: {

			submit(){

				this.alert.show = false;

				if(this.email == ""){

					this.alert.message = "Please enter a valid email";
					this.alert.type = "alert alert-danger";
					this.alert.show = true;

				}else{

					axios.post('api/forgotPassword', {email : this.email})
					.then(response => {

						if(response.data.code == 200){

							this.alert.message = response.data.message;
							this.alert.type = 'alert alert-success';

						}else if(response.data.code == 400){

							this.alert.message = response.data.message;
							this.alert.type = "alert alert-danger";

						}

						this.alert.show = true;

					})
					.catch(error => {
						console.log(error);
					});

				}

			}

		}

	}

</script>