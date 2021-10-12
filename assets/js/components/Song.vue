<template>
    <li @click="play" class="list-group-item search-suggestion song" :data-song-id="song.id">
        <div class="list-group-item-content">
            <div class="thumb" :title="song.title" :style="thumbnail"></div>
            <span class="title">{{ song.title }}</span>
            <router-link :to="{ name: 'ArtistDetail', params: { id: song.artistid } }" @click="$event.stopPropagation()">
                <span class="song-artist" :data-artist-id="song.artist.id">{{ song.artistname }}</span>
            </router-link>
        </div>
        <div class="row-actions">
            <div @click="toggle" v-if="selecting" class="ml-3">
                <CheckBoxOutlineIcon v-if="selecting && !selected" :height="45" :width="45" />
                <CheckboxMarkedIcon v-if="selecting && selected" :height="45" :width="45" />
            </div>
            <div v-if="!selecting" @click="playNext" class="play-next">
                <ChevronTripleRightIcon :height="45" :width="45" />
            </div>
            <div v-if="!selecting" @click="addSongToQueue" class="queue">
                <PlusIcon :height="45" :width="45" />
            </div>
            <div v-if="!selecting" @click="showSongEditModal">
                <DotsVerticalIcon :height="45" :width="45" />
            </div>
        </div>
    </li>
</template>

<script>
    import Util from "../util";
    import {mapActions, mapGetters, mapMutations} from 'vuex';
    import ChevronTripleRightIcon from "./icons/ChevronTripleRight";
    import PlusIcon from "./icons/Plus";
    import DotsVerticalIcon from "./icons/DotsVertical";
    import CheckBoxOutlineIcon from "./icons/CheckBoxOutline";
    import CheckboxMarkedIcon from "./icons/CheckboxMarked";

    export default {
        name: "Song",
        components: {CheckboxMarkedIcon, CheckBoxOutlineIcon, DotsVerticalIcon, PlusIcon, ChevronTripleRightIcon},
        props: {
            song: Object,
            selecting: {
                type: Boolean,
                default: false,
            },
            selected: {
                type: Boolean,
                default: false,
            },
            rowIndex: Number,
        },
        emits: [
            'toggleSelect',
        ],
        methods: {
            ...mapActions('queue', [
                'addToQueue',
                'playQueueItem'
            ]),
            ...mapMutations('queue', [
                'moveToNextIndex',
            ]),
            ...mapMutations([
                'setEditingSong',
            ]),
            showSongEditModal(e) {
                e.stopPropagation();
                this.setEditingSong(this.song);
            },
            addSongToQueue(e) {
                e.stopPropagation();
                this.addToQueue(this.song);

                // if (1 || !Util.isInView(this.$el)) {
                //     this.$el.scrollIntoView()
                // }
            },
            playNext(e) {
                e.stopPropagation();
                this.moveToNextIndex(this.song);
            },
            play(e) {
                e.preventDefault();
                this.addToQueue(this.song);
                this.playQueueItem(this.song);
            },
            toggle(e) {
                e.preventDefault();
                e.stopPropagation();
                this.$emit('toggleSelect', this.rowIndex)
            }
        },
        computed: {
            ...mapGetters([
                'songEditModal',
            ]),
            thumbnail() {
                return `background-image: url('${(this.song.thumbnail && this.song.thumbnail.length) ? this.song.thumbnail : Util.thumbnailDataUri}')`
            }
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .list-group-item {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        color: #999;
        background: #18191a;
        background: rgba(255, 255, 255, 0.05);
        position: relative;
        cursor: pointer;
        text-decoration: none;
        padding: 0;
        font-size: 0.7rem;

        label {
            cursor: pointer;
        }

        a {
            color: #999999;
        }

        .row-actions {
            position: absolute;
            left: 0;
            padding-left: 10px;
            width: 100px;

            @include smartphone {
                left: unset;
                right: 0;
                top: 3px;
            }

            svg {
                fill: rgba(255, 255, 255, 0.5);
                transition: fill .3s ease;
                height: 30px;
                margin: 0;

                &:hover {
                    fill: rgba(255, 255, 255, 1);
                }
            }

            .song-artist {
                margin-right: 0.5rem;
                font-weight: bold;
            }

            div {
                display: inline-block;
                margin: 0;
            }
        }

        .thumb {
            width: 45px;
            height: 45px;
            display: block;
            background-size: cover;
            float: left;
            margin-right: 10px;
            position: relative;
        }

        &:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        .list-group-item-content {
            position: relative;
            left: 95px;
            line-height: 40px;

            @include desktop {
                min-width: 600px;
            }

            @include smartphone {
                left: 0;
                width: 100%;
                line-height: 0.7rem;
            }
        }

        .title {
            position: absolute;
            left:50px;

            @include smartphone {
                margin-top: 10px;
            }
        }

        .song-artist {
            display: inline-block;
            position: absolute;
            text-align: center;
            left: 300px;

            @include smartphone {
                overflow: hidden;
                white-space: nowrap;
                position: absolute;
                left: 50px;
                top: 1.4rem;
                font-weight: bold;
            }
        }
    }
</style>