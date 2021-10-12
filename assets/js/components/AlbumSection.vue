<template>
    <div class="container">
        <div class="row">
            <Album :key="album.id" v-for="album in albums" :album="album"/>
        </div>
    </div>
</template>

<script>
    import Album from "./Album";

    export default {
        name: "AlbumSection",
        components: {
            Album
        },
        props: {
            albums: Array,
        },
    }
</script>

<style scoped>

</style>