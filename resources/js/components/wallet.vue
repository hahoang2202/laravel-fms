<template>
  <div class="container jumbotron mt-lg-5">
    <h3>Current balance: {{wallet.balance}}VND</h3>
    <h5>Reserved balance: {{wallet.reserved}}VND</h5>

    <div class="mt-5">

      <form action="#">
        <div class="form-group">
          <label for="deposit">Deposit ammount</label>
          <div style="width: 50%" class="input-group">
            <input v-model="deposit_ammount" class="form-control" id="deposit_input" type="number" placeholder="0.00">
            <button @click.prevent="deposit(deposit_ammount)" style="width:30%" class="btn btn-success ml-3" type="button">Deposit</button> 
          </div>
        </div>
      </form>

      <form action="#">
        <div class="form-group">
          <label for="withdraw">Withdraw ammount</label>
          <div style="width:50%" class="input-group">
            <input v-model="withdraw_ammount" class="form-control" id="withdraw_input" type="number" placeholder="0.00">
            <button @click.prevent="withdraw(withdraw_ammount)" style="width:30%" class="btn btn-success ml-3" type="button">Withdraw</button>
          </div>
        </div>
      </form>

    </div>

	</div>
</template>

<script>
import { mapGetters } from "vuex";

export default {
  data() {
    return {
      deposit_ammount: '',
      withdraw_ammount: '',
    };
  },

  computed: {
		...mapGetters({
			user: 'getUser',
      wallet: 'getWallet',
      token: 'getUserToken'
		}),
	},

  methods: {

    deposit(ammount){

      if(ammount > 0){
        this.$axios.put('/api/wallet/deposit', { deposit_ammount: ammount }, {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        }).then(response => {
          Vue.toasted.success(`Successfully deposited ${ammount}`);
          this.wallet.balance = response.data;
          this.$store.commit('setBalance', {balance: this.wallet.balance});
        }).catch(error => {
          console.log(error);
          Vue.toasted.error('There was an error depositing the deposit ammount');
        }).finally(() => {
          this.deposit_ammount = '';
        })
      }
    },

    withdraw(ammount){
      if(ammount > 0 && ammount <= this.wallet.balance){
        this.$axios.put('api/wallet/withdraw', {withdraw_ammount: ammount}, {
          headers: {
            Authorization: `Bearer ${this.token}`
          }
        }).then(response => {
          Vue.toasted.success(`Successfully withdrew ${ammount}`)
          this.wallet.balance = response.data.balance;
          this.$store.commit('setBalance', this.wallet.balance);
        }).catch(error => {
          console.log(error);
          Vue.toasted.error('There was an error withdrawing the requested ammount');
        }).finally(() => {
          this.withdraw_ammount = '';
        })
      }
    }

  },

  created() {
    console.log(this.wallet);
  }

};
</script>

<style>
</style>