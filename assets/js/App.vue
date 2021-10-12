<template>
    <Menu :class="playerType === 'detail' ? 'full-height' : ''" />
    <div id="content" :class="playerType === 'detail' ? 'full-height' : ''">
        <router-view v-if="playerType !== 'detail'"></router-view>
        <DetailPlayer v-show="playerType === 'detail'" />
    </div>
    <CollapsedPlayer v-if="currentSong  && playerType === 'collapsed'"/>
    <Queue ref="queue" v-if="currentSong" :class="playerType === 'detail' ? 'full-height' : ''"/>
    <NotificationSection />
    <SongEditModal v-if="editingSong" :song="editingSong"/>
</template>

<script>
    import Queue from './components/Queue'
    import Menu from "./components/Menu";
    import CollapsedPlayer from "./components/CollapsedPlayer";
    import DetailPlayer from './components/DetailPlayer';
    import {mapGetters, mapActions} from 'vuex';
    import NotificationSection from "./components/NotificationSection";
    import SongEditModal from "./components/SongEditModal";

    export default {
        name: 'App',
        components: {
            SongEditModal,
            NotificationSection,
            CollapsedPlayer,
            DetailPlayer,
            Menu,
            Queue,
        },
        computed: {
            ...mapGetters([
                'currentSong',
                'editingSong',
                'lastPlayedSongId',
                'playerType',
            ])
        },
        methods: {
            ...mapActions([
                'fetchLastPlayedSong'
            ]),
        },
        created() {
            if (this.lastPlayedSongId) {
                this.fetchLastPlayedSong(this.lastPlayedSongId)
            }
        },
    }
</script>

<style lang="scss">
    @import "~bootstrap/scss/bootstrap";
    @import "../css/mediaqueries.scss";
    @import url('https://fonts.googleapis.com/css?family=Roboto&display=swap');
    @import "../css/form.scss";

    html, body, html * {
        font-family: 'Roboto', sans-serif;
    }

    html {
        height: 100%;
        min-height: 100%;
    }

    body {
        height: 100%;
    }

    .disable-select {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .progress {
        border-radius: 0;
    }

    .full-height {
        height: 100% !important;
        max-height: unset !important;
    }

    #content {
        @include desktop {
            width: calc(100% - 600px);
            display: inline-block;
            position: absolute;
            padding: 0 25px 25px;
        }

        transition: width 1500s;
        max-height: calc(100% - 80px);
        height: calc(100% - 80px);
        overflow-y: auto;
        overflow-x: hidden;
        position: fixed;
        width: 100%;
    }

    body {
        background-color: #3a3b3c;
        margin: 0;

        // Fuck off chrome
        overscroll-behavior: none;
    }

    @font-face {
        font-family: 'Nocturne';
        src: url('../css/fonts/Nocturne.woff') format('woff'), /* Pretty Modern Browsers */
        url('../css/fonts/Nocturne.ttf') format('truetype'); /* Safari, Android, iOS */
    }
</style>