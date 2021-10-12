<template>
    <li @click="playQueueItem(song)" :data-song-id="song.id" :style="isActive && itemStyle" class="queue-item" :class="isActive ? 'playing':''">
        <div class="item-inner item-song" :data-song-id="song.id">
            <div class="thumb" :title="song.title" :style="thumbnail"></div>
            <div class="meta">
                <div class="title disable-select">
                    {{ song.title }}
                </div>
                <div class="subtitle disable-select">
                    {{ song.artistname }}
                </div>
            </div>
            <div class="row-actions">
                <ChevronTripleRightIcon @click="playNext" class="play-next"/>
                <TrashCanOutlineIcon @click="removeItem" class="remove-from-playlist"/>
            </div>
        </div>
    </li>
</template>

<script>
    import Util from '../util'
    import {mapActions, mapGetters, mapMutations} from 'vuex';
    import TrashCanOutlineIcon from "./icons/TrashCanOutline";
    import ChevronTripleRightIcon from "./icons/ChevronTripleRight";

    export default {
        name: 'QueueItem',
        props: {
            song: Object,
        },
        components: {
            ChevronTripleRightIcon,
            TrashCanOutlineIcon,
        },
        methods: {
            ...mapActions('queue', [
                'playQueueItem',
            ]),
            ...mapMutations('queue', [
                'moveToNextIndex',
                'deleteQueueItem',
            ]),
            playNext(e) {
                e.stopPropagation();
                this.moveToNextIndex(this.song)
            },
            removeItem(e) {
                e.stopPropagation();
                this.deleteQueueItem(this.song)
            },
        },
        computed: {
            ...mapGetters([
                'currentSong',
                'vibrant'
            ]),
            isActive() {
                return this.currentSong.id && this.currentSong.id === this.song.id;
            },
            thumbnail() {
                return `background-image: url('${(this.song.thumbnail && this.song.thumbnail.length) ? this.song.thumbnail : Util.thumbnailDataUri}')`
            },
            itemStyle() {
                return `background: rgba(${this.vibrant ? this.vibrant.Vibrant.getRgb().join(',') : '220,53,69'},.6)`
            }
        }
    }
</script>

<style lang="scss">
    @import "../../css/mediaqueries";

    .queue-item, .song {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);

        &.playing {
            background: rgba(220, 53, 69, 0.6);
        }

        height: 45px;
        position: relative;
        color: #999;
        cursor: pointer;

        .row-actions {
            position: absolute;
            right: 8px;
            top: 8px;
            height: 20px;

            svg {
                fill: rgba(255, 255, 255, 0.5);
                transition: fill .3s ease;
                margin: 0;
                height: 20px;

                @include smartphone {
                    height: 30px;
                    width: 30px;
                }

                &:hover {
                    fill: rgba(255, 255, 255, 1);
                }
            }

            div {
                display: inline-block;
                margin: 0;
            }
        }

        a {
            color: #999999;
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

    .meta {
        margin: 0 60px 0 0;
        padding: 9px 10px 5px 15px;
    }

    .title, .subtitle {
        display: block;
        overflow: hidden;
        white-space: nowrap;
    }

    .title, .title a {
        color: #bdc1c2;
    }

    .subtitle {
        display: none;
        font-size: 95%;
        margin-top: 2px;
    }
</style>