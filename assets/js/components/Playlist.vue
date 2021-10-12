<template>
    <div class="row playlist">
        <div class="thumbnail-wrapper col-5 col-md-2">
            <img :src="thumbnail" class="thumbnail rounded img-fluid">
        </div>
        <div class="playlist-meta col-7 col-md-10">
            <h4>{{ playlist.name }} <button class="btn btn-outline-secondary playlist-play" title="Play"><PlayIcon /> Play</button></h4>
        </div>
        <div class="col-12 mt-3">
            <SongSection :songs="playlist.entries" />
        </div>
    </div>
    <hr>
</template>

<script>
    import SongSection from "./SongSection";
    import PlayIcon from "./icons/Play";
    import Util from "../util";

    export default {
        name: "Playlist",
        components: {PlayIcon, SongSection},
        props: {
            playlist: Object
        },
        computed: {
            thumbnail() {
                return this.playlist.entries.length ? this.playlist.entries.last().thumbnail : Util.thumbnailDataUri;
            },
        },
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .playlist {
        margin-top: 2rem;
        color: #FFFFFF;

        .playlist-meta {
            @include smartphone {
                padding-left: 0;
            }
        }

        .playlist-play {
            fill: #dc3545;
            color: #dc3545;
            padding: 0 1rem 0 0;

            svg {
                height: 2rem;
                fill: #dc3545;
            }
        }

        .thumbnail-wrapper {
            text-align: center;

            .thumbnail {
                @include smartphone {
                    max-height: 150px;
                }
            }
        }

        hr {
            border-top: 5px solid #dc3545;
            margin-bottom: 4rem;
        }

        li {
            padding: 0;
        }
    }
</style>