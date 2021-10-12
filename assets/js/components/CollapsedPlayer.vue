<template>
    <div ref="playerWrapper" id="player-wrapper">
        <div class="player">
            <div class="controls-primary">
                <div class="controls-primary-buttons">
                    <PlayerControl>
                        <FastForwardIcon @click="previous()" class="control-prev" :size="60" />
                    </PlayerControl>
                    <PlayerControl>
                        <PauseIcon @click="pause()" :size="60" v-if="playbackState === 'playing'" />
                        <PlayIcon @click="play()" :size="60" v-else />
                    </PlayerControl>
                    <PlayerControl>
                        <FastForwardIcon @click="next()" :size="60" />
                    </PlayerControl>
                </div>
            </div>

            <div class="controls-secondary">
                <div @click="regulateVolume" ref="volumeBar" class="progress slider-bar">
                    <div ref="volumeBar" class="progress-bar" role="progressbar" aria-valuenow="0"
                         aria-valuemin="0" aria-valuemax="100" :style="volumeStyle"></div>
                </div>
                <div class="controls-secondary-buttons">
                    <PlayerControl @click="toggleMute">
                        <VolumeMuteIcon class="mute" :fillColor="mute ? activeStyle : 'currentColor'" :size="60" />
                    </PlayerControl>
                    <PlayerControl @click="toggleRepeat">
                        <RepeatOnceIcon v-if="repeat === 'one'" :fillColor="repeat !== 'off' ? activeStyle : 'currentColor'"  class="control-repeat" :size="60" />
                        <RepeatIcon v-else class="control-repeat" :fillColor="repeat !== 'off' ? activeStyle : 'currentColor'" :size="60" />
                    </PlayerControl>
                    <PlayerControl @click="toggleShuffle">
                        <ShuffleIcon class="control-shuffle" :fillColor="shuffle ? activeStyle : 'currentColor'" :size="60"  />
                    </PlayerControl>
                    <PlayerControl>
                        <MenuIcon class="control-menu" :size="60"  />
                    </PlayerControl>
                </div>
            </div>

            <CollapsedPlayerInfo />
        </div>
    </div>
</template>

<script>
    import PlayerControl from "./PlayerControl";
    import ChevronTripleRightIcon from "./icons/ChevronTripleRight";
    import FastForwardIcon from "./icons/FastForward";
    import PauseIcon from "./icons/Pause";
    import CollapsedPlayerInfo from "./CollapsedPlayerInfo";
    import VolumeMuteIcon from "./icons/VolumeMute";
    import RepeatIcon from "./icons/Repeat";
    import ShuffleIcon from "./icons/Shuffle";
    import MenuIcon from "./icons/Menu";
    import {mapGetters, mapActions, mapMutations} from 'vuex';
    import PlayIcon from "./icons/Play";
    import RepeatOnceIcon from "./icons/RepeatOnce";
    import ZingTouch from "zingtouch";

    export default {
        name: "CollapsedPlayer",
        components: {
            RepeatOnceIcon,
            PlayIcon,
            MenuIcon,
            ShuffleIcon,
            RepeatIcon,
            VolumeMuteIcon,
            CollapsedPlayerInfo, PauseIcon, FastForwardIcon, ChevronTripleRightIcon, PlayerControl
        },
        computed: {
            ...mapGetters([
                'playbackState',
                'volume',
                'mute',
                'repeat',
                'shuffle',
                'vibrant',
            ]),
            volumeStyle() {
                return `background-color: ${this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)'}; width: 100%;`;
            },
            activeStyle() {
                return this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)';
            }
        },
        methods: {
            ...mapMutations('player', [
                'play',
                'pause',
                'next',
                'previous',
                'setVolume',
                'toggleMute',
                'toggleRepeat',
                'toggleShuffle',
            ]),
            regulateVolume(event) {
                // See https://stackoverflow.com/a/33347664/8792213
                const bounds = event.target.getBoundingClientRect();
                const x = event.clientX - bounds.left;
                const barWidth = this.$refs.volumeBar.clientWidth;
                const step = barWidth / 100;
                const percentage = x / step;

                this.setVolume(Math.round(percentage));
            }
        },
        mounted() {
            const bottomTouchArea = this.$refs.playerWrapper;
            const queue = this.$parent.$refs.queue.$el;

            const bottomTouchRegion = new ZingTouch.Region(document.body, false, false);
            bottomTouchRegion.bind(bottomTouchArea, new ZingTouch.Swipe({
                maxRestTime: 300,
                escapeVelocity: 0,
            }), (e) => {
                console.log('Moin');
                e.preventDefault();
                queue.classList.toggle('active');
                document.body.classList.toggle('sidebar')
            }, false);
        }
    }
</script>

<style lang="scss">
    @import "../../css/mediaqueries.scss";

    .player {
        overflow: hidden;
    }

    #player-wrapper {
        @include smartphone {
            position: fixed;
            width: 100%;
            bottom: 0;
            height: 80px;
        }
    }

    .control-prev {
        svg {
            -moz-transform: scale(-1, 1);
            -webkit-transform: scale(-1, 1);
            -o-transform: scale(-1, 1);
            -ms-transform: scale(-1, 1);
            transform: scale(-1, 1);
        }
    }

    .controls-primary {
        float: left;
        display: block;
        width: 300px;
        height: 70px;
        background: rgba(255, 255, 255, 0.05);

        @include tablet {
            width: 185px;
        }

        @include smartphone {
            height: 35px;
            width: calc(100% - 80px);
            float: unset;
            margin-left: 80px;
            bottom: 0;
            position: absolute;
        }

        .control {
            width: 33.33%;
            svg {
                height: 60px !important;

                @include smartphone {
                    height: 30px !important;
                }
            }

        }
    }

    .controls-secondary {
        float: right;
        display: block;
        width: 300px;
        height: 70px;
        background: rgba(255, 255, 255, 0.05);

        @include desktop {
            visibility: visible;
        }

        @include tablet {
            width: 185px;
        }

        @include smartphone {
            display: none;
            height: 35px;
            width: 100%;
            float: unset;
            margin-left: 0;
            bottom: 0;
            position: absolute;

            .slider-bar {
                display: none;
            }
        }

        .control {
            margin-top: -9px;
            width: 25%;

            @include smartphone {
                margin-top: 0;
                width: 33%;

                .control-menu {
                    display: none;
                }
            }

            div svg {
                height: 30px;
            }
        }
    }
</style>