<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <h3 class="card-header">{{ $t('registerHeader') }}</h3>
                    <div class="card-body">
                        <form method="POST" @submit.prevent="regUser">
                            <div class="text-center">
                                <img :src="getProfile | appendURL" id="profile" alt='' width="80px" height="80px"/>
								<span class="lead">{{ $t('userFormPreview') }}</span>
                            </div>
                            <div class="form-group row">
                                <label for="image" class="col-md-4 col-form-label text-md-right">{{ $t('userFormPhotoLabel') }}</label>
                                <div class="col-md-6">
                                    <input type="file" @change="setPicture" name="image" :class="{ 'is-valid': !form.errors.has('image') && isSubmitted,'is-invalid': form.errors.has('image') }"
                                        class="form-control-file" id="image">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="name" class="col-md-4 col-form-label text-md-right">{{ $t('userFormNameLabel') }}</label>
                                <div class="col-md-6">
                                    <input id="name" type="text" class="form-control" name="name" :class="{ 'is-valid': !form.errors.has('name') && isSubmitted,'is-invalid': form.errors.has('name') }"
                                        v-model="form.name" autocomplete="name" autofocus :placeholder="$t('userFormNamePlaceHolder')">
                                    <has-error :form="form" field="name"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="email" class="col-md-4 col-form-label text-md-right">{{ $t('userFormEmailLabel') }}</label>
                                <div class="col-md-6">
                                    <input id="email" type="email" class="form-control" name="email" :class="{ 'is-valid': !form.errors.has('email') && isSubmitted,'is-invalid': form.errors.has('email') }"
                                        v-model="form.email" autocomplete="email" autofocus :placeholder="$t('userFormEmailPlaceHolder')">
                                    <has-error :form="form" field="email"></has-error>
                                </div>
                            </div>
							<div class="form-group row">
								<label for="phone" class="col-md-4 col-form-label text-md-right">{{ $t('userFormPhoneLabel') }}</label>
								<div class="col-md-8">
									<div class="row">
										<div class="col-sm-4">
											<select name="country_code" id="country" class="form-control" v-model="form.phone.phone_country"
													:class="{ 'is-invalid': form.errors.has('phone.phone_country') }" autocomplete="phone_country">
												<option v-for="(country,code) in getCountries" :value="code" :key="code">{{ country }}</option>
											</select>
											<has-error :form="form" field="phone.phone_country"></has-error>
										</div>
										<div class="col-sm-8">
											<input type="tel" v-model="form.phone.phone" class="form-control" id="phone" :placeholder="$t('userFormPhonePlaceHolder')"
												   :class="{ 'is-invalid': form.errors.has('phone.phone') }" autocomplete="phone">
											<has-error :form="form" field="phone.phone"></has-error>
										</div>
									</div>
								</div>
							</div>
                            <div class="form-group row">
                                <label for="password" class="col-md-4 col-form-label text-md-right">{{ $t('userFormPasswordLabel') }}</label>
                                <div class="col-md-6">
                                    <input id="password" type="password" class="form-control" name="password" :class="{ 'is-valid': !form.errors.has('password') && isSubmitted,'is-invalid': form.errors.has('password') }"
                                        v-model="form.password" autocomplete="new-password" autofocus :placeholder="$t('userFormPasswordPlaceHolder')">
                                    <has-error :form="form" field="password"></has-error>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ $t('userFormConfirmPasswordLabel') }}</label>
                                <div class="col-md-6">
                                    <input id="password-confirm" type="password" class="form-control" name="password_confirmation" :class="{ 'is-valid': !form.errors.has('password') && isSubmitted,'is-invalid': form.errors.has('password') }"
                                        v-model="form.password_confirmation" autocomplete="new-password" autofocus :placeholder="$t('userFormConfirmPasswordPlaceHolder')">
                                    <has-error :form="form" field="password_confirmation"></has-error>
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary" :disabled="isDisabled || isEmpty">
                                        <span v-if="!isDisabled">{{ $t('registerButton') }}</span>
                                        <i class="fas fa-spinner fa-spin" v-if="isDisabled"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<<script>
    import { mapActions,mapGetters } from "vuex"

    export default {
        name:"Register",
        data(){
            return {
                form: new Form({
                    name: '',
                    email: '',
					phone: {phone:'',phone_country:'NG'},
                    password: '',
                    password_confirmation:'',
                    image: ''
                }),
                submitted:false,
                disabled:false,
            }
        },
        computed:{
            ...mapGetters(["getIntended","authRoutes",'getCountries','getProfile']),
            isDisabled(){return this.disabled},
            isSubmitted(){return this.submitted},
            isEmpty(){ return !(this.form.name && this.form.email && this.form.password && this.form.password_confirmation) }
        },
        methods:{
            ...mapActions(["setAuth","setToken","clearIntended"]),
            regUser(){
                this.disabled = true;
                this.submitted = true;
                this.$Progress.start();
                this.form.post(this.authRoutes.register).then(response=>{
                    this.setToken({token:response.data.data,remember:true});
                    axios.get(this.authRoutes.profile).then(response=>{
                        this.setAuth({user:response.data.data,remember:true});
                        this.disabled = false;
						this.$router.push(this.getIntended);
						this.clearIntended();
						this.$Progress.finish();
						new toast({
							type: 'success',
							title: this.$t('userFormCreateSuccess'),
						});
					}).catch(()=>{
                        this.disabled = false;
						this.$Progress.fail();
						new toast({
							type: 'error',
							title: this.$t('unexpectedError'),
						});
					});
                }).catch(()=>{
                    this.disabled = false;
					this.$Progress.fail();
					new toast({
						type: 'error',
						title: this.$t('userFormCreateError'),
					});
                });
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
                            text: this.$t('fileUploadLimit'),
                        })
                        return false;
                    }
                    reader.onloadend = (file) => {
                        this.form.image = reader.result;
                        $('#profile').attr('src',reader.result);
                    }
                    reader.readAsDataURL(file);
                }
            }
        }
    }
</script>
