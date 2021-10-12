<template>
    <TopNavigation />
    <div v-if="album" class="row">
        <div class="col-12 col-md-3">
            <img :src="thumbnail" class="img-fluid artist-cover">
            <h2 class="d-lg-none">{{ album.title }}</h2>
            <router-link :to="{ name: 'ArtistDetail', params: { id: album.artistId } }">
                <h3 class="d-lg-none song-artist" :data-artist-id="album.artistId">{{ album.artist }}</h3>
            </router-link>
        </div>
        <div class="col-md-6">
            <h2 class="d-none d-lg-block">{{ album.title }}</h2>
            <router-link :to="{ name: 'ArtistDetail', params: { id: album.artistId } }">
                <h3 class="d-none d-lg-block song-artist" :data-artist-id="album.artistId">{{ album.artist }}</h3>
            </router-link>
            <ul v-if="album.description" class="list-group list-group-flush">
                <li class="list-group-item list-item-description" title="Click for more">
                    <strong>Description:</strong>
                    <div class="description-text">
                        {{ album.description }}
                    </div>
                </li>
            </ul>
            <ul class="list-group list-group-flush artist-specs m-0">
                <li v-if="album.year" class="list-group-item">
                    <strong>Released:</strong> {{ album.year}}
                </li>
                <li v-if="album.genre" class="list-group-item">
                    <strong>Genre:</strong> {{ album.genre.join(', ') }}
                </li>
                <li v-if="album.mood" class="list-group-item">
                    <strong>Mood:</strong> {{ album.mood.join(', ') }}
                </li>
                <li class="list-group-item">
                    <strong>Tracks:</strong> {{ album.songCount }}
                </li>
            </ul>
        </div>
    </div>
    <SongSection v-if="album" :songs="album.songs" />
</template>

<script>
    import Util from "../util";
    import {mapActions} from "vuex";
    import SongSection from "../components/SongSection";
    import TopNavigation from "../components/TopNavigation";

    export default {
        name: "AlbumDetail",
        components: {TopNavigation, SongSection},
        props: {
            id: String
        },
        methods: {
            ...mapActions('library', [
                'fetchAlbumById',
            ])
        },
        computed: {
            thumbnail() {
                return (this.album.thumbnail && this.album.thumbnail.length) ? this.album.thumbnail : Util.thumbnailDataUri;
            },
            album() {
                return this.$store.getters['library/getAlbumById'](this.id);
            },
            songs() {
                return this.album ? this.album.songs : [];
            }

        },
        created() {
            this.fetchAlbumById(this.id)
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    @include smartphone {
        background-position: top !important;
        background-size: cover !important;
        background-repeat: no-repeat !important;
    }

    h2 {
        color: #FFFFFF;
        margin-top: 30px;
        text-shadow: 1px 1px 1px #18191A;

        @include smartphone {
            display: inline-block;
            font-size: 1.5em;
        }
    }

    h3 {
        color: #FFFFFF;
        text-shadow: 1px 1px 1px #18191A;
        cursor: pointer;

        @include smartphone {
            display: inline-block;
            font-size: 1.2em;
        }
    }

    .artist-specs {
        @include desktop {
            max-width: 300px;
        }
    }

    .artist-cover {
        margin: 20px;

        @include smartphone {
            max-height: 110px;
        }
    }

    .list-group-item {
        background-color: rgba(10, 10, 10, 0.5);
        color: #FFF;
        max-width: 600px;

        @include smartphone {
            padding: 0.5em;
        }
    }

    .list-item-description {
        cursor: pointer;

        .description-text {
            max-height: calc(2 * 1em);
            line-height: 1em;
            font-size: 1em;
            overflow: hidden;

            &.expanded {
                max-height: unset;
            }
        }
    }
</style>