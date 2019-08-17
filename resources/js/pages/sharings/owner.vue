<template>
    <div>
        <div v-for="sharing in sharings" :key="sharing.id" class="card mb-3">
            <div class="row no-gutters">
                <div class="col-md-2" :style="{'background-image': `url(${sharing.image})`}"></div>
                <div class="col-md-10">
                    <div class="card-body">
                        <h5 class="card-title">{{sharing.name}} <small>{{sharing.availability}}/{{sharing.capacity}} disponibili</small></h5>
                        <p class="card-text">

                            <div v-if="sharing.sharing_status.Joined.users.length > 0">
                                <h6>{{sharing.sharing_status.Joined.description}}</h6>
                                <ul class="list-group">
                                    <li v-for="user in sharing.sharing_status.Joined.users" :key="user.id" class="list-group-item">
                                        <manage-sharing-user :user="user"/>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="sharing.sharing_status.Pending.users.length > 0">
                                <h6>{{sharing.sharing_status.Pending.description}}</h6>
                                <ul class="list-group">
                                    <li v-for="user in sharing.sharing_status.Pending.users" :key="user.id" class="list-group-item">
                                        <manage-sharing-user :user="user" :sharing="sharing" :buttons="true"/>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="sharing.sharing_status.Approved.users.length > 0">
                                <h6>{{sharing.sharing_status.Approved.description}}</h6>
                                <ul class="list-group">
                                    <li v-for="user in sharing.sharing_status.Approved.users" :key="user.id" class="list-group-item">
                                        <manage-sharing-user :user="user" :sharing="sharing" :buttons="true"/>
                                    </li>
                                </ul>
                            </div>

                            <div v-if="sharing.sharing_status.Refused.users.length > 0">
                                <h6>{{sharing.sharing_status.Refused.description}}</h6>
                                <ul class="list-group">
                                    <li v-for="user in sharing.sharing_status.Refused.users" :key="user.id" class="list-group-item">
                                        <manage-sharing-user :user="user" :sharing="sharing" :buttons="true"/>
                                    </li>
                                </ul>
                            </div>

                        </p>
                        <p class="card-text"><small class="text-muted">Last updated 3 mins ago</small></p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
    .category-item{
        position: relative;
    }
    .category-item span{
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        right: 0;
        text-align: center;
    }
</style>

<script>
    import { mapGetters } from 'vuex'
    import SharingCard from '~/components/SharingCard';
    import ManageSharingUser from "../../components/ManageSharingUser";

    export default {
        components: {
            ManageSharingUser,
            SharingCard
        },
        middleware: 'auth',

        props: {
            type: {
                type: String,
                default: ''
            }
        },

        watch: {
            type: function(){
                return this.getData();
            }
        },

        methods: {
            getData: function () {
                this.$store.dispatch('sharings/fetchSharings', this.type);
            }
        },

        created() {
            this.getData();
        },

        computed: mapGetters({
            sharings: 'sharings/sharings',
        }),

    }
</script>
