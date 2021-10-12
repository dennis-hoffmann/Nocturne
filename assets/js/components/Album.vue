<template>
    <div class="col-4 col-lg-2 col-md-3 album" :data-album-id="album.id" @click="viewAlbum">
        <img class="img-fluid" :src="thumbnail">
        <div class="meta">
            <div class="album-title">{{ album.title }}</div>
            <div class="album-artist-subtitle" :data-artist-id="album.artistid">{{ album.artist }}</div>
            <div class="album-year badge badge-info">{{ album.year }}</div>
        </div>
    </div>
</template>

<script>
    import Util from "../util";

    export default {
        name: "Album",
        props: {
            album: Object,
        },
        computed: {
            thumbnail() {
                return (this.album.thumbnail && this.album.thumbnail.length) ? this.album.thumbnail : Util.thumbnailDataUri;
            }
        },
        methods: {
            viewAlbum() {
                this.$router.push({name: 'AlbumDetail', params: { id: this.album.id}, props: { album: this.album }});
            }
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .album {
        cursor: pointer;
        padding: 5px;

        .meta {
            padding: 10px;
            background-color: rgba(255, 255, 255, 0.8);
            white-space: nowrap;
            overflow: hidden;
            position: relative;
            margin: 0;

            @include smartphone {
                padding: 5px;
            }

            .album-title {
                font-size: 0.7rem;
            }

            .album-artist-subtitle {
                font-size: 0.5rem;
            }

            .album-year {
                position: absolute;
                top: 2px;
                right: 2px;
                font-size: 0.5em;
            }
        }
    }
</style>