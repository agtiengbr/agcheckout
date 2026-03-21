window.addEventListener('load', function(){
    Vue.component("agcheckout-address-modal", {
        props: ['errors', 'addressProp'],
        data: function(){
            return {
                displaySearchInput: {
                    type: Boolean,
                    default: true
                },
                brazil_states : {
                    'AC':'Acre',
                    'AL':'Alagoas',
                    'AP':'Amapá',
                    'AM':'Amazonas',
                    'BA':'Bahia',
                    'CE':'Ceará',
                    'DF':'Distrito Federal',
                    'ES':'Espírito Santo',
                    'GO':'Goiás',
                    'MA':'Maranhão',
                    'MT':'Mato Grosso',
                    'MS':'Mato Grosso do Sul',
                    'MG':'Minas Gerais',
                    'PA':'Pará',
                    'PB':'Paraíba',
                    'PR':'Paraná',
                    'PE':'Pernambuco',
                    'PI':'Piauí',
                    'RJ':'Rio de Janeiro',
                    'RN':'Rio Grande do Norte',
                    'RS':'Rio Grande do Sul',
                    'RO':'Rondônia',
                    'RR':'Roraima',
                    'SC':'Santa Catarina',
                    'SP':'São Paulo',
                    'SE':'Sergipe',
                    'TO':'Tocantins'
                },

                searchAddress: {
                    type: Object,
                    default: function(){
                        return {};
                    }
                },
                postcodeToSearch: '00000-000',
                address: {uf:0},

                isLoading: false,

                disableInputs: false,
                disableAllInputs: false,
                disableSubmit: false,

                allowAddressEdit: agcheckout.config.allow_address_edit
            };
        },
        mounted: function(){
            if (this.addressProp !== undefined) {
                this.address = this.addressProp;
                this.postcodeToSearch = this.addressProp.postcode;
            } 
        },
        methods: {
            searchAddressByPostcode: async function(postcode){
                this.isLoading = true;

                var data = {
                    'method': 'findAddressByPostcode',
                    'postcode': postcode.replace('-', '')
                };

                let r = await axios.get(agcheckout.api_url, {params: data});

                this.isLoading = false;

                if (r.data.success) {
                    return r.data.address;
                }

                if (r.data.error == 'Endereço não encontrado') {
                    return {
                        street: '',
                        city: '',
                        uf: '0',
                        other: '',
                        district: ''
                    };
                }
            },
            confirmAddress: function() {
                this.errors = [];
                
                if (agcheckout.config.required_phone == true) {
                    if (typeof this.address.phone == 'undefined' || this.address.phone == '') {
                        this.errors.push('Telefone para contato é obrigatório.');
                        // this.$emit('error', 'Nome da rua é obrigatório.');
                    }
                }

                if (typeof this.address.street == 'undefined' || this.address.street == '') {
                    this.errors.push('Nome da rua é obrigatório.');
                    // this.$emit('error', 'Nome da rua é obrigatório.');
                }

                if (typeof this.address.number == 'undefined' || this.address.number == '') {
                    this.errors.push('Número da rua é obrigatório.');
                }

                if (typeof this.address.district == 'undefined' || this.address.district == '') {
                    this.errors.push('Bairro é obrigatório.');
                }

                if (typeof this.address.city == 'undefined' || this.address.city == '') {
                    this.errors.push('Cidade é obrigatória.');
                }

                if (typeof this.address.uf == 'undefined' || this.address.uf == '') {
                    this.errors.push('Estado é obrigatório.');
                }
                
                if (this.errors.length == 0) {
                    this.$emit('address-saved', this.address);
                }
            },
            focusPostcode: function(){
                if (this.postcodeToSearch == '00000-000') {
                    this.postcodeToSearch = '';
                }
            },
            cancel: function() {
                this.$emit('cancel');
            }
        },
        watch: {
            postcodeToSearch: async function(val){
                if (val.length == 9) {
                    this.searchAddress = await this.searchAddressByPostcode(val);

                    this.address.postcode = this.searchAddress.postcode;
                    this.address.street = this.searchAddress.street;
                    this.address.district = this.searchAddress.district;
                    this.address.city = this.searchAddress.city;
                    this.address.uf = this.searchAddress.uf;
                }
            }
        },
        template: `
            <agmodal classname="address-modal">
                <loading-div v-if="isLoading"></loading-div>

                <template slot="body">
                    <div class="alert alert-danger" v-if="errors.length > 0">
                        <p v-for="error in errors">{{ error }}</p>
                    </div>
                    <span v-if="displaySearchInput">
                        <p>Para cadastrar um novo endereço, informe o CEP de entrega abaixo.</p>
                        <input type="text" placeholder="00000-000" name="postcode" v-model="postcodeToSearch" v-maska="'#####-###'" v-on:focusin="focusPostcode" class="form-control"/>
                    </span>

                    <p>Por favor confirme os dados do seu endereço abaixo.</p>

                    <form>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Destinatário' v-model="address.name" /></div>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Telefone para Contato' v-model="address.phone" v-maska="['(##) #####-####', '(##) ####-####']"/></div>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Nome da Rua' v-model="address.street" v-bind:disabled="!allowAddressEdit || (searchAddress.street !== null && typeof searchAddress.street !== 'undefined' && searchAddress.street != '')" /></div>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Número' v-model="address.number" /></div>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Bairro' v-model="address.district" v-bind:disabled="!allowAddressEdit || (searchAddress.district !== null && typeof searchAddress.district !== 'undefined' && searchAddress.district != '')" /></div>
                        <div class='input-container'><input type="text" class="form-control" placeholder='Cidade' v-model="address.city" v-bind:disabled="!allowAddressEdit || (searchAddress.city !== null && typeof searchAddress.city !== 'undefined' && searchAddress.city != '')"  /></div>
                        
                        <div class='input-container'>
                            <select v-model="address.uf" v-bind:disabled="!allowAddressEdit || (searchAddress.uf !== null && typeof searchAddress.uf !== 'undefined' && searchAddress.uf != '')" class="form-control" placeholder="Estado">
                                <option value="0">Estado</option>
                                <option v-for="(name, acronym) in brazil_states" :value="acronym">{{ name }}</option>
                            </select>
                        </div>

                        <div class='input-container'><textarea class='form-control' placeholder="Complemento" v-model="address.other"></textarea></div>
                    </form>
                    <slot name="body">
                    </slot>
                </template>

                <template slot="footer">
                    <button type="button" class="btn btn-primary" @click="confirmAddress" v-bind:disabled="disableSubmit">Confirmar</button>
                    <button type="button" class="btn btn-danger" @click="cancel" data-dismiss="modal">Cancelar</button>
                </template>
            </agmodal>
        `
    });
});
