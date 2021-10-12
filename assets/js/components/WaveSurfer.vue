<template>
    <div ref="wave" id="waveform"></div>
</template>

<script>
    import {mapGetters} from "vuex";

    export default {
        name: "WaveSurfer",
        computed: {
            ...mapGetters([
                'playerType',
            ]),
            ...mapGetters('waveSurfer', [
                'wave',
            ])
        },
        props: {
            color: String
        },
        watch: {
            color (val, oldVal) {
                this.wave.setProgressColor(val);
            },
            playerType(val, oldVal) {
                if (oldVal !== 'detail' && val === 'detail') {
                    this.$nextTick(function() {
                        if (this.wave) {
                            this.wave.drawBuffer();
                        }
                    });
                }
            },
        },
    }
</script>

<style scoped>

</style>