<template>
    <TopNavigation/>
    <div v-if="artist" class="row" id="content-artist" :style="fanartStyle">
        <div class="col-12 col-md-3">
            <img :src="thumbnail" class="img-fluid artist-cover">
            <h2 class="d-lg-none">{{ artist.name }}</h2>
        </div>
        <div class="col-md-6">
            <h2 class="d-none d-lg-block">{{ artist.name }}</h2>
            <ul @click="toggleTextCollapse" v-if="artist.description" class="list-group list-group-flush">
                <li class="list-group-item list-item-description" title="Click for more">
                    <strong>Description:</strong>
                    <div class="description-text">
                        {{ artist.description }}
                    </div>
                </li>
            </ul>
            <ul class="list-group list-group-flush artist-specs m-0">
                <li v-if="artist.yearsactive.length" class="list-group-item list-item-yearsactive">
                    <strong>Years Active:</strong> {{ artist.yearsactive.join(', ') }}
                </li>
                <li v-if="artist.instrument.length" class="list-group-item list-item-instrument">
                    <strong>Instrument:</strong>{{ artist.instrument.join(', ') }}
                </li>
                <li v-if="artist.genre.length" class="list-group-item list-item-genre">
                    <strong>Genre:</strong> {{ artist.genre.join(', ') }}
                </li>
                <li v-if="artist.formed" class="list-group-item list-item-formed">
                    <strong>Formed:</strong> {{ artist.formed }}
                </li>
                <li v-if="artist.died" class="list-group-item list-item-died">
                    <strong>Died:</strong> {{ artist.died }}
                </li>
                <li v-if="artist.born" class="list-group-item list-item-born">
                    <strong>Born:</strong> {{ artist.formed }}
                </li>
            </ul>
        </div>
        <div class="col-md-6">
            <div @click="playArtist" class="btn btn-primary">
                Play All
            </div>
        </div>
    </div>
    <div class="container">
        <SongSection v-if="artist && topSongs.length" :songs="topSongs"/>
    </div>
    <AlbumSection v-if="artist && albums" :albums="albums"/>
</template>

<script>
    import TopNavigation from "../components/TopNavigation";
    import {mapActions, mapMutations} from "vuex";
    import Util from "../util";
    import SongSection from "../components/SongSection";
    import AlbumSection from "../components/AlbumSection";

    export default {
        name: "ArtistDetail",
        components: {AlbumSection, SongSection, TopNavigation},
        props: {
            id: String
        },
        methods: {
            ...mapActions('library', [
                'fetchArtistById',
            ]),
            ...mapMutations('queue', [
                'setQueue'
            ]),
            playArtist(e) {
                e.preventDefault();

                this.setQueue(this.artist.songs)
            },
            toggleTextCollapse(e) {
                e.preventDefault();
                e.target.classList.toggle('expanded');
            }
        },
        computed: {
            thumbnail() {
                return (this.artist && this.artist.thumbnail.length) ? this.artist.thumbnail : Util.thumbnailDataUri;
            },
            fanartStyle() {
                return `background: ${this.artist.fanart ? `url('${this.artist.fanart}')` : 'none'}`
            },
            artist() {
                return this.$store.getters['library/getArtistById'](this.id);
            },
            topSongs() {
                return this.artist.topSongs ? this.artist.topSongs : [];
            },
            albums() {
                return this.artist.albums ? this.artist.albums : []
            }

        },
        created() {
            this.fetchArtistById(this.id)
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    #content-artist {
        margin: 2em 0 1em;
        padding-bottom: 1em;

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
            margin: 1em;

            @include smartphone {
                max-height: 110px;
            }
        }

        .list-group-item {
            background-color: rgba(10, 10, 10, 0.5);
            color: #FFF;
            max-width: 600px;
            height: unset;

            @include smartphone {
                padding: 0.5em;
            }
        }

        .list-item-description {
            cursor: pointer;
            margin: 1em 0;

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
    }
</style>