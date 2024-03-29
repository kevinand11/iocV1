<template>
    <v-navigation-drawer v-model="drawer" app temporary dark class="pa-2" :class="appInfo.color" :width="appInfo.sidebar_width">
        <v-list class="pa-1">
            <v-list-tile avatar>
                <v-list-tile-avatar>
                    <img :src="getLogo | appendURL" alt="">
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ appInfo.name }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-divider></v-divider>
            <v-list-tile avatar>
                <v-list-tile-avatar>
                    <img :src="userProfile | appendURL" alt="">
                </v-list-tile-avatar>
                <v-list-tile-content>
                    <v-list-tile-title>{{ isLoggedIn ? getAuth.name : $t('sidebarAnonymous') }}</v-list-tile-title>
                    <small>{{ isLoggedIn ? getAuth.role : $t('sidebarUserRole') }}</small>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
        <v-divider></v-divider>
        <v-list class="pt-0" dense>
            <v-list-tile v-for="link in getLinks()" :key="link.id" v-if="link.showIf" :to="link.route">
                <v-list-tile-action>
                    <v-icon :color="link.color">{{link.icon}}</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t(`sidebar${link.name}`) }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
            <v-list-group v-model="role.model" v-for="role in getManagements(this.isLoggedIn,this.isAdmin)" value="true" no-action :key="role.id" v-if="role.showIf">
                <v-icon :color="role.color" slot="prependIcon">{{role.icon}}</v-icon>
                <template v-slot:activator>
                    <v-list-tile>
                        <v-list-tile-title>{{ $t(`sidebar${role.name}`) }}</v-list-tile-title>
                    </v-list-tile>
                </template>
                <v-list-tile v-for="sub in role.subs" :key="sub.id" :to="sub.route">
                    <v-list-tile-action>
                        <v-icon :color="sub.color">{{sub.icon}}</v-icon>
                    </v-list-tile-action>
                    <v-list-tile-content>
                        <v-list-tile-title>{{ $t(`sidebar${sub.name}`) }}</v-list-tile-title>
                    </v-list-tile-content>
                </v-list-tile>
            </v-list-group>
            <v-list-tile v-for="auth in getAuths(isLoggedIn)" :key="auth.id" v-if="auth.showIf" @click="testIfLogout(auth.name)" :to="auth.route">
                <v-list-tile-action>
                    <v-icon :color="auth.color">{{auth.icon}}</v-icon>
                </v-list-tile-action>
                <v-list-tile-content>
                    <v-list-tile-title>{{ $t(`sidebar${auth.name}`) }}</v-list-tile-title>
                </v-list-tile-content>
            </v-list-tile>
        </v-list>
    </v-navigation-drawer>
</template>

<script>
    import { mapGetters,mapActions } from "vuex"

    export default {
        name: "Sidebar",
        data(){
            return {
                drawer: null,
                admin: null,
            }
        },
        computed:{
            ...mapGetters(['appInfo',"getAuth",'isLoggedIn','isAdmin',"authRoutes",'getLogo','getProfile']),
			userProfile(){ return (this.getAuth.picture ? this.getAuth.picture.filename : this.getProfile ) },
        },
        methods:{
            ...mapActions(["logout"]),
            logoutUser(){
                new swal({
                    title: this.$t('sidebarAskLogout'),
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: this.$t('sidebarConfirmLogout'),
                }).then((result) => {
                    if (result.value) {
                    	this.$Progress.start();
                        axios.post(this.authRoutes.logout,{}).then(()=>{
                            this.logout();
                            this.drawer = !(this.drawer);
                            this.$router.push("/login");
							this.$Progress.finish();
							new toast({
								type: 'success',
                                title: this.$t('logoutSuccess'),
                            });
                        }).catch(()=>{
                        	this.$Progress.fail();
                            new toast({
                                type: 'error',
                                title: this.$t('logoutError'),
                            });
                        });
                    }
                })
            },
            getLinks(){
                return [
                    {id:1, name:"Dashboard", icon:'apps', color:"white", route:'/', showIf:true},
                    {id:2, name:'Store', icon:'shop', color:"white", route:'/store', showIf:true},
                    {id:3, name:'Profile', icon:'account_circle', color:"white", route:'/profile', showIf:true},
                ];
            },
            getAuths(logged){
                return [
                    {id:11, name:'Login', icon:'logout', color:"green", route:'/login', showIf:!logged},
                    {id:12, name:'Register', icon:'fas fa-user-plus', color:"green", route:'/register', showIf:!logged},
                    {id:13, name:'Logout', icon:'fas fa-power-off', color:"red", showIf:logged},
                ];
            },
            getManagements(logged,admin){
                return [
                    {id:21, name:"Admin", icon:"supervised_user_circle", color:"white", model:this.admin,showIf:(logged && admin),subs:[
                            {id:31, name:'Users', icon:'group', route:'/admin/users', color:"white"},
                            {id:32, name:'Stores', icon:'stores', route:'/admin/stores', color:"white"},
                            {id:33, name:'Posts', icon:'shopping_basket', route:'/admin/posts', color:"white"},
                            {id:34, name:'Categories', icon:'category', route:'/admin/categories', color:"white"},
                            {id:35, name:'Developer', icon:'laptop', route:'/admin/developer', color:'white'},
                        ]
                    },
                    
                ];
            },
            testIfLogout(ref){
                if(ref === 'Logout'){
                    this.logoutUser()
                }
            }
        },
        mounted(){
            Fire.$on("ToggleSidebar",()=>{
                this.drawer = !(this.drawer)
            });
        },
    }
</script>
