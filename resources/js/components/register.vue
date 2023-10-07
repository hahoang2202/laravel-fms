<template>
	
	<div class="container">
		<div class="row">
			<div class="col"></div>
			<div class="col-6">
				<div class="form-group align-midle" style="margin-top: 100px;">
          <h2 class="h2 text-center">Register</h2>

					<form action="#" class="form-group">
						<label for="name">Name</label>
						<input class="form-control" type="text" v-model="user.name" required>
						<label for="Email">Email</label>
						<input class="form-control" type="email" name="email" v-model="user.email" required>
						<label for="password">Password</label>
						<input class="form-control" type="password" name="password" v-model="user.password" required>
						<label for="passwordConfirmation">Confirm Password</label>
						<input class="form-control" type="password" v-model="confPass" required>
						<button class="btn btn-success form-control mt-4" type="button" @click.prevent="registerUser">Register</button>
					</form>

					<alertBox @close="alert.show = false" :errors="errors" :message="alert.message" :type="alert.type" :show="alert.show"></alertBox>

				</div>
			</div>
			<div class="col"></div>
		</div>
	</div>

</template>

<script type="text/javascript">

export default {
  components: {
    AlertBox: () => import("./utils/alertBox.vue"),
  },

  data() {
    return {
      user: {
        name: "",
        password: "",
        email: "",
      },

      confPass: "",
      alert: {
        type: "alert alert-danger",
        show: false,
        message: "",
      },

      errors: [],

    };
  },

  methods: {
		
    registerUser() {

      if(!this.validateForm()){
        return;
      }

      this.$toasted.show("Creating your account...");
      this.$axios.post("api/register", this.user).then((response) => {
        if (response.data.code != 200) {
          this.alert.message = response.data.message;
          this.alert.show = true;
        } else {
          Vue.toasted.success(response.data.message);
          this.$router.push("/login");
        }
      })
      .catch((error) => {
        console.log(error);
      });
    },
    
    validateForm(){

      this.alert.show = false;
      this.errors = [];

      const user = this.user;
      let newErrors = [];

      if(!user.name){
        newErrors.push('Name is required');
      }

      if(!user.email){
        newErrors.push('Email is required');
      }else if(!this.validEmail(user.email)){
        newErrors.push('Email must be a valid email');
      }

      if(!user.password){
        newErrors.push('Password is required');
      }else if(user.password != this.confPass){
        newErrors.push('Passwords must match');
      }

      if(newErrors.length){
        this.errors.push(...newErrors);
        this.alert.show = true;
        return false;
      }

      return true;

    },

    validEmail(email){
      const regex = /^((?!\.)[\w\-_.]*[^.])(@\w+)(\.\w+(\.\w+)?[^.\W])$/;
      return regex.test(email);
    }

  }
}
</script>