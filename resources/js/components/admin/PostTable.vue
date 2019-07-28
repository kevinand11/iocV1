<template>
    <table-extend>
        <template slot="headers">
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Store</th>
                <th>Category</th>
                <th>Pictures</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Modifiers</th>
            </tr>
        </template>
        <template slot="rows">
            <tbody>
                <v-layout row wrap v-if="fetching">
                    <v-flex xs6 offset-xs3>
                        <vue-simple-spinner message="Loading" size="large" />
                    </v-flex>
                </v-layout>
                <tr v-for="post in posts.data" :key="post.id" @click="emitCurr(post)" v-if="!fetching">
                    <td>{{post.id}}</td>
                    <td>{{post.name}}</td>
                    <td>{{post.description | first100}}</td>
                    <td>{{post.price | addNairaSign}}</td>
                    <td>{{post.store.name}}</td>
                    <td>{{post.category.name}}</td>
                    <td>
                        <span v-for="picture in post.pictures">{{picture.filename}},</span>
                    </td>
                    <td>{{post.created_at | myDate}}</td>
                    <td>{{post.updated_at | myDate}}</td>
                    <td>
                        <a @click.prevent="viewModal(post.id)"><i class="fas fa-eye text-blue"></i></a>
                        &nbsp;|&nbsp;
                        <a @click.prevent="emitUpdate(post)"><i class="fas fa-pen text-orange"></i></a>
                        &nbsp;|&nbsp;
                        <a @click.prevent="emitDelete(post.id)"><i class="fas fa-trash text-red"></i></a>
                    </td>
                </tr>
            </tbody>
        </template>
        <template slot="pagination">
            <pagination :data="posts" align="center" :limit="limit" @pagination-change-page="getPosts">
                <span slot="prev-nav"><i class="fas fa-angle-left"></i></span>
                <span slot="next-nav"><i class="fas fa-angle-right"></i></span>
            </pagination>
        </template>
    </table-extend>
</template>

<script>
    import { mapGetters } from "vuex"
    import TableEx from "../Table.vue"

    export default {
        name:"PostTable",
        data(){
            return {
                limit:2,
                posts:{},
                fetch:false,
            }
        },
        computed:{
            ...mapGetters(["postsRoutes"]),
            fetching(){return this.fetch}
        },
        components:{
            "table-extend":TableEx,
        },
        mounted(){
            this.getPosts();
            Fire.$on('Reload',() => {
               this.getPosts();
            });
        },
        methods:{
            emitCurr(post){
                Fire.$emit("SetCurrentPost",post);
            },
            loadPosts(url){
                this.fetch = true;
                $("body").get(0).scrollIntoView();
                axios.get(url)
                .then((response)=>{
                    this.fetch = false;
                    this.posts = response.data;
                }).catch(error=>{
                    this.fetch = false;
                })
            },
            getPosts(page = 1) {
                this.loadPosts(this.postsRoutes.paginate + page);
            },
            viewModal(id){
                axios.get(this.postsRoutes.show+id).then((response)=>{
                    this.post = response.data.data;
                    $('#view').modal('show');
                }).catch({});
            },
            emitUpdate(post){
                Fire.$emit("BeforeUpdatePost",post);
            },
            emitDelete(id){
                new swal({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.value) {
                        Fire.$emit("DeletePost",id)
                    }
                })
            },
        }
    }
</script>