<template>
    <div id="queue" @dblclick="toggle">
        <div ref="queueHeader" id="queue-header">
            <h3>Playlist</h3>
            <DotsVerticalIcon />
        </div>

        <ul id="queue-context-menu" class="d-none">
            <li id="clear-playlist">Clear Playlist</li>
            <li id="save-playlist">Save Playlist</li>
        </ul>
        <ul ref="queueItems" id="queue-items" :class="playerType === 'detail' ? 'full' : ''">
            <QueueItem :key="song.id" v-for="song in songs" :song="song"/>
        </ul>
    </div>
</template>

<script>
    import draggable from 'vuedraggable'
    import {mapGetters, mapActions, mapMutations} from 'vuex';
    import DotsVerticalIcon from "./icons/DotsVertical";
    import QueueItem from "./QueueItem";
    import {Sortable} from "sortablejs";
    import Util from "../util";

    export default {
        name: 'Queue',
        components: {
            draggable,
            QueueItem,
            DotsVerticalIcon,
        },
        data() {
            return {
                timeout: null,
            };
        },
        methods: {
            ...mapActions('queue', [
                'fetchQueue',
                'moveItem',
            ]),
            ...mapMutations('queue', [
                'setQueue',
            ]),
            toggle(e) {
                if (!Util.isSmartphone()) {
                    return;
                }

                e.preventDefault();
                e.stopPropagation();

                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    console.log('queue');
                    this.$el.classList.toggle('active');
                    document.body.classList.toggle('sidebar')
                }, 100);
            }
        },
        computed: {
            ...mapGetters('queue', [
                'songs',
            ]),
            ...mapGetters([
                'playerType',
            ]),
            queueItems: {
                get() {
                    return this.songs
                },
                set(value) {
                    this.setQueue(value)
                }
            }
        },
        mounted() {
            const headerTouchArea = this.$refs.queueHeader;
            const queue = this.$el;

            const headerTouchRegion = new ZingTouch.Region(document.body, false, false);
            headerTouchRegion.bind(headerTouchArea, new ZingTouch.Swipe({
                maxRestTime: 300,
                escapeVelocity: 0,
            }), (e) => {
                e.preventDefault();
                queue.classList.toggle('active');
                document.body.classList.toggle('sidebar')
            }, false);

            const playlistSortable = Sortable.create(this.$refs.queueItems, {
                dataIdAttr: 'data-song-id',
                delay: 300,
                delayOnTouchOnly: true,
                animation: 150,
                easing: "cubic-bezier(1, 0, 0, 1)",
                onEnd: (e) => {
                    this.moveItem({
                        from: e.oldIndex,
                        to: e.newIndex
                    });
                    // player.setPlaylist(this.toArray());
                }
            });
        },
        async created() {
            this.fetchQueue();
        },
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    #queue {
        @include smartphone {
            left: 0;
            height: 100%;
            width: 100%;

            &.active {
                visibility: visible;
                transform: translateX(0);
                transition: transform .2s;

                .controls-secondary {
                    visibility: visible;
                }
            }
        }

        visibility: hidden;
        width: 300px;
        position: fixed;
        top: 0;
        height: calc(100vh - 70px);
        z-index: 401;
        background: rgba(255, 255, 255, 0.05);
        text-align: left;
        color: #fff;
        transform: translateX(-100%);
        transition: all .2s;
        background: #18191A;

        @include desktop {
            display: inline-block;
            position: fixed;
            left: 0;
            visibility: visible;
            transform: translate3d(100vw,0,0) translateX(-100%);
            transition: transform .2s;

            &.active {
                visibility: visible;
                transform: translate3d(100vw,0,0) translateX(-100%);
                transition: transform .2s;
            }
        }
    }

    #queue-items {
        list-style: none;
        background: rgba(255, 255, 255, 0.05);
        padding-left: 5px;
        overflow-y: auto;
        max-height: calc(100vh - 70px - 55px);

        &.full {
            max-height: unset;
            height: calc(100% - 50px);

            @include smartphone {
                max-height: calc(100% - 55px);
            }
        }
    }

    #queue-context-menu {
        border-bottom: 2px solid rgba(255, 255, 255, 0.4);
        margin: 0;
        padding: 0;

        li {
            vertical-align: middle;
            background-color: rgba(255, 255, 255, 0.2);
            font-size: 1.3em;
            text-align: center;
            height: 45px;
            line-height: 45px;
            list-style: none;
            transition: background-color .3s ease;
            cursor: pointer;

            &:hover {
                background-color: rgba(255, 255, 255, 0.5);
            }
        }
    }

    #queue-header {
        height: 55px;
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;

        #queue-options {
            height: 40px;
            fill: rgba(255, 255, 255, 0.5);
            transition: fill .3s ease;
            margin: 0;
            cursor: pointer;
            position: absolute;
            right: 0;

            &:hover {
                fill: rgba(255, 255, 255, 1);
            }

        }
    }
</style>