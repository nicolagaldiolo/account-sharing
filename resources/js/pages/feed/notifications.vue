<template>
    <div class="row">
        <div class="col-md-3 mb-4" v-for="sharing in sharings" :key="sharing.id">
            <sharing-card :sharing="sharing" />
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

    export default {
        components: {
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
