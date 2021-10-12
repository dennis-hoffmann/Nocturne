<template>
    <div class="modal" id="lyrics-modal" tabindex="-1" role="dialog" aria-hidden="true" style="display: block;" data-backdrop="false">
        <div class="modal-dialog modal-dialog-centered " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Lyrics</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="lyrics-holder" class="text-center" v-html="lyrics">
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "LyricOverlay",
        props: {
            lyrics: String,
        },
        emits: ['hidden'],
        mounted() {
            // Todo migrate this to bootstrap-vue
            $(this.$el).on('hidden.bs.modal', (e) => {
                this.$emit('hidden')
            })
        },
    }
</script>

<style scoped lang="scss">
    #lyrics-modal {
        background-color: rgba(0, 0, 0, .75);
    }
</style>