<template>
    <table-extend>
        <template slot="headers">
            <tr>
                <th>{{ $t('userTableId') }}</th>
                <th>{{ $t('userTableName') }}</th>
                <th>{{ $t('userTableEmail') }}</th>
                <th>{{ $t('userTablePhone') }}</th>
                <th>{{ $t('userTableRole') }}</th>
                <th>{{ $t('userTablePicture') }}</th>
                <th>{{ $t('userTableCreated') }}</th>
                <th>{{ $t('userTableUpdated') }}</th>
                <th>{{ $t('userTableModifiers') }}</th>
            </tr>
        </template>
        <template slot="rows">
            <tbody>
                <v-layout row wrap v-if="fetching">
                    <v-flex xs6 offset-xs3>
                        <vue-simple-spinner :message="$t('loadingSpinner')" size="large" />
                    </v-flex>
                </v-layout>
                <tr v-for="user in users.data" :key="user.id" @click="emitCurr(user)" v-if="!fetching">
                    <td>{{user.id}}</td>
                    <td>{{user.name}}</td>
                    <td>{{user.email}}</td>
                    <td>{{user.phone.phone}}<small>{{user.phone.phone_country}}</small></td>
                    <td>{{user.role}}</td>
                    <td>{{ user.picture ? user.picture.filename : $t('userTableNone') }}</td>
                    <td>{{user.created_at | myDate}}</td>
                    <td>{{user.updated_at | myDate}}</td>
                    <td>
                        <a @click.prevent="viewModal(user.id)"><i class="fas fa-eye text-blue"></i></a>
                        &nbsp;|&nbsp;
                        <a @click.prevent="emitUpdate(user)"><i class="fas fa-pen text-orange"></i></a>
                        &nbsp;|&nbsp;
                        <a @click.prevent="emitDelete(user.id)"><i class="fas fa-trash text-red"></i></a>
                    </td>
                </tr>
            </tbody>
        </template>
        <template slot="pagination">
            <pagination :data="users" align="center" :limit="limit" @pagination-change-page="getUsers">
                <span slot="prev-nav"><i class="fas fa-angle-left"></i></span>
                <span slot="next-nav"><i class="fas fa-angle-right"></i></span>
            </pagination>
        </template>
    </table-extend>
</template>

<script>
    import { mapGetters } from "vuex"
    import TableEx from "./extensions/Table.vue"

    export default {
        name:"UserTable",
        data(){
            return {
                limit:2,
                users:{},
                fetch: false,
            }
        },
        computed:{
            ...mapGetters(["usersRoutes"]),
            fetching(){return this.fetch}
        },
        components:{
            "table-extend":TableEx,
        },
        mounted(){
            this.getUsers();
            Fire.$on('ReloadUsers',() => {
               this.getUsers();
            });
        },
        methods:{
            emitCurr(user){
                Fire.$emit("SetCurrentUser",user);
            },
            loadUsers(url){
                this.fetch = true;
                $("body").get(0).scrollIntoView();
                axios.get(url)
                .then((response)=>{
                    this.fetch = false;
                    this.users = response.data;
                }).catch(()=>{
                    new toast({
                        type: 'error',
                        title: this.$t('userTableError'),
                    });
                    this.fetch = false;
                })
            },
            getUsers(page = 1) {
                this.loadUsers(this.usersRoutes.paginate + page);
            },
            viewModal(id){
                axios.get(this.usersRoutes.show+id).then((response)=>{
                    this.user = response.data.data;
                    $('#view').modal('show');
                }).catch({});
            },
            emitUpdate(user){
                Fire.$emit("BeforeUpdateUser",user);
            },
            emitDelete(id){
                new swal({
                    title: this.$t('deleteConfirmTitle'),
                    text: this.$t('deleteConfirmText'),
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: this.$t('deleteConfirmButton')
                }).then((result) => {
                    if (result.value) {
                        Fire.$emit("DeleteUser",id)
                    }
                })
            },
        }
    }
</script>
