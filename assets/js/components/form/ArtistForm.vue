<template>
    <form>
        <div class="cover-row">
            <img :src="thumbnail" />
        </div>
        <div class="mb-3">
            <label for="artist-name" class="form-label">Name</label>
            <input v-model="artist.name" id="artist-name" type="text" class="form-control" />
        </div>
        <div class="mb-3">
            <label for="artist-description" class="form-label">Description</label>
            <textarea v-model="artist.description" id="artist-description" class="form-control" ></textarea>
        </div>
        <div class="mb-3">
            <label for="artist-years-active" class="form-label">Years Active</label>
            <input v-model="artist.yearsactive" id="artist-years-active" type="text" class="form-control" />
        </div>
    </form>
</template>

<script>
    import Util from "../../util";

    export default {
        name: "ArtistForm",
        props: {
            artist: Object,
        },
        computed: {
            thumbnail() {
                return (this.artist && this.artist.thumbnail.length) ? this.artist.thumbnail : Util.thumbnailDataUri;
            },
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