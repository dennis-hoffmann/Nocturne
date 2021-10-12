<template>
    <form>
        <div class="mb-3">
            <label for="song-title" class="form-label">Title</label>
            <input v-model="song.title" id="song-title" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="song-track" class="form-label">Track</label>
            <input v-model="song.track" type="number" class="form-control" id="song-track" />
        </div>
        <div class="mb-3">
            <label for="song-artist" class="form-label">Artist</label>
            <input v-model="song.artist.name" id="song-artist" type="text" class="form-control" />
        </div>
    </form>
</template>

<script>
    export default {
        name: "SongForm",
        props: {
            song: Object
        },
    }
</script>

<style scoped lang="scss">

</style>