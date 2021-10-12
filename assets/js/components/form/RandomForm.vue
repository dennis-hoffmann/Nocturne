<template>
    <div class="col-12">
        <div class="container-fluid no-gutters p-0">
            <div class="row no-gutters justify-content-center">
                <div class="col-4">
                    <button class="btn btn-outline-light btn-block mb-2" data-toggle="collapse" data-target="#random-filters" :style="btnStyle">Random</button>
                </div>
            </div>
            <form @submit="fetchRandomSongs" id="random-filters" class="row collapse">
                <div id="filter-artist" v-if="filters.years" class="col-12 col-md-3">
                    <label>Artist</label>
                    <select v-model="activeFilters.artists" name="artist[]" class="selectpicker" data-width="100%" multiple data-live-search="true">
                        <option v-for="(artist, id) in filters.artists" :value="id">{{ artist }}</option>
                    </select>
                    <div class="form-control">
                        <input v-model="activeFilters.negations.artist" id="negate_artist" name="negate[artist]" type="checkbox">
                        <label for="negate_artist">Negate</label>
                    </div>
                </div>
                <div id="filter-genre" v-if="filters.genres" class="col-12 col-md-3">
                    <label>Genre</label>
                    <select v-model="activeFilters.genres" name="genre[]" class="selectpicker" data-width="100%" multiple data-live-search="true">
                        <option v-for="genre in filters.genres" :value="genre">{{ genre }}</option>
                    </select>
                    <div class="form-control">
                        <input v-model="activeFilters.negations.genre" id="negate_genre" name="negate[genre]" type="checkbox">
                        <label for="negate_genre">Negate</label>
                    </div>
                </div>
                <div id="filter-year" v-if="filters.years" class="col-12 col-md-6">
                    <label>Year</label>
                    <input name="year" class="bs-slider" type="text" :data-slider-value="`[${filters.years[0]}, ${filters.years[filters.years.length-1] }]`" :data-slider-ticks="`[${filters.years.join(', ')}]`" data-slider-lock-to-ticks="true"/>
                    <div class="form-control">
                        <input v-model="activeFilters.negations.year" id="negate_year" name="negate[year]" type="checkbox">
                        <label for="negate_year">Negate</label>
                    </div>
                </div>
                <div class="col-12">
                    <br>
                    <button type="submit" class="btn btn-light btn-block bg-info mb-2">Fetch</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import {mapGetters, mapMutations} from "vuex";

    const Slider = require('bootstrap-slider');

    export default {
        name: "RandomForm",
        data() {
            return {
                activeFilters: {
                    artists: [],
                    genres: [],
                    years: [],
                    negations: {
                        artist: false,
                        genre: false,
                        year: false,
                    }
                }
            }
        },
        props: {
            filters: Object,
        },
        methods: {
            ...mapMutations('queue', [
                'setQueue'
            ]),
            ...mapMutations([
                'setCurrentSong',
            ]),
            ...mapMutations('player', [
                'play'
            ]),
            async fetchRandomSongs(e) {
                e.preventDefault();

                const songs = await this.$store.state.lib.findRandom(this.activeFilters);

                if (!songs.length) {
                    return;
                }

                this.setQueue(songs);
                this.setCurrentSong(songs[0]);
                this.play();
            }
        },
        computed: {
            ...mapGetters([
                'vibrant',
            ]),
            btnStyle() {
                return `background-color: ${this.vibrant ? this.vibrant.Vibrant.getHex() : 'rgba(220, 53, 69, 1)'};`
            },
        },
        mounted() {
            const slider = new Slider('.bs-slider');
            slider.on('change', (values) => {
                this.activeFilters.years = values.newValue;
            });

            // Todo this is until now the only place where jQuery is needed. This sucks.
            $('.selectpicker').selectpicker();
        }
    }
</script>

<style lang="scss">
    .slider.slider-horizontal {
        width: 100%;
        margin-bottom: 1em;
    }

    #random-filters {
        .form-control {
            margin-top: 1em;
        }

        label {
            color: #999999;
        }
    }
</style>