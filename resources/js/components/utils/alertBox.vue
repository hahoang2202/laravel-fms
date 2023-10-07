<template>
	
	<div :class="[type, animation]" role="" v-if="show" >
		<div style="float: right; margin-top: -6px;">
			<button type="button" class="btn" @click="close()">X</button>
		</div>
		{{message}} 
		<div v-if="errors.length > 0">
			<ul v-for="(error, index) in errors" :key="index">
				<li>{{error}}</li>
			</ul>
		</div>
	</div>

</template>

<script type="text/javascript">
	
	export default {
		props: {
			message: String,
			errors: {
				type: Array,
				default: function() { return new Array()}
			},
			show: Boolean,
			type: String,
			animation: String,
		},
		methods: {
			close(){
				this.$emit('close');
			}
		}
	};
</script>