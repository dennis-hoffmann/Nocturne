<template>
    <div id="now-playing">
        <div @click="detailPlayer" class="playing-thumb thumb" :style="thumbnailStyle"></div>
        <div class="playing-info">
            <div ref="progressBar" @click="moveProgress" class="progress slider-bar">
                <canvas ref="elapsedProgressCanvas" id="player-progressbar"></canvas>
            </div>
            <div class="playing-time">
                <div class="playing-time-current">{{ progressReadable }}</div>
                <div class="playing-time-duration">{{ durationReadable }}</div>
            </div>
            <div class="playing-meta">
                <div @click="$router.push({name: 'AlbumDetail', params: { id: currentSong.albumId}})" class="playing-title">{{ currentSong.title }}</div>
                <div class="playing-subtitle">{{ currentSong.artist.name }}</div>
            </div>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapActions, mapMutations} from 'vuex';
    import Util from "../util";

    export default {
        name: "CollapsedPlayerInfo",
        computed: {
            ...mapGetters([
                'progressPercentage',
                'progressReadable',
                'durationReadable',
                'currentSong',
                'vibrant',
            ]),
            thumbnailStyle() {
                return `background: url("${this.currentSong.thumbnail ?? Util.thumbnailDataUri}") no-repeat fixed 50% 50%; `;
            }
        },
        watch: {
            progressPercentage(val) {
                const canvas = this.$refs.elapsedProgressCanvas;
                const context = canvas.getContext('2d');

                const percentage = val / 100;
                const width = Math.round(percentage * canvas.width);
                const height = canvas.height;

                context.fillStyle = this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)';
                context.fillRect(0, 0, width, height);
            },
            currentSong(val) {
                const canvas = this.$refs.elapsedProgressCanvas;
                const context = canvas.getContext('2d');
                context.clearRect(0, 0, canvas.width, canvas.height);
            }
        },
        methods: {
            ...mapMutations('player', [
                'seek'
            ]),
            ...mapMutations([
                'toggleDetailPlayer'
            ]),
            detailPlayer(e) {
                this.$router.push({name: 'Detail'});
            },
            moveProgress(event) {
                // See https://stackoverflow.com/a/33347664/8792213
                const bounds = event.target.getBoundingClientRect();
                const x = event.clientX - bounds.left;
                const barWidth = this.$refs.progressBar.clientWidth;
                const step = barWidth / 100;
                const percentage = x / step;

                this.seek(percentage);
            }
        },
        mounted() {
            const canvas = this.$refs.elapsedProgressCanvas;
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .playing-thumb {
        width: 70px;
        height: 70px;
        margin: 0;
        z-index: 10;
        overflow: hidden;
        background-size: cover;
        background: rgba(255, 255, 255, 0.2) 50% 50%;
        background-size: cover !important;
        background-attachment: inherit !important;
        cursor: pointer;

        @include smartphone {
            width: 80px;
            height: 80px;
        }
    }

    .playing-info {
        margin-left: 70px;
        color: #FFFFFF;

        @include smartphone {
            margin-left: 80px;
        }

        a {
            text-decoration: none;
            color: #FFFFFF;
        }

        #player-progressbar {
            width: 100%;
        }

        .playing-time {
            display: inline-block;
            float: right;
            text-align: right;
            margin: 2px 1em 0 0;

            @include smartphone {
                display: none;
            }
        }

        .playing-meta {
            margin: 2px 0 0 1em;
            display: inline-block;

            @include smartphone {
                font-size: 0.75rem;

                div {
                    display: inline-block;
                }

                .playing-title {
                    a {
                        margin-right: 0.5em;
                    }
                    &::after {
                        content: '·';
                        font-weight: bold;
                    }
                }

                .playing-subtitle {
                    margin-left: 0.5em;
                }
            }

            .playing-subtitle, .playing-title {
                cursor: pointer;
            }
        }
    }
</style>