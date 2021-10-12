<template>
    <form>
        <div class="mb-3">
            <label for="song-lyrics" class="form-label">Description</label>
            <textarea v-model="song.lyrics" id="song-lyrics" class="form-control" rows="10" ></textarea>
        </div>
    </form>
</template>

<script>
    export default {
        name: "LyricsForm",
        props: {
            song: Object,
        },
    }
</script>

<style scoped lang="scss">

</style>