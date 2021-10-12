<template>
    <aside id="main-menu">
        <header class="p-2" :style="borderStyle">
            <button @click="toggleMenu" class="btn btn-outline-danger btn-close menu-close"> &times Close </button>
            <h4 class="mb-0 text-center">Menu </h4>
        </header>
        <nav class="list-group list-group-flush">
            <div class="menu-item list-group-item menu-item-player">Player</div>
            <router-link :to="{ name: 'Home' }"><div class="menu-item list-group-item return-to-home">Home</div></router-link>
            <router-link :to="{ name: 'Library' }"><div class="menu-item list-group-item menu-item-library">Library</div></router-link>
            <div @click="scanAudioLibrary" class="menu-item list-group-item menu-item-scan-audio-library">Scan Audio Library</div>
            <router-link :to="{ name: 'Playlists' }"><div class="menu-item list-group-item menu-item-my-lists">My Lists</div></router-link>
        </nav>
    </aside>
</template>

<script>
    import {mapGetters} from "vuex";

    export default {
        name: "Menu",
        computed: {
            ...mapGetters([
                'vibrant'
            ]),
            borderStyle() {
                return `border-bottom: 5px solid ${this.vibrant ? this.vibrant.Vibrant.getHex() : '#dc3545'}`
            }
        },
        methods: {
            toggleMenu() {
                document.getElementById('main-menu').classList.toggle('show');
            },
            scanAudioLibrary() {
                fetch('/scan/audio', {
                    method: 'GET',
                    mode: 'same-origin',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json'
                    }
                });
            }
        },
        mounted() {
            document.getElementsByClassName('menu-item').forEach(element => {
                element.addEventListener('click', (e) => {
                    this.toggleMenu();
                })
            })
        }
    }
</script>

<style scoped lang="scss">
    @import "../../css/mediaqueries.scss";

    .menu-trigger {
        height: 2.8rem;
        fill: #12b2e7;
        position: absolute;
        right: -5px;
        top: 5px;

        @include desktop {
            display: none;
        }
    }

    .menu-close {
        @include desktop {
            display: none;
        }
    }

    #main-menu {
        width: 300px;
        visibility: hidden;
        transform: translateX(-100%);
        transition: all .2s;
        border-radius: 0;
        box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        display:block;
        position: fixed;
        top: 0;
        left: 0;
        height: calc(100vh - 70px);
        z-index: 401;
        background-color: #3a3b3c;
        max-height: calc(100vh - 70px);

        body.player-detail & {
            max-height: 100vh;
        }

        #menu-hosts, #menu-playlists {
            margin-left: 1rem;
            border-left: 5px solid #dc3545;
        }

        header {
            background-color: #18191a38;
            color: #FFFFFF;
            border-bottom: 5px solid #dc3545;

            .menu-close {
                float: right;
                top: -10px;

                @include smartphone {
                    top: -5px;
                }
            }
        }

        .list-group-item {
            background-color: rgba(0,0,0,0.25);
            color: #FFFFFF;
            cursor: pointer;

            &:hover {
                background-color: rgba(250,250,250,0.45);
                transition: background-color .5s;
            }

            &.active {
                background: rgba(220, 53, 69, 0.6);
            }
        }

        @include desktop {
            display: inline-block;
            position: relative;
            visibility: visible;
            transform: translateX(0);
            transition: transform .2s;
        }

        &.show {
            visibility: visible;
            transform: translateX(0);
            transition: transform .2s;
        }
    }
</style>