// register modal component
window.addEventListener('load', function(){
    Vue.component("payment-mode", {
        props: ['name', 'additional_details', 'form', 'is_open', 'id'],
        methods: {
            expand: function(){
                // this.is_open = true;
                this.$emit("open", this.id);
            }
        },
        template: `
        <div class="payment-method">
            <div v-on:click="expand">{{ name }}</div>
            <template v-if="form != null && this.is_open">
                <div v-html="form"></div>
            </template>
            <template v-else-if="this.is_open">
                <div v-html="additional_details"></div>
            </template>
        </div>
        `
    });
});