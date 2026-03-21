// register modal component
window.addEventListener('load', function(){
    Vue.component("agcheckout-cart-modal", {
        props: ['products', 'total_products', 'total_shipping', 'total_discounts', 'total_value'],
        data: function(){
            return {
                displayHeader: false
            };
        },
        methods: {
            expand: function(){
                // this.is_open = true;
                this.$emit("open", this.id);
            }
        },
        template: `
            <agmodal :displayHeader="displayHeader" classname="cart-modal" @backdropClicked="backdropClicked">
                <template slot="body">
                    <div class="products">
                        <div class="title ">
                            <div>Ref.</div>
                            <div>Imagem.</div>
                            <div>Produto</div>
                            <div>Total</div>
                        </div>
                        <div class="product " v-for="product in products">
                            <div class="reference">{{ product.reference }}</div>
                            <div class="image"><img :src="product.image"/></div>
                            <div class="name">
                                <div>{{ product.cart_quantity }}x {{ product.name }}</div>
                                <div>{{ product.combination }}</div>
                            </div>
                            <div class="totals">{{ product.price }}</div>
                        </div>
                    </div>

                    <div class="subtotals">
                        <div>
                            <div>Total de Produtos</div>
                            <div>{{ total_products }}</div>
                        </div>

                        <div>
                            <div>Frete</div>
                            <div>{{ total_shipping }}</div>
                        </div>

                        <div>
                            <div>Descontos</div>
                            <div>{{ total_discounts }}</div>
                        </div>

                        <div>
                            <div>Total a Pagar</div>
                            <div>{{ total_value }}</div>
                        </div>
                    </div>
                </template>

                <template slot="footer">
                    <button type="button" class="btn btn-primary" @click="confirm">Confirmar</button>
                </template>
            </agmodal>
        `,
        methods: {
            backdropClicked: function(){
                this.$emit('backdropclicked');
            },
            confirm: function(){
                this.$emit('confirmclicked');
            }
        }
    });
});