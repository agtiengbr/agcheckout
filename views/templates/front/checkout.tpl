{extends file='layouts/layout-full-width.tpl'}

{block name='left_column'}{/block}
{block name='right_column'}{/block}

{block name='content'}
    <div class='agcheckout row is-loadable hidden' v-bind:class="{ ready : isReady }">
        <template v-if="isReady && cart.products.length == 0">
            <div class="alert alert-info">
                {if $no_products_message == ''}
                    {l s='O seu carrinho de compras está vazio.' mod='agcheckout'}<a href='{$link->getPageLink('index')}'>{l s='Clique aqui' mod='agcheckout'}</a> {l s='para retornar às compras.' mod='agcheckout'}
                {else}
                    {$no_products_message nofilter}
                {/if}
            </div>
        </template>
        <template v-else>
            <agcheckout-address-modal
                v-if="displayDeliveryAddressModal"
                v-on:address-saved="deliveryAddressConfirmed"
                v-on:error="deliveryAddressError"
                @cancel="displayDeliveryAddressModal=false"
                v-bind:errors="this.errorsDeliveryAddressModal"
                :address-prop="addressToEdit"
            ></agcheckout-address-modal>

            <agcheckout-address-modal
                v-if="displayInvoiceAddressModal"
                v-on:address-saved="invoiceAddressConfirmed"
                @cancel="displayInvoiceAddressModal=false"
                :errors="errorsInvoiceAddressModal"
            ></agcheckout-address-modal>

            <loading-div v-if="isLoading"></loading-div>

            <div class="col-lg-4 is-loadable">
                <div class="card customer_form">
                    <loading-div v-if="isLoadingCustomer"></loading-div>

                    <div class="card-header">
                        <h5><i class="material-icons">person</i> {l s='Dados Pessoais' mod='agcheckout'}</h5>
                    </div>

                    <div class="card-body">
                        <div v-if="customer_data.id == 0 || typeof customer_data.id === 'undefined'" class="customer-top-action">
                            <template v-if="display.registrationForm">
                                {l s='Já possui cadastro?' mod='agcheckout'}<a href='javascript:void(0);' @click="displayLoginFormClicked">{l s='Clique aqui' mod='agcheckout'}</a>
                            </template>
                            <template v-else="display.configForm">
                                {l s='Não possui cadastro?' mod='agcheckout'}<a href='javascript:void(0);' @click="displayRegistrationFormClicked">{l s='Clique aqui' mod='agcheckout'}</a>
                            </template>
                        </div>

                        <div class="alert alert-danger" v-if="customerErrors.length">
                            <p v-for="error in customerErrors">{{ error }}</p>
                        </div>

                        <template v-if="display.registrationForm">
                            <div class='{if $person_types|count == 1}hidden {/if}mb-1'>
                                {foreach from=$person_types item=person_type}
                                    <label for="person_type_{$person_type->getId()}"> <input id="person_type_{$person_type->getId()}" type="radio"  v-model="customer_data.person_type" value="{$person_type->getId()}" name="person_type"/> {$person_type->getName()}</label>
                                {/foreach}
                            </div>
                            
                            <div class="mb-2">
                                <div class='input-container'>
                                    <input class="form-control" type="text" name="name" placeholder="{l s='Nome Completo' mod='agcheckout'}" v-model="customer_data.name" required />
                                </div>

                                <div class='input-container'>
                                    <input class="form-control" type="email" name="email" placeholder="{l s='E-mail' mod='agcheckout'}" v-model="customer_data.email" required />
                                </div>

                                <div class='input-container'>
                                    <input v-if="customer_data.id == 0 || typeof customer_data.id === 'undefined'" class="form-control" type="password" name="password" placeholder="Senha" v-model="customer_data.password" required />
                                    <a v-if="typeof customer_data.id == 'undefined'" class='pull-right mb-1' href='{$link->getPageLink('password')}'>{l s='Recuperar senha' mod='agcheckout'}</a>
                                </div>

                                <div class='input-container'>
                                    <input class="form-control" type="text" name="bday" placeholder="Data de Nascimento" v-model="customer_data.birthday" required onfocus="this.type = 'date'" onfocusout="this.type = this.value == '' || this.value == '0000-00-00' ? 'text' : 'date'"/>
                                </div>
                            
                                {foreach from=$person_types item=person_type}
                                    {foreach from=$fields[$person_type->getId()] item=field}

                                        {if $field->getId() != 'firstname' && $field->getId() != 'lastname' && $field->getId() !== 'birthday'}
                                            <div class='input-container'>
                                                <input
                                                    {if $field->getId() == 'cpf'}v-maska="'###.###.###-##'"{/if}
                                                    {if $field->getId() == 'cnpj'}v-maska="'##.###.###/####-##'"{/if}
                                                    v-if="customer_data.person_type == '{$person_type->getId()}'"
                                                    v-model="customer_data.{$field->getId()}"
                                                    class="form-control"
                                                    type="text"
                                                    name="{$field->getId()}" placeholder="{$field->getName()}"
                                                    {if $field->getRequired()} required {/if}
                                                />
                                            </div>
                                        {/if}
                                    {/foreach}
                                {/foreach}

                            </div>

                            <div class="mb-2">
                                {foreach from=$extraFields key=module item=moduleFields}
                                    {if $module=='agcustomers'}
                                        {continue}
                                    {/if}

                                    {foreach from=$moduleFields item=field}
                                        {if $field->getType() == 'text'}
                                            <div class="input-container">
                                                <input class='form-control' type="text" placeholder="{$field->getLabel()}" name="{$field->getName()}" {if $field->isRequired()}required{/if}/>
                                            </div>
                                        {else if $field->getType() == 'checkbox'}
                                            <label for="{$field->getName()}">
                                                <input type="checkbox" id="{$field->getName()}" name="{$field->getName()}" {if $field->isRequired()}required{/if} {if $field->getValue()}checked="checked"{/if}>
                                                {$field->getLabel() nofilter}
                                            </label>
                                        {/if}
                                    {/foreach}
                                {/foreach}

                                <label for="checkbox">
                                    <input type="checkbox" id="checkbox" v-model="customer_data.newsletter">
                                    {l s='Inscrever-se em nossa newsletter' mod='agcheckout'}
                                </label>
                            </div>
                            <div class="mb-2" id="g-recaptcha">
                            </div>

                            <span class="btn btn-primary" id="btnSaveRegistrationForm" v-on:click="saveCustomerData">{l s='Salvar' mod='agcheckout'}</span>
                        </template>
                        
                        <template v-if="display.loginForm">
                            <div class='input-container'>
                                <input class="form-control" type="email" name="email" placeholder="E-mail" v-model="customer_data.email" required />
                            </div>

                            <div class='input-container'>
                                <input class="form-control" type="password" name="password" placeholder="Senha" v-model="customer_data.password" required />
                            </div>

                            <span class="btn btn-primary" v-on:click="login">{l s='Salvar' mod='agcheckout'}</span>
                        </template>
                    </div>
                </div>
            </div>

            <div v-if="ignoreDeliveryStep == false" class="col-lg-4 is-loadable">
                <loading-div v-if="isLoadingDelivery"></loading-div>

                <div class="card address-container">

                    <div class="card-header">
                        <h5><i class="material-icons">place</i> {l s='Entrega' mod='agcheckout'}</h5>
                    </div>

                    <div class="card-body">
                        <template v-if="customer_data.id == '' || customer_data.id == 0 || customer_data.id == null">
                            <div class='alert alert-warning'>{l s='Por favor conclua o seu cadastro primeiro.' mod='agcheckout'}</div>
                        </template>
                        <template v-else>
                            <div v-if="addresses !== null && addresses.length > 0" class="delivery-container">
                            </div>
                            <template v-if="addresses === null || typeof addresses.length === 'undefined' || addresses.length == 0">
                                <p>{l s='Você ainda não tem um endereço cadastrado.' mod='agcheckout'}</p>
                                <span class="btn btn-primary mt-1" @click="displayDeliveryAddressModal=true; return false;">{l s='Novo Endereço' mod='agcheckout'}</span>
                            </template>
                            <template v-else>
                                <p class="mb-1">{l s='O seu pedido será enviado para:' mod='agcheckout'}</p>

                                <div class="addresses mb-1">
                                    <div
                                        v-for="address in addresses"
                                        class="agcheckout-address"
                                        :data-id="address.id_address"
                                        @click="addressSelected($event)"
                                        data-type="delivery"
                                    >
                                        <div>
                                            <input type="radio" name="id_address_delivery" :value="address.id_address" v-model="id_address_delivery" />
                                        </div>
                                        <div>
                                            <b>{{ address.street }}, {{ address.number }}</b>
                                            <br>
                                            <b>{{ address.postcode }}</b>
                                            <p><small>{{ address.district }} - {{ address.city }}, {{ address.state}} - {{ address.country }}</small></p>
                                        </div>
                                        <div><i class="material-icons" @click.prevent="editAddress(address)">edit</i></div>
                                        <div><i class="material-icons" @click.prevent="deleteAddress(address)">delete</i></div>
                                    </div>
                                </div>

                                <span class="btn btn-primary" @click="displayDeliveryAddressModal=true; return false;">Novo Endereço</span>
                            </template>
                        </template>
                    </div>
                </div>
                
                <div v-if="addresses !== null && addresses.length > 0" class="card carriers-container">

                    <div class="card-header">
                        <h5><i class="material-icons">local_shipping</i> {l s='Transportadora' mod='agcheckout'}</h5>
                    </div>

                    <div class="card-body carriers">
                        <p class="mb-1">{l s='O prazo de entrega começa a contar a partir da confirmação do pagamento' mod='agcheckout'}</p>
                        <div v-if="typeof carriers.length !== 'undefined'" v-for="carrier in carriers"
                        >
                            <div class="carrier" @click="carrierClicked($event)" :data-id="carrier.id_carrier">
                                <div class="carrier-selection"><input type="radio" name="id_carrier" :value="carrier.id_carrier" v-model="id_carrier" /></div>
                                {* <div class="carrier-image"><img v-if="carrier.img != ''" :src="carrier.img" /></div> *}
                                <div class="carrier-data">
                                    <div><b>{{ carrier.name }}</b></div>
                                    <div>
                                        <p class="carrier-delay"><small>{l s='Prazo de entrega:' mod='agcheckout'} {{ carrier.delay }}</small></p>
                                        <p class='carrier-shipping-cost'><small>{l s='Custo da entrega:' mod='agcheckout'} {{ carrier.shipping_cost }}</small></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="col-lg-4 is-loadable">
                {*bloco de endereço no carrinho virtual *}
                <h5><i class="material-icons">place</i> {l s='Endereço' mod='agcheckout'}</h5>

                <template v-if="customer_data.id == '' || customer_data.id == 0 || customer_data.id == null">
                    <div class='alert alert-warning'>{l s='Por favor conclua o seu cadastro primeiro.' mod='agcheckout'}</div>
                </template>
                <template v-else>
                    <p>{l s='O endereço de faturamento do seu pedido é:' mod='agcheckout'}</p>
                    <div class="addresses">
                        <div v-for="address in addresses" class="agcheckout-address">
                            <div><input type="radio" name="id_address_invoice" :value="address.id_address" v-model="id_address_invoice" /></div>
                            <div>
                                <b>{{ address.postcode }} | {{ address.street }}, {{ address.number }}</b>
                                <p><small>{{ address.district }} - {{ address.city }}, {{ address.state}} - {{ address.country }}</small></p>
                            </div>
                            <div><i class="material-icons" @click.prevent="editAddress(address)">edit</i></div>
                            <div><i class="material-icons" @click.prevent="deleteAddress(address)">delete</i></div>
                        </div>
                    </div>

                    <a href='javascript:;' class="btn btn-primary new-address" @click="displayInvoiceAddressModal=true; return false;">{l s='Novo Endereço' mod='agcheckout'}</a>
                </template>
            </div>

            <div class="col-lg-4 is-loadable">
                <loading-div v-if="isLoadingPayment"></loading-div>

                <div v-if="!ignoreDeliveryStep" class="card invoicing">
                    <div class="card-header">
                        <h5><i class="material-icons">receipt</i> {l s='Faturamento' mod='agcheckout'}</h5>
                    </div>

                    <div class="card-body">
                        <template v-if="customer_data.id == '' || customer_data.id == 0 || customer_data.id == null">
                            <div class='alert alert-warning'>{l s='Por favor conclua o seu cadastro primeiro.' mod='agcheckout'}</div>
                        </template>
                        <template v-else-if="id_address_delivery == null || !addresses.length">
                            <div class='alert alert-warning'>{l s='Selecione um endereço de envio e uma transportadora para prosseguir.' mod='agcheckout'}</div>
                        </template>
                        <template v-else>
                            <div v-if="has_custom_address_invoice == false">
                                <p>{l s='O endereço de faturamento do seu pedido é o mesmo do endereço de entrega.' mod='agcheckout'} <a href='javascript:void(0);'
                                        class="new-address" @click="has_custom_address_invoice=true">{l s='Faturar para Outro
                                        Endereço' mod='agcheckout'}</a></p>
                            </div>
                            <div v-else>
                                <p class="mb-1">{l s='O endereço de faturamento do seu pedido é:' mod='agcheckout'}</p>
                                <div class="addresses mb-1">
                                    <div v-for="address in addresses" class="agcheckout-address">
                                        <div><input type="radio" name="id_address_invoice" :value="address.id_address"
                                                v-model="id_address_invoice" /></div>
                                        <div>
                                            <b>{{ address.postcode }} | {{ address.street }}, {{ address.number }}</b>
                                            <p><small>{{ address.district }} - {{ address.city }}, {{ address.state}} -
                                                {{ address.country }}</small></p>
                                        </div>

                                        <div><i class="material-icons" @click.prevent="editAddress(address)">edit</i></div>
                                        <div><i class="material-icons" @click.prevent="deleteAddress(address)">delete</i></div>
                                    </div>
                                </div>

                                <a href='javascript:;' class="btn-primary new-address"
                                    @click="displayInvoiceAddressModal=true; return false;">{l s='Novo Endereço' mod='agcheckout'}</a>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="payment card">
                    <template v-if="total_unformatted === 0 && (id_address_delivery != null && addresses.length)">
                        <p class="card-body">{l s='Por se tratar de um pedido gratuito, nenhum pagamento é necessário.' mod='agcheckout'}</p>
                    </template>
                    <template v-else-if="payment_methods.length > 0 && ((id_carrier != 0 && id_carrier != null) || ignoreDeliveryStep)">
                        
                        <div class="card-header">
                            <h5><i class="material-icons">credit_card</i> {l s='Pagamento' mod='agcheckout'}</h5>
                        </div>    
                    
                        <div class="card-body">
                            <template v-if="customer_data.id == '' || customer_data.id == 0 || customer_data.id == null">
                                <div class='alert alert-warning'>{l s='Por favor conclua o seu cadastro primeiro.' mod='agcheckout'}</div>
                            </template>
                            <template v-else-if="cart.id_address_invoice == 0">
                                <div class='alert alert-warning'>{l s='Por favor informe o seu endereço primeiro.' mod='agcheckout'}</div>
                            </template>
                            <template v-else>
                                <div class="alert alert-danger" v-if="paymentErrors.length != 0">
                                    <ul>
                                        <li v-for="error in paymentErrors">{{ error }}</li>
                                    </ul>
                                </div>

                                <div class="totals">
                                    <p>{l s='Possui um cupom de desconto? Insira abaixo.' mod='agcheckout'}</p>

                                    <div class="vouchers">
                                        <div class="vouchers-form">
                                            <input type="text" placeholder="Codigo do cupom" v-model="voucherCode"
                                                class="form-control" />
                                            <button class="btn btn-primary" @click="addCartRule">{l s='Adicionar' mod='agcheckout'}</button>
                                        </div>

                                        <div class='vouchers-list' v-if="cart.vouchers.length > 0"
                                            v-for="voucher in cart.vouchers">
                                            <div class="voucher" :data-id="voucher.id">
                                                <div class="voucher-name">{{ voucher.name }}</div>
                                                <div class="voucher-discount">-{{ voucher.discountValueFormatted}}</div>
                                                <i class="material-icons" @click="deleteCartRule">delete</i>
                                            </div>
                                        </div>
                                    </div>

                                    <div v-if="id_carrier != 0 && id_carrier != null || ignoreDeliveryStep">
                                        <b>
                                            {l s='Total a Pagar:' mod='agcheckout'} {{ cart.subtotals.total }}
                                        </b>
                                        <br>
                                        <a href='javascript:void(0);' @click="openCartModalClicked(); return false;">{l s='Detalhes' mod='agcheckout'}</a>
                                    </div>
                                </div>

                                <agcheckout-cart-modal v-if="displayCartModal" :products="cart.products"
                                    :total_products="cart.subtotals.products" :total_shipping="cart.subtotals.shipping"
                                    :total_discounts="cart.subtotals.discount" :total_value="cart.subtotals.total"
                                    @backdropclicked="cartModalBackdropClicked" @confirmclicked="cartModalConfirmClicked">
                                </agcheckout-cart-modal>

                                <div class="payment-methods">
                                    <div class="payment-method" v-for="payment_method_loop in payment_methods"
                                        :data-id="payment_method_loop.id" v-on:click="expand($event)">
                                        <div><input type="radio" name="id_payment_mode"
                                                :checked="payment_method.id == payment_method_loop.id" /></div>
                                        <div>
                                            <p>{{ payment_method_loop.call_to_action_text }}</p>
                                        </div>
                                    </div>
                                </div>

                                <template v-for="payment_method_loop in payment_methods">
                                    <div class="payment-method-body mt-1" v-if="paymentMethodSelected(payment_method_loop.id)">
                                        <template v-if="payment_method_loop.form != null">
                                            <div v-html="payment_method_loop.form"></div>
                                        </template>
                                        <template v-else>
                                            <div v-html="payment_method.additional_details"></div>
                                        </template>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </template> 
                </div>

                <button v-if="(payment_method.id || (total_unformatted === 0 && customer_data.id)) && addresses.length && id_address_delivery && id_carrier !== 0" 
                        class="btn btn-primary" v-on:click="finishOrderClicked">
                    {l s='Finalizar Compra' mod='agcheckout'}
                </button>

            </div>
        </template>
    </div>
{/block}
