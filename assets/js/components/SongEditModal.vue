<template>
    <div class="modal" role="dialog" style="display: block;">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <p>{{ song.title }} - {{ song.artistname }}</p>
                    <ul class="nav nav-tabs">
                        <li @click="setTab('song')" class="nav-item">
                            <span class="nav-link" :class="tab === 'song' ? ' active' : ''">Song</span>
                        </li>
                        <li @click="setTab('album')" class="nav-item">
                            <span class="nav-link" :class="tab === 'album' ? ' active' : ''">Album</span>
                        </li>
                        <li @click="setTab('artist')" class="nav-item">
                            <span class="nav-link" :class="tab === 'artist' ? ' active' : ''">Artist</span>
                        </li>
                        <li @click="setTab('lyrics')" class="nav-item">
                            <span class="nav-link" :class="tab === 'lyrics' ? ' active' : ''">Lyrics</span>
                        </li>
                    </ul>
                    <button @click="close" type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <SongForm v-if="tab === 'song'" :song="song" />
                    <AlbumForm v-if="tab === 'album'" :song="song" />
                    <ArtistForm v-if="tab === 'artist'" :artist="song.artist" />
                    <LyricsForm v-if="tab === 'lyrics'" :song="song" />
                </div>
                <div class="modal-footer">
                    <button @click="save" type="button" class="btn btn-primary">Save changes</button>
                    <button @click="close" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import SongForm from "./form/SongForm";
    import {mapMutations} from "vuex";
    import AlbumForm from "./form/AlbumForm";
    import ArtistForm from "./form/ArtistForm";
    import LyricsForm from "./form/LyricsForm";

    export default {
        name: "SongEditModal",
        components: {LyricsForm, ArtistForm, AlbumForm, SongForm},
        data() {
            return {
                tab: 'song'
            }
        },
        methods: {
            ...mapMutations([
                'setEditingSong',
            ]),
            setTab(tab) {
                this.tab = tab;
            },
            close() {
                this.setEditingSong(null);
            },
            save() {
                this.$store.state.lib.updateSong(this.song).then((json) => {
                    this.close();
                });
            }
        },
        props: {
            song: Object,
        }
    }
</script>

<style scoped lang="scss">
    .modal-header {
        display: block;
    }

    .close {
        position: absolute;
        right: 2rem;
        top: 1rem;
    }
</style>