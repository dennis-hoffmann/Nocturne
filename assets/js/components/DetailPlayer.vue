<template>
    <div id="player-detail">
        <div id="player-bg" :style="bgStyle"></div>
        <div v-if="currentSong" id="track-info" :style="trackInfoStyle">
            <div class="title">{{ currentSong.title }}</div>
            <div class="artist">{{ currentSong.artistname }}</div>
            <div class="album">{{ currentSong.album }}</div>
            <div class="year">{{ currentSong.year }}</div>
        </div>
        <img @dblclick="toggleQueue" class="cover" :src="thumbnail"/>
        <WaveSurfer :color="accentColor" />
        <div class="actions">
            <div class="action action-heart" title="Like">
                <HeartOutlineIcon :size="45"/>
            </div>
            <div class="action action-add-to-playlist" title="Add To Playlist" data-toggle="modal"
                 data-target="#add-to-playlist-modal">
                <PlusBoxMultipleIcon :size="45"/>
            </div>
            <div v-if="lyrics" @click="lyricModalVisible = !lyricModalVisible" class="action action-show-lyrics" title="Lyrics" data-toggle="modal" data-target="#lyrics-modal">
                <MusicClefTrebleIcon :size="45"/>
            </div>
            <div class="action action-shuffle" title="Shuffle">
                <ShuffleIcon @click="toggleShuffle" class="control-shuffle" :fillColor="shuffle ? activeStyle : '#FFFFFF'" :size="45" />
            </div>
            <div @click="toggleRepeat" class="action action-repeat">
                <RepeatOnceIcon v-if="repeat === 'one'" :fillColor="repeat !== 'off' ? activeStyle : '#FFFFFF'" :size="45" />
                <RepeatIcon v-else :fillColor="repeat !== 'off' ? activeStyle : '#FFFFFF'" :size="45" />
            </div>
        </div>
        <div class="primary-controls">
            <PlayerControl class="control-prev">
                <FastForwardIcon @click="previous()" :size="120"/>
            </PlayerControl>
            <PlayerControl class="control-play">
                <PauseIcon @click="pause()" :size="120" v-if="playbackState === 'playing'"/>
                <PlayIcon @click="play()" :size="120" v-else class="control-play"/>
            </PlayerControl>
            <PlayerControl class="control-next">
                <FastForwardIcon @click="next()" :size="120" />
            </PlayerControl>
        </div>
        <LyricOverlay v-if="lyrics && lyricModalVisible" :lyrics="lyrics" v-on:hidden="lyricModalVisible = !lyricModalVisible"/>
    </div>
</template>

<script>
    import CloseBoxIcon from "./icons/CloseBox";
    import HeartIcon from './icons/Heart';
    import HeartOutlineIcon from './icons/HeartOutline';
    import PlusBoxMultipleIcon from './icons/PlusBoxMultiple';
    import MusicClefTrebleIcon from './icons/MusicClefTreble';
    import FastForwardIcon from './icons/FastForward';
    import PlayIcon from './icons/Play';
    import PauseIcon from './icons/Pause';
    import PlayerControl from './PlayerControl'
    import {mapGetters, mapMutations} from "vuex";
    import WaveSurfer from "./WaveSurfer";
    import Util from "../util";
    import ShuffleIcon from "./icons/Shuffle";
    import LyricOverlay from "./LyricOverlay";
    import RepeatOnceIcon from "./icons/RepeatOnce";
    import RepeatIcon from "./icons/Repeat";

    export default {
        name: 'DetailPlayer',
        components: {
            RepeatIcon,
            RepeatOnceIcon,
            LyricOverlay,
            ShuffleIcon,
            CloseBoxIcon,
            HeartIcon,
            HeartOutlineIcon,
            PlusBoxMultipleIcon,
            MusicClefTrebleIcon,
            FastForwardIcon,
            PlayIcon,
            PauseIcon,
            PlayerControl,
            WaveSurfer,
        },
        data() {
            return {
                timeout: null,
                lyricModalVisible: false,
            }
        },
        computed: {
            ...mapGetters([
                'playbackState',
                'currentSong',
                'vibrant',
                'shuffle',
                'repeat',
            ]),
            lyrics() {
                return (
                    this.currentSong
                    && this.currentSong.lyrics
                    && this.currentSong.lyrics.length
                ) ? this.currentSong.lyrics.replace(/(?:\r\n|\r|\n)/g, '<br>') : '';
            },
            accentColor() {
                return this.vibrant ? this.vibrant.Vibrant.getHex() : '#d44b5e';
            },
            trackInfoStyle() {
                return `background-color: rgba(${this.vibrant ? this.vibrant.Vibrant.getRgb().join(',') : '0,0,0'}, 0.3);`;
            },
            thumbnail() {
                return this.currentSong && this.currentSong.thumbnail ? this.currentSong.thumbnail : Util.thumbnailDataUri;
            },
            bgStyle() {
                return this.currentSong && this.currentSong.thumbnail
                    ? `background-image: url('${this.currentSong.thumbnail}')`
                    : 'background-image: none'
                ;
            },
            activeStyle() {
                return this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)';
            },
        },
        methods: {
            ...mapMutations('player', [
                'play',
                'pause',
                'next',
                'previous',
                'toggleShuffle',
                'toggleRepeat',
            ]),
            toggleQueue(e) {
                e.preventDefault();
                e.stopPropagation();

                clearTimeout(this.timeout);
                this.timeout = setTimeout(() => {
                    document.getElementById('queue').classList.toggle('active');
                    document.body.classList.toggle('sidebar')
                }, 100);
            }
        },
    }
</script>

<style lang="scss">
    @import "../../css/mediaqueries.scss";

    #player-detail {
        width: 100%;
        min-height: 100%;
        max-height: 100%;
        vertical-align: middle;
        background-size: 600% 100%;
        position: relative;

        @include smartphone {
            position: fixed;
        }

        #player-bg {
            height: 100%;
            width: 100%;
            display: flex;
            position: absolute;
            background-size: 300% 100%;
            filter: blur(36px);
        }

        #track-info {
            width: 75%;
            position: relative;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.3);
            margin: 0 auto;
            border-radius: 20px;
            top: 5px;
            line-height: 1.3;
            z-index: 55;

            @include smartphone {
                width: 95%;
            }

            div {
                display: block;
                color: #FFFFFF;
            }

            .artist {
                font-size: 0.9rem;
            }

            .title {
                font-size: 1.2rem;
            }

            .album {
                font-size: 0.9rem;
            }

            .year {
                font-size: 0.6rem;
            }
        }

        .cover {
            position: absolute;
            vertical-align: middle;
            left: 50%;
            top: 33%;
            transform: translate(-50%, -40%);
            height: 65%;

            @include smartphone {
                height: 50%;
            }
        }

        #waveform {
            bottom: 150px;
            position: absolute;
            width: 100%;

            wave {
                @include smartphone {
                    height: 60px !important;
                }
            }

            @include smartphone {
                bottom: 160px;
            }
        }

        .primary-controls {
            position: absolute;
            bottom: 50px;
            width: 100%;
            height: 120px;
            display: flex;
            top: calc(50% - 120px);

            @include smartphone {
                top: unset;
                position: absolute;
                bottom: 75px;
                width: 100%;
                height: 60px;
            }
        }

        .control {
            cursor: pointer;

            div svg {
                height: 120px;

                @include smartphone {
                    height: 60px;
                }
            }
        }

        .modal {
            .modal-content {
                background-color: rgba(0, 0, 0, 0.33);

                color: #FFFFFF
            }

            .list-group-item {
                background: rgba(255, 255, 255, 0.05);
                color: #FFFFFF;
            }
        }

        #new-playlist {
            width: 100%;

            .bmd-form-group {
                margin: 0;
                padding: 0;
                width: 80%;
            }

            input {
                width: 100%;
            }

            button {
                right: 20px;
                top: 15px;
                position: absolute;
            }

            svg {
                height: 2rem;
                fill: #FFFFFF;
                margin-right: 0;
            }
        }

        .actions {
            height: 2.5rem;
            width: 100%;
            position: absolute;
            bottom: 3.5rem;
            text-align: center;

            @include smartphone {
                bottom: 1rem;
            }

            .action {
                padding: 0 1rem;
                display: inline-block;
                cursor: pointer;

                @include smartphone {
                    &:first-child {
                        padding-left: 0;
                    }

                    &:last-child {
                        padding-right: 0;
                    }
                }

                svg {
                    height: 2.3rem;
                }
            }

            .action:not(.action-shuffle):not(.action-repeat) {
                svg {
                    fill: #FFFFFF;
                }
            }
        }

        #close-detail-player {
            right: 20px;
            top: 10px;
            height: 2rem;
            position: absolute;
            cursor: pointer;

            svg {
                height: 3rem;
            }
        }
    }

    .bg-pan-left {
        animation: 120s ease 0s infinite alternate-reverse both running bg-pan-left;
    }

    @-webkit-keyframes bg-pan-left {
        0% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0 50%;
        }
    }

    @keyframes bg-pan-left {
        0% {
            background-position: 100% 50%;
        }
        100% {
            background-position: 0 50%;
        }
    }
</style>