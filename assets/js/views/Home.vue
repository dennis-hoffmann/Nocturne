<template>
    <TopNavigation />
    <RandomForm v-if="filters" :filters="filters" />
    <SongSection v-if="recentlyListened.length" :songs="recentlyListened"/>
    <br>
    <br>
    <br>
    <SongSection v-if="newSongs.length" :songs="newSongs"/>
</template>

<script>
    import SongSection from "../components/SongSection";
    import TopNavigation from "../components/TopNavigation";
    import {mapActions, mapGetters} from "vuex";
    import RandomForm from "../components/form/RandomForm";

    export default {
        name: 'Home',
        components: {
            RandomForm,
            TopNavigation,
            SongSection
        },
        data() {
            return {
                tasks: [],
            }
        },
        methods: {
            ...mapActions('library', [
                'fetchLibrarySection',
            ]),
            ...mapActions('filters', [
                'fetchFilterValues',
            ])
        },
        computed: {
            newSongs: function () {
                return this.$store.getters['library/getSongsBySection']('newSongs')
            },
            recentlyListened: function () {
                return this.$store.getters['library/getSongsBySection']('recentlyListened')
            },
            ...mapGetters('filters', [
                'filters',
            ]),
        },
        created() {
            this.fetchLibrarySection('newSongs');
            this.fetchLibrarySection('recentlyListened');
            this.fetchFilterValues();
        }
    }
</script>