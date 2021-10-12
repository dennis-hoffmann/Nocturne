<template>
    <TopNavigation />
    <div v-if="playlists" id="content-playlists" class="col-md-12 container-fluid">
        <Playlist :key="playlist.id" v-for="playlist in playlists" :playlist="playlist"/>
    </div>
</template>

<script>
    import {mapActions} from "vuex";
    import Playlist from "../components/Playlist";
    import TopNavigation from "../components/TopNavigation";

    export default {
        name: "Playlists",
        components: {TopNavigation, Playlist},
        computed: {
            playlists() {
                return this.$store.getters['library/getUserPlaylists']();
            }
        },
        methods: {
            ...mapActions('library', [
                'fetchUserPlaylists'
            ]),
        },
        created() {
            this.fetchUserPlaylists()
        }
    }
</script>

<style scoped lang="scss">

</style>