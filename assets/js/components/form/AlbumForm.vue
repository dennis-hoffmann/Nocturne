<template>
    <form>
        <div class="cover-row">
            <img :src="thumbnail" />
        </div>
        <div class="mb-3">
            <label for="album-title" class="form-label">Title</label>
            <input v-model="song.album" id="album-title" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="song-album-artist" class="form-label">Artist</label>
            <input v-model="song.artist.name" id="song-album-artist" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="album-year" class="form-label">Year</label>
            <input v-model="song.year" id="album-year" type="text" class="form-control" />
        </div>
        <FileDropZone ref="form" @submit="handleFileUpload"/>
    </form>
</template>

<script>
    import Util from "../../util";
    import FileDropZone from "../FileDropZone";

    export default {
        name: "AlbumForm",
        components: {FileDropZone},
        props: {
            song: Object
        },
        computed: {
            thumbnail() {
                return (this.song.thumbnail && this.song.thumbnail.length) ? this.song.thumbnail : Util.thumbnailDataUri;
            }
        },
        methods: {
            handleFileUpload (files) {
                const fd = new FormData();
                fd.append(this.song.name, files[0]);

                fetch(`/library/audio/album/${this.song.albumId}`, {
                    method: 'POST',
                    body: fd
                })
                .then(res => res.json())
                .then((json) => {
                    console.log(json);
                    this.$refs.form.$refs.form.classList.remove('is-uploading')
                    this.$refs.form.$refs.form.classList.add('is-success')
                })
                .catch(err => console.error(err));
            }
        },
    }
</script>

<style scoped lang="scss">
    .cover-row {
        text-align: center;

        img {
            max-height: 200px;
        }
    }
</style>