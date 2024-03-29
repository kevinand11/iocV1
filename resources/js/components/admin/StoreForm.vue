<template>
    <form-extend data-name="Store" :mode="mode">
        <template slot="inputFields">
            <div class="text-center">
                <img :src="decideImage| appendURL" alt='' id="profile" width="80px" height="80px"/><br>
                <span class="lead">{{ $t('storeFormPreview') }}</span>
            </div>
            <div class="form-group row">
                <label for="image" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormPhotoLabel') }}</label>
                <div class="col-sm-9">
                    <input type="file" @change="setPicture" name="image" :class="{ 'is-valid': !form.errors.has('image') && isSubmitted,'is-invalid': form.errors.has('image') }"
                           class="form-control-file" id="image">
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormNameLabel') }}</label>
                <div class="col-sm-9">
                    <input v-model="form.name" id="name" type="text" name="name" :placeholder="$t('storeFormNamePlaceHolder')" autocomplete="business_name"
                           class="form-control" :class="{ 'is-valid': !form.errors.has('name') && isSubmitted,'is-invalid': form.errors.has('name') }">
                    <has-error :form="form" field="name"></has-error>
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormEmailLabel') }}</label>
                <div class="col-sm-9">
                    <input v-model="form.email" id="email" name="email" type="email" :placeholder="$t('storeFormEmailPlaceHolder')" autocomplete="business_email"
                           class="form-control" :class="{ 'is-valid': !form.errors.has('email') && isSubmitted,'is-invalid': form.errors.has('email') }">
                    <has-error :form="form" field="email"></has-error>
                </div>
            </div>
			<div class="form-group row">
				<label for="phone" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormPhoneLabel') }}</label>
				<div class="col-sm-9">
					<div class="row">
						<div class="col-sm-4">
							<select name="country_code" id="country" class="form-control" v-model="form.phone.phone_country"
									:class="{ 'is-invalid': form.errors.has('phone.phone_country') }" autocomplete="business_phone_country">
								<option v-for="(country,code) in getCountries" :value="code" :key="code">{{ country }}</option>
							</select>
							<has-error :form="form" field="phone.phone_country"></has-error>
						</div>
						<div class="col-sm-8">
							<input type="tel" v-model="form.phone.phone" class="form-control" id="phone" :placeholder="$t('storeFormPhonePlaceHolder')"
								   :class="{ 'is-invalid': form.errors.has('phone.phone') }" autocomplete="business_phone">
							<has-error :form="form" field="phone.phone"></has-error>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group row">
                <label for="link" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormLinkLabel') }}</label>
                <div class="col-sm-9">
					<input v-model="form.link" id="link" type="url" name="link" :placeholder="$t('storeFormLinkPlaceHolder')" autocomplete="business_link"
                           class="form-control" :class="{ 'is-valid': !form.errors.has('link') && isSubmitted,'is-invalid': form.errors.has('link') }">
                    <has-error :form="form" field="link"></has-error>
                </div>
            </div>
			<div class="form-group row">
                <label for="desc" class="col-sm-3 col-form-label text-sm-right">{{ $t('storeFormDescriptionLabel') }}</label>
                <div class="col-sm-9">
                    <textarea v-model="form.description" id="desc" type="text" name="description" :placeholder="$t('storeFormDescriptionPlaceHolder')" autocomplete="business_description"
                        class="form-control" :class="{ 'is-valid': !form.errors.has('description') && isSubmitted,'is-invalid': form.errors.has('description') }"></textarea>
                    <has-error :form="form" field="description"></has-error>
                </div>
            </div>
        </template>
    </form-extend>
</template>

<script>
    import { mapGetters } from "vuex"
    import FormEx from "./extensions/Form.vue"

    export default {
        name:"StoreForm",
		props:{
            "form":{
                type:Object,
                required:true
            },
            "mode":{
                type:String,
                required:true
            },
            "isSubmitted":{
                type:Boolean,
                required:true
            }
        },
        mounted(){
            Fire.$on('CreateStore',() => {
                this.createStore();
            });
            Fire.$on('UpdateStore',() => {
                this.updateStore(this.form.id);
            });
        },
        computed:{
            ...mapGetters(["storesRoutes",'getAuth','getCountries','getStoreLogo']),
			decideImage(){return !_.isEmpty(this.form.picture) ? this.form.picture.filename : this.getStoreLogo },
        },
        methods:{
        	createStore(){
                this.submitted = true;
                this.$Progress.start();
                this.form.post(this.storesRoutes.store).then(()=>{
                    Fire.$emit('ReloadStores');
                    Fire.$emit('Enable');
                    $('#form').modal('hide');
                    new toast({
                        type: 'success',
                        title: this.$t('storeFormCreateSuccess')
                    });
                    this.$Progress.finish();
                }).catch(()=>{
                    Fire.$emit('Enable');
                    this.$Progress.fail();
                    new toast({
                        type: 'error',
                        title: this.$t('storeFormCreateError')
                    });
                })
            },
            updateStore(id){
                this.submitted = true;
                this.$Progress.start();
                this.form.patch(this.storesRoutes.update+id).then(()=>{
                    Fire.$emit('ReloadStores');
                    Fire.$emit('Enable');
                    $('#form').modal('hide');
                    new toast({
                        type: 'success',
                        title: this.$t('storeFormUpdateSuccess')
                    });
                    this.$Progress.finish();
                }).catch(()=>{
                    Fire.$emit('Enable');
                    this.$Progress.fail();
                    new toast({
                        type: 'error',
                        title: this.$t('storeFormUpdateError')
                    });
                })
            },
            setPicture(e){
                let file = e.target.files[0];
                if(file){
                    let reader = new FileReader();
                    let limit = 1024 * 1024 * 2;
                    if(file['size'] > limit){
                        swal({
                            type: 'error',
                            title: this.$t('fileUploadOops'),
                            text: this.$t('fileUploadLimit')
                        })
                        return false;
                    }
                    reader.onloadend = () => {
                        this.form.image = reader.result;
                        $('#profile').attr('src',reader.result);
                    };
                    reader.readAsDataURL(file);
                }
            }
        },
        components:{
            "form-extend": FormEx,
        }
    }
</script>
