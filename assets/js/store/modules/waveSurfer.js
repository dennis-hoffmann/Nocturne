import WaveSurfer from 'wavesurfer.js';

const state = {
    wave: null,
};

const getters = {
    wave: state => state.wave,
};

const actions = {

};

const mutations = {
    init(state, domElement) {
        if (state.wave === null) {
            state.wave = WaveSurfer.create({
                container: domElement,
                waveColor: '#FFFFFF',
                cursorColor: '#FFFFFF',
                progressColor: '#AFAFAF',
                backend: 'MediaElement',
                barWidth: 3,
                responsive: true,
                removeMediaElementOnDestroy: false
            });
        }
    },
};

export default {
    namespaced: true,
    state,
    getters,
    actions,
    mutations
};