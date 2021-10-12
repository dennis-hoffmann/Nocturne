<template>
    <TopNavigation />
    <AlbumSection :albums="allAlbums" />
</template>

<script>
    import AlbumSection from "../components/AlbumSection";
    import {mapActions, mapGetters} from "vuex";
    import TopNavigation from "../components/TopNavigation";

    export default {
        name: 'Library',
        components: {
            TopNavigation,
            AlbumSection
        },
        methods: {
            ...mapActions('library', [
                'fetchLibrarySection',
            ])
        },
        computed: {
            ...mapGetters([
                'libraryScrollPosition'
            ]),
            allAlbums: function () {
                return this.$store.getters['library/getAlbumsBySection']('allAlbums')
            },
        },
        mounted() {
            document.getElementById('content').scrollTop = this.libraryScrollPosition;
        },
        created() {
            this.fetchLibrarySection('allAlbums')
        },
        beforeRouteLeave(to, from) {
            const scrollPosition = document.getElementById('content').scrollTop;
            this.$store.commit('setLibraryScrollPosition', scrollPosition);
        },
    }
</script>