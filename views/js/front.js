window.addEventListener('load', function(){
    function updateSaveBtn(){
        if(agcheckout.captcha_public_key){
            if($("#g-recaptcha-response").val() == undefined || $("#g-recaptcha-response").val() == ''){
                $("#btnSaveRegistrationForm").addClass('disabled');
            }else{
                $("#btnSaveRegistrationForm").removeClass('disabled');
            }
        }
    }

    if($('#g-recaptcha').is(':empty') && agcheckout.captcha_public_key){
        grecaptcha.ready(function(){
            grecaptcha.render("g-recaptcha", {
              'sitekey': agcheckout.captcha_public_key,
              'callback': updateSaveBtn
            });
        });
    }
    updateSaveBtn();
});

window.addEventListener('DOMContentLoaded', function(){
    var AgCheckoutObj = {};

    let payment_methods = [];

    Vue.component('loading-div', {
        template: `<div class="div-loading"><svg viewBox="0 0 38 38" xmlns="http://www.w3.org/2000/svg" width="128" height="128" stroke="#007bff"><g fill="none" fill-rule="evenodd"><g transform="translate(1 1)" stroke-width="2"><circle stroke-opacity=".25" cx="18" cy="18" r="18"></circle><path d="M36 18c0-9.94-8.06-18-18-18"><animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="0.8s" repeatCount="indefinite"></animateTransform></path></g></g></svg></div>`
    });

    Vue.use(Maska)

    let app = new Vue({
        el: '.agcheckout',
        data: {
            display: {
                loginForm: false,
                registrationForm: true
            },

            customer_data : agcheckout.customer,
            customerErrors: [],
            addresses : agcheckout.addresses,
            id_address_delivery: agcheckout.cart.id_address_delivery,
            id_address_invoice: agcheckout.cart.id_address_invoice,
            has_custom_address_invoice: false,

            payment_methods: payment_methods,
            payment_method: {},

            total_to_pay: '',
            total_unformatted: 0,
            id_carrier: 0,
            carriers: [],

            isReady : false,
            isLoading: false,
            isLoadingCustomer: false,
            isLoadingDelivery: false,
            isLoadingPayment: false,
            isLoadingAddressConfirmation: false,
            isWaitingForAddressConfirmation: false,

            displayDeliveryAddressModal: false,
            displayInvoiceAddressModal: false,
            displayCartModal: false,

            searchAddress: {street: ''},
            addressToEdit: {},
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

            cart: agcheckout.cart,
            errorsDeliveryAddressModal: [],
            errorsInvoiceAddressModal: [],

            paymentErrors: [],
            voucherCode: '',

            ignoreDeliveryStep: agcheckout.config.ignore_delivery_step
        },
        mounted : async function() {
            this.isReady = true;

            await this.loadCart();

            if (this.id_address_delivery) {
                if (this.ignoreDeliveryStep) {
                    this.loadPaymentModes();
                } else {
                    this.loadCarriers();
                }
            }

            if ($('[name=person_type]').length == 1) {
                this.customer_data.person_type = $('[name=person_type]').val();
            }

            if (this.customer_data.birthday == '' || this.customer_data.birthday == '0000-00-00') {
                $('[name=birthday]').attr('type', 'text');
            } else {
                $('[name=birthday]').attr('type', 'date');
            }
        },
        methods: {
            loadCustomerData : async function(){
                this.isLoadingCustomer = true;

                let r = await axios.get(
                    agcheckout.api_url,
                    {
                        params: {'method' : 'getCustomerData'},
                        headers: {
                            'Cache-Control': 'no-cache',
                            'Pragma': 'no-cache',
                            'Expires': '0',
                        }
                    }
                );

                this.customer_data = r.data.customer_data;

                if (this.customer_data.birthday == '' || this.customer_data.birthday == '0000-00-00') {
                    $('[name=birthday]').attr('type', 'text');
                } else {
                    $('[name=birthday]').attr('type', 'date');
                }

                this.isLoadingCustomer = false;
            },
            saveCustomerData : async function ()
            {
                if (!this.validateInputs()) {
                    return;
                }

                this.isLoadingCustomer = true;

                var data = new FormData();
                data.append('method', 'saveCustomerData');
            
                for (i in app.customer_data) {
                    if (i != 'firstname' && i != 'lastname') {
                        data.append(i, app.customer_data[i]);
                    }
                }
                data.append('g-recaptcha-response', $("#g-recaptcha-response").val());

                try {
                    let r = await axios.post(agcheckout.api_url, data);
                    if (r.data.success) {
                        this.customerErrors=[];
                        this.customer_data = r.data.customer_data;
                    } else {
                        this.customerErrors = [r.data.error];
                    }
                } catch(e) {
                    this.customerErrors = [e];
                }

                this.isLoadingCustomer = false;
                grecaptcha.reset();
                updateSaveBtn();
            },
            login: async function()
            {
                this.isLoadingCustomer = true;

                var data = new FormData();
                data.append('method', 'login');
                data.append('email', app.customer_data.email);
                data.append('password', app.customer_data.password);

                try {
                    let r = await axios.post(agcheckout.api_url, data);
                    if (r.data.success) {
                        if (agcheckout.config.redirect_after_login == '1') {
                            location.reload();
                            return;
                        } else {
                            await this.loadCustomerData();
                            await this.loadAddresses();
                            this.display.loginForm = false;
                            this.display.registrationForm = true;
                        }
                    } else {
                        let error;
                        r.data.error ? error = r.data.error : error = "Ocorreu um erro, tente novamente mais tarde."
                        this.customerErrors = [r.data.error];
                    }
                } catch(e) {
                    this.customerErrors = [e];
                }

                this.isLoadingCustomer = false;
            },
            searchAddressByPostcode: async function(e)
            {
                clearTimeout(this.intervalSearchAddressByPostcode)

                this.intervalSearchAddressByPostcode = setTimeout(async function(){
                    var data = {
                        'method': 'findAddressByPostcode',
                        'postcode': e.target.value
                    };
                    if (data.postcode.length != 8) {
                        return;
                    }

                    app.isLoadingDelivery = true;

                    let r = await axios.get(agcheckout.api_url, {params: data});
                    app.searchAddress = r.data.address;
                    app.isWaitingForAddressConfirmation = true;

                    app.isLoadingDelivery = false;
                }, 300);
            },
            submitCustomerForm: function(){
                saveCustomerData();

                //se foi tudo bem com o cadastro do cliente
                if (typeof this.customer.id !== 'undefined' && this.customer.id > 0) {
                    loadAddresses();
                }
            },
            loadAddresses: async function()
            {
                this.isLoadingDelivery = true;
                
                var data = {
                    'method': 'findAddresses',
                };

                let r = await axios.get(agcheckout.api_url, {params: data});
                app.addresses = r.data.addresses;

                app.id_address_delivery = r.data.id_address_delivery;
                app.id_address_invoice = r.data.id_address_invoice;

                this.isLoadingDelivery = false;
            },
            addressSelected: function(event)
            {
                let target = event.target;

                if (!$(target).is('.agcheckout-address')) {
                    target = $(target).closest('.agcheckout-address');
                }

                let type = $(target).attr('data-type');
                let id = $(target).attr('data-id');

                if (type == 'delivery') {
                    app.id_address_delivery = id;
                } else {
                    app.id_address_invoice = id;
                }
            },
            editAddress: function(address)
            {
                this.displayDeliveryAddressModal = true;

                address.name = address.firstname + ' ' + address.lastname;
                address.phone = address.phone_mobile ?? address.phone;
                address.uf = address.state_iso;
                
                this.addressToEdit = address;
            },
            deleteAddress: async function(address)
            {
                if (!window.confirm("Deseja realmente excluir esse endereço?")) {
                    return;
                }

                var formData = new FormData();
                formData.append('method', 'deleteAddress');
                formData.append('id', address.id_address);

                let r = await axios.post(agcheckout.api_url, formData);
                this.loadAddresses();
            },
            deliveryAddressConfirmed: async function(data){
                try {
                    app.errorsDeliveryAddressModal = [];
                    await this.saveAddress('delivery', data);
                    app.displayDeliveryAddressModal = false;
                } catch (e) {
                    app.errorsDeliveryAddressModal = [e];
                }
            },
            deliveryAddressError: function(err) {
                this.errorsDeliveryAddressModal = [err];
            },
            invoiceAddressConfirmed: async function(data){
                try {
                    app.errorsDeliveryAddressModal = [];
                    await this.saveAddress('invoice', data);
                    app.displayInvoiceAddressModal = false;
                } catch (e) {
                    app.errorsDeliveryAddressModal = [e];
                }
            },
            saveAddress: async function(type, address) {
                this.isLoadingAddressConfirmation = true;

                var formData = new FormData();
                formData.append('method', 'saveAddress');

                for (i in address) {
                    formData.append(i, address[i]);
                }

                formData.append('type', type);

                let r = await axios.post(agcheckout.api_url, formData);
                if (r.data.success) {
                    this.loadCustomerData();
                    
                    this.isWaitingForAddressConfirmation = false;
                    this.isLoadingAddressConfirmation = false;

                    this.loadAddresses();

                    if (type == 'delivery') {
                        this.id_address_delivery = r.data.id_address;
                    } else {
                        this.id_address_invoice = r.data.id_address;
                    }
                } else {
                    throw new Error(r.data.error);
                }
            },
            confirmAddress: async function() {
                saveAddress();
            },
            loadCarriers: async function() {
                this.isLoadingDelivery = true;
                this.payment_methods = [];

                let r = await axios.get(`${agcheckout.api_url}?id_address_delivery=${this.id_address_delivery}&method=getCarriers`,
                    {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache',
                        'Expires': '0',
                    }
                });
                this.carriers = r.data.carriers;
                this.id_carrier = r.data.id_carrier;
                
                this.isLoadingDelivery = false;
            },
            carrierClicked: function(event)
            {
                let target = $(event.target);
                if (!target.is('.carrier')) {
                    target = target.closest('.carrier');
                }

                this.id_carrier = target.attr('data-id');
            },
            loadPaymentModes: async function(){
                this.isLoadingPayment = true;
                this.isLoadingDelivery = true;

                let r = await axios.get(
                    `${agcheckout.api_url}?method=getPaymentOptions&id_carrier=${this.id_carrier}`,
                    {
                        headers: {
                            'Cache-Control': 'no-cache',
                            'Pragma': 'no-cache',
                            'Expires': '0',
                        }
                    }
                );

                this.payment_methods = r.data.options;
                this.total_to_pay = r.data.total_to_pay;
                this.total_unformatted = r.data.total_unformatted;
                this.cart = r.data.cart;

                prestashop.emit('AgCheckout.paymentModesLoaded');

                this.isLoadingPayment = false;
                this.isLoadingDelivery = false;
            },
            loadCart: async function()
            {
                let r = await axios.get(`${agcheckout.api_url}?method=loadCart`,
                    {
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache',
                        'Expires': '0',
                    }
                });

                this.cart = r.data.cart;
                this.total_unformatted = r.data.total_unformatted
            },
            paymentMethodOpened: function(id){
                for (var i in this.payment_methods) {
                    if (this.payment_methods[i].id == id) {
                        this.payment_method = this.payment_methods[i];
                        break;
                    }
                }
            },
            finishOrderClicked: async function(){
                if (!this.validateInputs()) {
                    return;
                }

                this.isLoading = true;

                if (this.total_unformatted == 0) {
                    await this.closeFreeOrder();
                } else {
                    if (this.payment_method.action != '' && this.payment_method.action != null) {
                        location.href = this.payment_method.action;
                    } else if (this.payment_method.form != '' && this.payment_method.form != null) {
                        let parsed = $.parseHTML(this.payment_method.form);
                        let id = $(parsed).filter('form').attr('id');
                        $(`#${id}`).submit();

                        //ocorreu um erro no processamento do pagamento
                        if ($(`#${id} .has-error`).length) {
                            this.isLoading = false;
                        }
                    }
                }

                return false;
            },
            openCartModalClicked: function(){
                this.displayCartModal = true;
            },
            cartModalBackdropClicked: function(){
                this.displayCartModal = false;
            },
            cartModalConfirmClicked: function(){
                this.displayCartModal = false;
            },
            displayLoginFormClicked: function(){
                this.display.loginForm = true;
                this.display.registrationForm = false;
            },
            displayRegistrationFormClicked: function(){
                this.display.loginForm = false;
                this.display.registrationForm = true;
            },
            addCartRule: async function(){
                this.isLoadingPayment = true;
                
                this.paymentErrors = [];
                
                var data = new FormData();
                data.append('method', 'addVoucher');
                data.append('code', this.voucherCode);

                let r = await axios.post(agcheckout.api_url, data);

                if (r.data.success) {
                    this.cart = r.data.cart;
                } else {
                    this.paymentErrors.push(r.data.error);
                }
                
                this.isLoadingPayment = false;
            },
            deleteCartRule: async function(event)
            {
                this.isLoadingPayment = true;

                let target = $(event.target);
                let id = target.closest('.voucher').attr('data-id');

                var data = new FormData();
                data.append('method', 'deleteVoucher');
                data.append('id', id);

                let r = await axios.post(agcheckout.api_url, data);

                if (r.data.success) {
                    await this.loadCart();
                } else {
                    this.paymentErrors.push(r.data.error);
                }

                this.isLoadingPayment = false;
            },
            expand: function(event){
                let target = $(event.target);

                if (!target.is('.payment-method')) {
                    target = target.closest('.payment-method');
                }

                let id = target.attr('data-id');
                this.paymentMethodOpened(id);
    
                prestashop.emit('AgCheckout.paymentModeSelected', {paymentMethod: this.payment_method});
            },
            updateAddressInvoice: async function()
            {
                var data = new FormData();
                data.append('method', 'updateAddressInvoice');
                data.append('id_address_invoice', this.id_address_invoice);
                await axios.post(agcheckout.api_url, data);

                await this.loadPaymentModes();
            },
            closeFreeOrder: async function()
            {
                var data = new FormData();
                data.append('method', 'closeFreeOrder');

                let r = await axios.post(agcheckout.api_url, data);
                let url = r.data.redirect_url;

                location.href = url;
            },
            validateInputs: function()
            {
                let ret = true;

                $('.agcheckout input, .agcheckout checkbox').each(function(){
                    if (!this.reportValidity()) {
                        ret = false;
                    }
                });

                return ret;
            }
        },
        computed: {
            paymentMethodSelected(){
                return (id) => id == this.payment_method.id;
            }
        },
        watch: {
            id_address_delivery: function(){
                if (!this.has_custom_address_invoice) {
                    this.id_address_invoice = this.id_address_delivery;
                }

                this.loadCarriers();
            },
            id_address_invoice: async function(){
                await this.updateAddressInvoice();
            },
            id_carrier: async function() {
                if (this.id_carrier > 0) {
                    this.loadPaymentModes();
                } else {
                    this.payment_methods = [];
                }
            },
            addresses: function() {
                
            }
        }
    });

    $('.agcheckout-address').on('click', 'div, p', function(e){
        if ($(e.target).is('input')) {
            return false;
        }
        $(this).closest('.agcheckout-address').find('input').click();
    });

    //LOGIN
	    var container_login = $('<div/>', {class: 'agcustomers login-container'}); 
	    container_login.load(
	    	agcheckout.urls.facebook,
	    	{
	    		action: 'getFacebookButton',
	    		form: 'registration_checkout'
	    	},
            function (params) {
                if($(".btn-facebook").length == 0){
                    var btn_facebook =false; 
                }else{
                    var btn_facebook =true; 
                }
                $(".login-container").prepend("<div class='' id='buttonDiv'></div>");
                $("#buttonDiv").css("margin-bottom","5px");
                if(agcheckout.google_client_id != ''){
                    if(!btn_facebook){
                        $(".login-container").append("<p>Utilize a sua conta para facilitar a sua identificação. Apenas o seu nome, sobrenome e endereço de e-mail será armazenado.</p><hr>");
                    }
                    
                    google.accounts.id.initialize({
                        client_id: agcheckout.google_client_id,
                        callback: handleResponse
                    });
                  
                    if(agcheckout.google_prompt && !agcheckout.logged){
                        google.accounts.id.prompt(
                            function (prmts) {
                                console.log(prmts);
                            }
                        );
                    }
                  
                    google.accounts.id.renderButton(
                        document.getElementById("buttonDiv"),
                        {
                            type: agcheckout.configs_btn.google_type_btn,
                            theme: agcheckout.configs_btn.google_theme_btn,
                            size: agcheckout.configs_btn.google_size_btn,
                            text: agcheckout.configs_btn.google_text_btn,
                            shape: agcheckout.configs_btn.google_shape_btn,
                            logo_alignment: agcheckout.configs_btn.google_logo_btn
                        }  
                    );
                }
            }
	    );
	    $('.customer-top-action').prepend(container_login);

    function updateSaveBtn(){
        if(agcheckout.captcha_public_key){
            if($("#g-recaptcha-response").val() == undefined || $("#g-recaptcha-response").val() == ''){
                $("#btnSaveRegistrationForm").addClass('disabled');
            }else{
                $("#btnSaveRegistrationForm").removeClass('disabled');
            }
        }
    }
})

function handleResponse(response) {
	var info_google = parseJwt(response.credential);
	redirectGoogleBtn(info_google);
 
}

function parseJwt (token) {
    var base64Url = token.split('.')[1];
    var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    var jsonPayload = decodeURIComponent(window.atob(base64).split('').map(function(c) {
        return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));

    return JSON.parse(jsonPayload);
};

function redirectGoogleBtn(info_google)
{
		$.ajax({
			url: agcheckout.urls.google,
			type: 'GET',
			dataType: 'JSON',
			data: {
				email: info_google.email
			}
		}).done(function(response) {
			if(response.success){
				if(response.duplicated){
					document.location.reload(true);
				}else{
					window.location.replace(agcheckout.urls.create_acount+"?&firstname="+info_google.given_name+"&lastname="+info_google.family_name+"&email="+info_google.email);
				}
			}
		  });
}
