import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({

    state: {

        token: sessionStorage.getItem('userToken') || localStorage.getItem('userToken') || null,
        user: JSON.parse(sessionStorage.getItem('loggedUser')) || JSON.parse(localStorage.getItem('loggedUser')) || null,
        wallet: JSON.parse(sessionStorage.getItem('wallet')) || JSON.parse(localStorage.getItem('wallet')) || null,
        user_id: sessionStorage.getItem('user_id') || localStorage.getItem('user_id') || null,

    },

    mutations: {

        storeRememberedUserToken(state, token) {

            this.token = token;
            localStorage.setItem('userToken', token);

        },

        storeRememberedUser(state, user, id) {
            this.user = user;
            this.user_id = id;
            localStorage.setItem('loggedUser', user);
            localStorage.setItem('user_id', id);
        },

        storeUserToken(state, token){
            this.state.token = token;
            sessionStorage.setItem('userToken', token);
        },

        storeUser(state, user, id){
            this.state.user = user;
            let jsonUser = JSON.stringify(user);
            sessionStorage.setItem('loggedUser', jsonUser);
        },

        storeUserId(state, id){
            this.state.user_id = id;
            sessionStorage.setItem('user_id', id);
        },

        storeWallet(state, wallet){
            this.state.wallet = wallet;
            let jsonWallet = JSON.stringify(wallet);
            localStorage.setItem('wallet', jsonWallet);
            sessionStorage.setItem('wallet', jsonWallet);
        },

        revokeUserToken(state, token) {
            this.state.token = " ";
            localStorage.removeItem('userToken');
            sessionStorage.removeItem('userToken');
        },

        revokeUser(state, user) {
            this.state.user = "";
            this.state.user_id = "";
            localStorage.removeItem('user_id');
            sessionStorage.removeItem('user_id');
            localStorage.removeItem('loggedUser');
            sessionStorage.removeItem('loggedUser');
        },

        revokeWallet(state, wallet){
            this.state.wallet = '';
            localStorage.removeItem('wallet');
            sessionStorage.removeItem('wallet');
        },

        setBalance(state, wallet){
            console.log("Store wallet", wallet);
            if(wallet.balance){
                this.state.wallet.balance = wallet.balance;
            }
            if(wallet.reserved){
                this.state.wallet.reserved = wallet.reserved;
            }
            let jsonWallet = JSON.stringify(this.state.wallet);
            localStorage.setItem('wallet', jsonWallet);
            sessionStorage.setItem('wallet', jsonWallet);
        }

    },

    getters: {

        getUserToken(state){
            return state.token;
        },

        getUser(state){
            return state.user;
        },

        getUserId(state){
            return state.user_id;
        },

        getWallet(state){
            console.log('this', this);
            console.log('state', state);
            return state.wallet;
        }

    }


});