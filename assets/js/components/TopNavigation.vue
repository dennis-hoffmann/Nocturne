<template>
    <div id="library-header" class="row no-gutters">
        <div class="col-2">
            <div class="navbar-brand">
                <router-link :to="{ name: 'Home' }">
                    <img height="50" width="50" src="/images/logo.png">
                </router-link>
            </div>
        </div>
        <div class="col-9">
            <div id="audio-search-wrapper" class="col-md-6 offset-md-3">
                <input placeholder="Search..." type="text" class="form-control search bg-dark" v-model="searchQuery">
            </div>
        </div>
        <div @click="toggleMenu" class="navbar-brand m-0 menu-trigger">
            <MenuIcon :size="40" fill-color="#12b2e7" />
        </div>
    </div>
    <div id="result-collapse" v-if="searchQuery">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h3 class="text-center">Results</h3>
                    <ul class="list-group list-group-flush artist-results" v-if="searchResult.artists">
                        <router-link :to="{ name: 'ArtistDetail', params: { id: artist.id } }" :key="artist.id" v-for="(artist) in searchResult.artists">
                            <li>
                                <div class="list-group-item-content">
                                    <img class="thumb" :src="artist.thumbnail.length ? artist.thumbnail : ''" />
                                    <span class="artist-name">{{ artist.name }}</span>
                                </div>
                            </li>
                        </router-link>
                    </ul>
                    <AlbumSection :albums="searchResult.albums" />
                    <br />
                    <SongSection :songs="searchResult.songs" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import MenuIcon from "./icons/Menu";
    import SongSection from "./SongSection";
    import AlbumSection from "./AlbumSection";

    export default {
        name: "TopNavigation",
        components: {AlbumSection, SongSection, MenuIcon},
        data() {
            return {
                timeout: null,
                debouncedSearchQuery: '',
            }
        },
        methods: {
            toggleMenu() {
                document.getElementById('main-menu').classList.toggle('show');
            }
        },
        watch: {
            searchQuery (val, oldVal) {
                this.$store.dispatch('library/fetchSearchResult', val)
            }
        },
        computed: {
            searchResult() {
                return this.$store.getters['library/getSearchResult'](this.searchQuery);
            },
            searchQuery: {
                get() {
                    return this.debouncedSearchQuery
                },
                set(val) {
                    if (this.timeout) {
                        clearTimeout(this.timeout)
                    }

                    this.timeout = setTimeout(() => {
                        this.debouncedSearchQuery = val
                    }, 300)
                }
            }
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    #library-header {
        height: 55px;
        position: relative;

        #audio-search-wrapper {
            margin-top: 5px;
            height: 50px;

            input {
                &::placeholder {
                    color: #FFFFFF;
                    opacity: 1;
                    margin-left: 5px;
                }

                color: #FFFFFF;
            }
        }

        .menu-trigger {
            height: 2.8rem;
            position: absolute;
            right: 5px;
            top: 0;

            @include desktop {
                display: none;
            }
        }

        .menu-close {
            @include desktop {
                display: none;
            }
        }
    }

    #result-collapse {
        height: 60%;
        width: 100%;
        position: absolute;
        background-color: #3a3b3c;
        z-index: 5;

        @include smartphone {
            height: calc(100vh - 80px - 55px);
        }

        h3 {
            color: #FFFFFF;
        }

        .artist-results {
            list-style: none;
            margin-bottom: 1em;

            a {
                text-decoration: none;
                color: #FFF;
            }

            li {
                &:hover {
                    background: rgba(255, 255, 255, 0.15);
                }

                .list-group-item-content {
                    height: 45px;
                    line-height: 45px;
                }
            }
        }

        .container-fluid {
            @include smartphone {
                padding: 0;
                width: auto;
                max-height: calc(100vh - 80px - 55px);
            }

            padding: 2rem;
            overflow-y: scroll;
            overflow-x: hidden;
            max-height: 90%;
            width: 80%;
            margin: 0 auto;
        }
    }
</style>