<template>
    <ul class="list-group list-group-flush show collapse title-suggestions-container">
        <li class="list-group-item song-section-actions">
            <div class="row">
                <div class="col-12">
                    <CheckboxMultipleMarkedIcon v-if="selecting" @click="toggleAll" class="ml-4 mt-2" />
                    <CheckBoxMultipleOutlineIcon v-if="!selecting" @click="selecting = !selecting" class="mr-4 mt-2" :class="!selecting ? 'ml-4' : ''" />
                    <ChevronTripleRightIcon v-if="selecting" @click="queueSelected" />
                    <CloseBoxIcon v-if="selecting" @click="selecting = false" class="mr-4 mt-2" />
                    <div @click="playSectionSongs" class="btn btn-outline-light" :style="btnStyle">
                        Play
                    </div>
                    <div @click="queueSectionSongs" class="btn btn-outline-light" :style="btnStyle">
                        Queue
                    </div>
                </div>
            </div>
        </li>
        <Song :key="song.id" v-for="(song, rowIndex) in songs" :song="song" :selecting="selecting" :selected="toggledSongs[rowIndex]" @toggleSelect="toggleSong" :rowIndex="rowIndex" />
    </ul>
</template>

<script>
    import Song from "./Song";
    import {mapActions, mapGetters, mapMutations} from "vuex";
    import CheckboxMultipleMarkedIcon from "./icons/CheckboxMultipleMarked";
    import CheckBoxMultipleOutlineIcon from "./icons/CheckBoxMultipleOutline";
    import CloseBoxIcon from "./icons/CloseBox";
    import ChevronTripleRightIcon from "./icons/ChevronTripleRight";

    export default {
        name: "SongSection",
        components: {ChevronTripleRightIcon, CloseBoxIcon, CheckBoxMultipleOutlineIcon, CheckboxMultipleMarkedIcon, Song},
        data() {
            return {
                selecting: false,
                toggledSongs: [],
            }
        },
        props: {
            songs: Array
        },
        methods: {
            ...mapMutations('queue', [
                'setQueue',
            ]),
            ...mapActions('queue', [
                'playQueueItem',
                'addSongsToQueue',
            ]),
            playSectionSongs() {
                this.setQueue(this.songs);
                this.playQueueItem(this.songs[0])
            },
            queueSectionSongs() {

            },
            queueSelected() {
                console.log(this.toggledSongs);
                this.addSongsToQueue(this.songs.filter((song, index) => {return this.toggledSongs[index]}))
            },
            toggleAll(e) {
                e.preventDefault();
                this.toggledSongs.forEach((selected, rowIndex) => {
                    this.toggledSongs[rowIndex] = !this.toggledSongs[rowIndex];
                })
            },
            toggleSong(rowIndex) {
                this.toggledSongs[rowIndex] = !this.toggledSongs[rowIndex]
            },
        },
        computed: {
            ...mapGetters([
                'vibrant'
            ]),
            btnStyle() {
                return `background-color: ${this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)'};`
            }
        },
        mounted() {
            this.toggledSongs = [];

            this.songs.forEach((song, index) => {
                this.toggledSongs[index] = false;
            })
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .title-suggestions-container {
        background: #18191a;
        max-width: 1280px;
        margin: 0 auto;
        padding: 1em;

        @include smartphone {
            padding: 0;
        }

        .song-section-actions {
            margin: 0;
            padding: 0;
            background-color: unset;

            .btn {
                margin: 0 1em 0 0;
            }
        }
    }
</style>