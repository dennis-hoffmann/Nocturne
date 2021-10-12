let store = null;

//// Listeners ////
const onTimeupdate = function (e) {
    store.commit('updateProgress', {
        currentTime: e.target.currentTime,
        duration: e.target.duration,
    });
};

const onEnded = function (e) {
    if (store.state.meta.repeat === 'one') {
        store.dispatch('player/track');
        store.commit('player/seek', 0);
        store.commit('player/play');

        return;
    }

    store.commit('player/next');
};

//// Main ////
const initTriggers = function (audio) {
    audio.addEventListener('timeupdate', onTimeupdate);
    audio.addEventListener('ended', onEnded);
};

export default function createAudioBackendPlugin(rootStore) {
    store = rootStore;
    initTriggers(store.state.audio)
}